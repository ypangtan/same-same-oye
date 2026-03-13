<?php

namespace App\Services;

use App\Models\{
    CallbackLog,
    SubscriptionPlan,
    UserSubscription,
    PaymentTransaction,
    User,
};
use Imdhemy\Purchases\Facades\{
    Subscription,
};
use Carbon\Carbon;
use Exception;
use Imdhemy\AppStore\ClientFactory as AppStoreClientFactory;
use Google\Client as GoogleClient;
use Google\Service\AndroidPublisher;

use Illuminate\Support\Facades\{
    Http,
    DB,
    Log,
};

class PaymentService {

    public static function verifyIOSPurchase( $user_id, $data ) {
        try {
            DB::beginTransaction();

            $user = User::find( $user_id );
            $receiptData = $data['receipt_data'];
            $plan = SubscriptionPlan::find( $data['plan_id'] );
            $productId = $plan->ios_product_id;
            
            // plugin 没有处理sandbox和生产环境切换，这里手动处理
            $isSandbox = config('liap.appstore_sandbox', true);
            $client = AppStoreClientFactory::create($isSandbox);

            // 验证收据
            $response = Subscription::appStore( $client )
                ->receiptData($receiptData)
                ->verifyRenewable();

            $statusCode = $response->getStatus();
            $status = $statusCode->getValue();

            // 检查验证状态
            if ($status !== 0) {
                throw new Exception("Receipt verification failed with status: " . $status);
            }

            // 获取最新的收据信息
            $latestReceipt = $response->getLatestReceiptInfo();
            if (empty($latestReceipt)) {
                throw new Exception("No receipt info found");
            }

            $receiptInfo = $latestReceipt[0];
            $transactionId = $receiptInfo->getTransactionId();
            $originalTransactionId = $receiptInfo->getOriginalTransactionId();

            $rawData = $receiptInfo->toArray();
            $currentProductId = $rawData['product_id'];
            $expiryDate = Carbon::createFromTimestampMs( $rawData['expires_date_ms'] )
                ->timezone('Asia/Kuala_Lumpur')
                ->format( 'Y-m-d' );

            $currentPlan = SubscriptionPlan::where('ios_product_id', $currentProductId)->first();
            $currentPlanId = $currentPlan ? $currentPlan->id : $plan->id;

            // 检查是否有 pending renewal info（iOS 降级）
            // pending_renewal_info 里的 auto_renew_product_id 与当前 product_id 不同时，表示有 deferred plan change
            $deferredIosProductId = null;
            $pendingRenewalInfo = $response->getPendingRenewalInfo() ?? [];
            foreach ($pendingRenewalInfo as $renewal) {
                $renewalArray = $renewal->toArray();
                $autoRenewProductId = $renewalArray['auto_renew_product_id'] ?? null;
                if ($autoRenewProductId && $autoRenewProductId !== $productId) {
                    $deferredIosProductId = $autoRenewProductId;
                    break;
                }
            }

            // 检查交易是否已存在
            if ( PaymentTransaction::exists( $transactionId ) ) {
                return [
                    'success' => true,
                    'message' => 'Transaction already processed',
                    'subscription' => $user->subscriptions()->where( 'platform_transaction_id', $originalTransactionId )->first(),
                ];
            }

            // 创建或更新订阅
            $isRenew = true;
            $subscription = self::createOrUpdateSubscription($user_id, $currentPlanId, 1, $originalTransactionId, $expiryDate, $isRenew);   
            
            // ✅ 如果有 deferred plan（iOS 降级），额外创建 status=1 的 pending subscription
            if ($deferredIosProductId) {
                $deferredPlan = SubscriptionPlan::where('ios_product_id', $deferredIosProductId)->first();

                if ($deferredPlan) {
                    $existingPending = UserSubscription::where('user_id', $user->id)
                        ->where('subscription_plan_id', $deferredPlan->id)
                        ->where('status', 1)
                        ->first();

                    if (!$existingPending) {
                        UserSubscription::create([
                            'user_id' => $user->id,
                            'subscription_plan_id' => $deferredPlan->id,
                            'status' => 1, // pending
                            'start_date' => null,
                            'end_date' => null,
                            'platform' => 1,
                            'platform_transaction_id' => $originalTransactionId,
                        ]);

                        Log::channel('payment')->info('iOS deferred plan change recorded', [
                            'user_id' => $user->id,
                            'current_plan_id' => $plan->id,
                            'deferred_plan_id' => $deferredPlan->id,
                            'deferred_product_id' => $deferredIosProductId,
                        ]);
                    }
                }
            }

            // // 记录交易
            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'user_subscription_id' => $subscription->id,
                'transaction_id' => $transactionId,
                'original_transaction_id' => $originalTransactionId,
                'amount' => 0,
                'currency' => 'MYR',
                'platform' => 1,
                'product_id' => $productId,
                'receipt_data' => $receiptData,
                'status' => 10,
                'verified_at' => Carbon::now()->timezone( 'Asia/Kuala_Lumpur' ),
                'verification_response' => json_encode($response->toArray()),
            ]);

            Log::channel('payment')->info('iOS purchase verified', [
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
            ]);

            DB::commit();
            return [
                'success' => true,
                'message' => 'Subscription activated successfully',
                'subscription' => $subscription->fresh(),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('payment')->error('iOS verification failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public static function verifyIOSPurchaseV2( $user_id, $data ) {
        try {
            DB::beginTransaction();

            $user = User::find( $user_id );
            $receiptData = $data['receipt_data'];
            $plan = SubscriptionPlan::find( $data['plan_id'] );
            $productId = $plan->ios_product_id;

            // 检测是 StoreKit 1 还是 StoreKit 2
            // $isStoreKit2 = ( count( explode( '.', $receiptData ) ) === 3 );
            $isStoreKit2 = substr_count($receiptData, '.') === 2;
            return $isStoreKit2;
            if ( $isStoreKit2 ) {
                $parts = explode( '.', $receiptData );
                $payload = json_decode( base64_decode( str_pad( $parts[1], strlen( $parts[1] ) + ( 4 - strlen( $parts[1] ) % 4 ) % 4, '=' ) ), true );

                if ( empty( $payload ) ) {
                    throw new Exception( "Failed to decode JWT payload" );
                }

                $transactionId = $payload['transactionId'] ?? null;
                $originalTransactionId = $payload['originalTransactionId'] ?? $transactionId;
                $currentProductId = $payload['productId'] ?? $productId;
                $expiresDateMs = $payload['expiresDate'] ?? null;

                if ( !$transactionId || !$expiresDateMs ) {
                    throw new Exception( "Missing transactionId or expiresDate in JWT" );
                }

                $expiryDate = Carbon::createFromTimestampMs( $expiresDateMs )->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d' );

                $deferredIosProductId = null; // StoreKit 2 deferred 另外处理
                $verificationResponse = json_encode( $payload );

                Log::channel('payment')->info('iOS StoreKit 2 JWT decoded', [
                    'user_id' => $user_id,
                    'environment' => $payload['environment'] ?? null,
                    'productId' => $currentProductId,
                    'transactionId' => $transactionId,
                    'expiresDate' => $expiresDateMs,
                ]);

            } else {
                // StoreKit 1
                $isSandbox = config('liap.appstore_sandbox', false);
                $client = AppStoreClientFactory::create($isSandbox);

                $response = Subscription::appStore($client)
                    ->receiptData($receiptData)
                    ->verifyRenewable();

                $statusCode = $response->getStatus();
                $status = $statusCode->getValue();

                if ( $status !== 0 ) {
                    throw new Exception( "Receipt verification failed with status: " . $status );
                }

                $latestReceipt = $response->getLatestReceiptInfo();
                if ( empty( $latestReceipt ) ) {
                    throw new Exception( "No receipt info found" );
                }

                $receiptInfo = $latestReceipt[0];
                $transactionId = $receiptInfo->getTransactionId();
                $originalTransactionId = $receiptInfo->getOriginalTransactionId();

                $rawData = $receiptInfo->toArray();
                $currentProductId = $rawData['product_id'];
                $expiryDate = Carbon::createFromTimestampMs( $rawData['expires_date_ms'] )->timezone('Asia/Kuala_Lumpur')->format( 'Y-m-d' );

                // deferred plan（iOS 降级）
                $deferredIosProductId = null;
                $pendingRenewalInfo = $response->getPendingRenewalInfo() ?? [];
                foreach ( $pendingRenewalInfo as $renewal ) {
                    $renewalArray = $renewal->toArray();
                    $autoRenewProductId = $renewalArray['auto_renew_product_id'] ?? null;
                    if ( $autoRenewProductId && $autoRenewProductId !== $productId ) {
                        $deferredIosProductId = $autoRenewProductId;
                        break;
                    }
                }

                $verificationResponse = json_encode( $response->toArray() );

                Log::channel('payment')->info('iOS StoreKit 1 verified', [
                    'user_id'       => $user_id,
                    'productId'     => $currentProductId,
                    'transactionId' => $transactionId,
                ]);
            }

            // 以下逻辑 StoreKit 1 & 2 共用
            $currentPlan = SubscriptionPlan::where('ios_product_id', $currentProductId)->first();
            $currentPlanId = $currentPlan ? $currentPlan->id : $plan->id;

            // 检查交易是否已存在
            if ( PaymentTransaction::exists( $transactionId ) ) {
                return [
                    'success' => true,
                    'message' => 'Transaction already processed',
                    'subscription' => $user->subscriptions()->where('platform_transaction_id', $originalTransactionId)->first(),
                ];
            }

            // 创建或更新订阅
            $subscription = self::createOrUpdateSubscription( $user_id, $currentPlanId, 1, $originalTransactionId, $expiryDate, true );

            // deferred plan（StoreKit 1 降级）
            if ( !empty( $deferredIosProductId ) ) {
                $deferredPlan = SubscriptionPlan::where('ios_product_id', $deferredIosProductId)->first();
                if ( $deferredPlan ) {
                    $existingPending = UserSubscription::where( 'user_id', $user->id )
                        ->where( 'subscription_plan_id', $deferredPlan->id )
                        ->where( 'status', 1 )
                        ->first();

                    if (!$existingPending) {
                        UserSubscription::create([
                            'user_id' => $user->id,
                            'subscription_plan_id' => $deferredPlan->id,
                            'status' => 1,
                            'start_date' => null,
                            'end_date' => null,
                            'platform' => 1,
                            'platform_transaction_id'  => $originalTransactionId,
                        ]);

                        Log::channel('payment')->info('iOS deferred plan change recorded', [
                            'user_id'            => $user->id,
                            'current_plan_id'    => $plan->id,
                            'deferred_plan_id'   => $deferredPlan->id,
                            'deferred_product_id'=> $deferredIosProductId,
                        ]);
                    }
                }
            }

            // 记录交易
            PaymentTransaction::create([
                'user_id' => $user->id,
                'user_subscription_id' => $subscription->id,
                'transaction_id' => $transactionId,
                'original_transaction_id' => $originalTransactionId,
                'amount' => 0,
                'currency' => 'MYR',
                'platform' => 1,
                'product_id' => $currentProductId,
                'receipt_data' => $receiptData,
                'status' => 10,
                'verified_at' => Carbon::now()->timezone('Asia/Kuala_Lumpur'),
                'verification_response' => $verificationResponse,
            ]);

            Log::channel('payment')->info('iOS purchase verified', [
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
                'storekit' => $isStoreKit2 ? 'SK2' : 'SK1',
            ]);

            DB::commit();
            return [
                'success' => true,
                'message' => 'Subscription activated successfully',
                'subscription' => $subscription->fresh(),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('payment')->error('iOS verification failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public static function verifyAndroidPurchase( $user_id, $data ) {
        try {
            DB::beginTransaction();

            $credentialsPath = config('liap.google_application_credentials');
            
            if (!file_exists($credentialsPath)) {
                throw new \Exception('Google Play 凭据文件不存在: ' . $credentialsPath);
            }
        
            $user = User::find( $user_id );
            $plan = SubscriptionPlan::find( $data['plan_id'] );
            $purchaseToken = $data['purchase_token'];
            $packageName = config('liap.google_play_package_name');

            $client = new GoogleClient();
            $client->setAuthConfig($credentialsPath);
            $client->setScopes([
                AndroidPublisher::ANDROIDPUBLISHER,
            ]);

            $androidPublisher = new AndroidPublisher($client);

            $subscriptionPurchase = $androidPublisher->purchases_subscriptionsv2->get($packageName, $purchaseToken);

            if ( empty( $subscriptionPurchase->getLineItems() ) ) {
                throw new \Exception('Invalid subscription purchase (no line items)');
            }

            // ✅ 遍历 lineItems，找出 current active（有 expiryTime）和 deferred plan
            $currentLineItem = null;
            $deferredProductId = null;

            foreach ($subscriptionPurchase->getLineItems() as $item) {
                if ($item->getExpiryTime()) {
                    $currentLineItem = $item;
                }
            }

            // ✅ 有 current active plan 才处理 expiry 和 productId
            if ($currentLineItem) {
                $productId = $currentLineItem->getProductId();
                $expiryTime = $currentLineItem->getExpiryTime();
                if ($expiryTime) {
                    $expiredDate = Carbon::parse($expiryTime)->timezone('Asia/Kuala_Lumpur');
                }
            } else {
                // 没有 active line item，只处理 deferred
                $productId = null;
                $expiredDate = null;
            }

            $orderId = $subscriptionPurchase->getLatestOrderId();

            // check state payment
            if ($subscriptionPurchase->getSubscriptionState() !== 'SUBSCRIPTION_STATE_ACTIVE') {
                throw new \Exception( 'Subscription not active, ' . $subscriptionPurchase->getSubscriptionState() );
            }

            // 检查交易是否已存在
            if ( PaymentTransaction::exists($orderId) ) {
                return [
                    'success' => true,
                    'message' => 'Transaction already processed',
                    'subscription' => $user->subscriptions()->where( 'platform', 2 )->isActive()->first(),
                ];
            }

            // ✅ 只有 currentLineItem 存在才 createOrUpdate
            if ($currentLineItem) {
                $isRenew = true;
                
                $currentPlan = SubscriptionPlan::where('android_product_id', $productId)->first();
                $currentPlanId = $currentPlan ? $currentPlan->id : $plan->id;
                
                $subscription = self::createOrUpdateSubscription($user_id, $currentPlanId, 2, $orderId, $expiredDate ?? null, $isRenew);
            }

            // 记录交易
            if ($subscription) {
                $transaction = PaymentTransaction::create([
                    'user_id' => $user->id,
                    'user_subscription_id' => $subscription->id,
                    'transaction_id' => $orderId,
                    'original_transaction_id' => $orderId,
                    'amount' => 0,
                    'currency' => 'MYR',
                    'platform' => 2,
                    'product_id' => $productId,
                    'receipt_data' => $purchaseToken,
                    'status' => 10,
                    'verified_at' => Carbon::now()->timezone( 'Asia/Kuala_Lumpur' ),
                    'verification_response' => json_encode( $subscriptionPurchase ),
                ]);
            }

            // 确认购买（告诉 Google 已经处理）
            if ( $subscriptionPurchase->getAcknowledgementState() === 'ACKNOWLEDGEMENT_STATE_ACKNOWLEDGED' ) {
                Log::channel('payment')->info('Subscription already acknowledged', [
                    'purchaseToken' => $purchaseToken,
                ]);
            } else {
                $accessTokenData = $client->fetchAccessTokenWithAssertion();
                if ( empty( $accessTokenData['access_token'] ) ) {
                    throw new \Exception('Failed to fetch Google access token');
                }

                $accessToken = $accessTokenData['access_token'];
                
                // acknowledge 用 currentLineItem 的 productId，如果没有就用 plan 的
                $ackProductId = $productId ?? $plan->android_product_id;
                $ackUrl = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/subscriptions/{$ackProductId}/tokens/{$purchaseToken}:acknowledge";
                
                $ackResponse = Http::withToken($accessToken)->post($ackUrl);

                if ( !$ackResponse->successful() ) {
                    Log::channel('payment')->error('Android subscription acknowledge failed', [
                        'status' => $ackResponse->status(),
                        'response' => $ackResponse->body(),
                        'url' => $ackUrl,
                    ]);

                    throw new \Exception('Failed to acknowledge Android subscription');
                }
            }

            Log::channel('payment')->info('Android purchase verified', [
                'user_id' => $user_id,
                'transaction_id' => $orderId,
                'has_deferred_plan' => !is_null($deferredProductId),
            ]);

            DB::commit();
            return [
                'success' => true,
                'message' => 'Subscription activated successfully',
                'subscription' => $subscription ? $subscription->fresh() : null,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('payment')->error('Android verification failed', [
                'user_id' => $user_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public static function verifyHuaweiPurchase( $user_id, $data ) {
        try {
            // TODO: 
            $user = User::find( $user_id );
            $plan = SubscriptionPlan::find( $data['plan_id'] );
            $productId = $plan->huawei_product_id;
            
            throw new Exception("Huawei IAP verification not implemented yet");

        } catch (Exception $e) {
            Log::channel('payment')->error('Huawei verification failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected static function createOrUpdateSubscription( $user_id, $plan_id, $platform, $transactionId, $endDate, $autoRenew = true ) {
        $user = User::find( $user_id );
        $plan = SubscriptionPlan::find( $plan_id );

        // ✅ 只过期同平台的其他 active subscription（不影响跨平台）
        $existsSubscriptions = $user->subscriptions()
            ->where('platform', $platform)
            ->where('platform_transaction_id', '!=', $transactionId)
            ->where('status', 10)
            ->get();

        foreach ( $existsSubscriptions as $exists ) {
            $exists->status = 20;
            $exists->save();
        }
        
        // 只过期trial
        $existsSubscriptions = $user->subscriptions()
            ->where('type', '2')
            ->where('status', 10)
            ->get();

        foreach ( $existsSubscriptions as $exists ) {
            $exists->status = 20;
            $exists->save();
        }

        $subscription = $user->subscriptions()
            ->where('platform', $platform)
            ->where('platform_transaction_id', $transactionId)
            ->first();

        if ($subscription) {
            $subscription->update([
                'subscription_plan_id' => $plan->id,
                'status' => 10,
                'end_date' => $endDate ?? null,
            ]);
        } else {
            $subscription = UserSubscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => 10,
                'start_date' => Carbon::now()->timezone( 'Asia/Kuala_Lumpur' ),
                'end_date' => $endDate ?? null,
                'platform' => $platform,
                'platform_transaction_id' => $transactionId,
            ]);
            
            UserService::createUserNotification(
                $user->id,
                'notification.subscribed_success_title',
                'notification.subscribed_success_content',
                'subscription_success',
                'subscription'
            );
        }

        return $subscription;
    }
}