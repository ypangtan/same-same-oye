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
    Product,
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
    Cache,
};

use Firebase\JWT\{
    JWT,
    JWK,
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
            $expiryDate = Carbon::createFromTimestampMs( $receiptInfo->getExpiresDateMs() )
                ->setTimezone('Asia/Kuala_Lumpur');

            // $expiredDate = Carbon::now()->timezone( 'Asia/Kuala_Lumpur' )->addYears( $plan->duration_in_years )->addMonths( $plan->duration_in_months )->addDays( $plan->duration_in_days );

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
            $subscription = self::createOrUpdateSubscription( $user_id, $plan->id, 1, $originalTransactionId, $expiryDate, $isRenew );

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
                // 'transaction' => $transaction,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('payment')->error('iOS verification failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'previous' => $e->getPrevious() ? [
                    'message' => $e->getPrevious()->getMessage(),
                    'file' => $e->getPrevious()->getFile(),
                    'line' => $e->getPrevious()->getLine(),
                    'class' => get_class($e->getPrevious()),
                ] : null,
            ]);

            throw $e;
        }
    }

    public static function verifyAndroidPurchase( $user_id, $data ) {
        try {
            DB::beginTransaction();

            $credentialsPath = config('liap.google_application_credentials');
            
            // 检查文件是否存在
            if (!file_exists($credentialsPath)) {
                throw new \Exception('Google Play 凭据文件不存在: ' . $credentialsPath);
            }
        
            $user = User::find( $user_id );
            $plan = SubscriptionPlan::find( $data['plan_id'] );
            $productId = $plan->android_product_id;
            $purchaseToken = $data['purchase_token'];
            $packageName = config('liap.google_play_package_name');

            // 验证订阅
            $client = new GoogleClient();
            $client->setAuthConfig($credentialsPath);
            $client->setScopes([
                AndroidPublisher::ANDROIDPUBLISHER,
            ]);

            $androidPublisher = new AndroidPublisher($client);

            /**
             * 2️⃣ 调用 subscriptionsv2.get
             */
            $subscriptionPurchase = $androidPublisher->purchases_subscriptionsv2->get($packageName, $purchaseToken);

            
            if ( empty( $subscriptionPurchase->getLineItems() ) ) {
                throw new \Exception('Invalid subscription purchase (no line items)');
            }

            // 获取订阅信息
            $lineItem = $subscriptionPurchase->getLineItems()[0];
            $orderId = $subscriptionPurchase->getLatestOrderId();
            $productId = $lineItem->getProductId();
            $expiryDate = Carbon::createFromTimestampMs( $lineItem->getExpiryTimeMillis() )
                ->setTimezone('Asia/Kuala_Lumpur');
            // $expiredDate = Carbon::now()->timezone( 'Asia/Kuala_Lumpur' )->addYears( $plan->duration_in_years )->addMonths( $plan->duration_in_months )->addDays( $plan->duration_in_days );
            
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

            // 创建或更新订阅
            $isRenew = true;
            $subscription = self::createOrUpdateSubscription( $user_id, $plan->id, 2, $orderId, $expiredDate, $isRenew );

            // 记录交易
            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'user_subscription_id' => $subscription->id,
                'transaction_id' => $orderId,
                'original_transaction_id' => $orderId,
                'amount' => 0,
                'currency' => 'MYR',
                'platform' => 2,
                'product_id' => $productId,
                'receipt_data' => json_encode( [ 'purchase_token' => $purchaseToken ] ),
                'status' => 10,
                'verified_at' => Carbon::now()->timezone( 'Asia/Kuala_Lumpur' ),
                'verification_response' => json_encode( $subscriptionPurchase ),
            ]);

            // 确认购买（告诉 Google 已经处理）plugin 没有处理确认购买，这里手动处理
            if ($subscriptionPurchase->getAcknowledgementState() === 'ACKNOWLEDGEMENT_STATE_ACKNOWLEDGED') {
                Log::channel('payment')->info('Subscription already acknowledged', [
                    'purchaseToken' => $purchaseToken,
                ]);
            } else {
                $accessTokenData = $client->fetchAccessTokenWithAssertion();
                if (empty($accessTokenData['access_token'])) {
                    throw new \Exception('Failed to fetch Google access token');
                }

                $accessToken = $accessTokenData['access_token'];
                
                $ackUrl = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/subscriptions/{$productId}/tokens/{$purchaseToken}:acknowledge";
                
                $ackResponse = Http::withToken($accessToken)
                    ->post($ackUrl);

                if (!$ackResponse->successful()) {
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
            ]);

            DB::commit();
            return [
                'success' => true,
                'message' => 'Subscription activated successfully',
                'subscription' => $subscription->fresh(),
                'transaction' => $transaction,
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

        $subscription = $user->subscriptions()
            ->where('platform', $platform)
            ->where('platform_transaction_id', $transactionId)
            ->first();

        if ($subscription) {
            // 更新现有订阅
            $subscription->update([
                'status' => 10,
                'end_date' => $endDate,
            ]);
        } else {
            // 创建新订阅
            $subscription = UserSubscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => 10,
                'start_date' => Carbon::now()->timezone( 'Asia/Kuala_Lumpur' ),
                'end_date' => $endDate,
                'platform' => $platform,
                'platform_transaction_id' => $transactionId,
            ]);
        }

        return $subscription;
    }

    public static function verifySubscriptionV2($packageName, $subscriptionId, $purchaseToken) {
        $credentialsPath = config('liap.google_application_credentials');
            
        // 检查文件是否存在
        if (!file_exists($credentialsPath)) {
            throw new \Exception('Google Play 凭据文件不存在: ' . $credentialsPath);
        }
        
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([
            AndroidPublisher::ANDROIDPUBLISHER,
        ]);

        $service = new AndroidPublisher($client);

        try {
            $subscriptionData = $service
                ->purchases_subscriptionsv2
                ->get( $packageName, $subscriptionId, $purchaseToken );

            if ($subscriptionData) {
                // 更新用户订阅表
                $userSubscription = UserSubscription::where('purchase_token', $purchaseToken)->first();
                if ($userSubscription) {
                    switch( $subscriptionData->getSubscriptionState() ) {
                        case 'SUBSCRIPTION_STATE_ACTIVE': // 活跃
                            $userSubscription->status = 10; // 激活
                            break;
                        case 'SUBSCRIPTION_STATE_CANCELED': // 已取消
                            $userSubscription->status = 40; // 取消
                            break;
                        case 'SUBSCRIPTION_STATE_EXPIRED': // 已过期
                            $userSubscription->status = 20; // 过期
                            break;
                        case 'SUBSCRIPTION_STATE_IN_GRACE_PERIOD': // 宽限期
                        case 'SUBSCRIPTION_STATE_PAUSED': // 暂停
                            break;
                    }
                    $lineItems = $subscriptionData->getLineItems();
                    $expiryMillis = $lineItems[0]->getExpiryTimeMillis();
                    $userSubscription->end_date = Carbon::createFromTimestampMs( $expiryMillis )->timezone( 'Asia/Kuala_Lumpur' );
                    $userSubscription->save();
                }
            }

        } catch ( Exception $e ) {
            \Log::channel( 'payment' )->error('Google subscription verify failed: '.$e->getMessage());
            return null;
        }
    }

    public static function callbackAndroid( $request ) {
        
        try{
            DB::beginTransaction();

            $createLog = CallbackLog::create([
                'platform' => 'android',
                'payload' => json_encode( $request->all() ),
            ]);

            $message = $request->input('message');
            $data = json_decode(base64_decode($message['data']), true);

            switch ($data['eventType']) {
                case 'SUBSCRIPTION_RECOVERED':
                case 'SUBSCRIPTION_RENEWED':
                    // 查询最新订阅状态
                    PaymentService::verifySubscriptionV2(
                        $data['packageName'],
                        $data['subscriptionId'],
                        $data['purchaseToken']
                    );
                    break;

                case 'SUBSCRIPTION_CANCELED':
                case 'SUBSCRIPTION_REVOKED':
                    // 标记用户订阅为取消
                    PaymentService::verifySubscriptionV2(
                        $data['packageName'],
                        $data['subscriptionId'],
                        $data['purchaseToken']
                    );
                    break;
            }

            DB::commit();

            return response()->json( ['status' => 'success'], 200 );

        } catch (Exception $e) {
            DB::rollBack();

            Log::channel('payment')->error('Android callback failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json( [ 
                'message' => $e->getMessage()
            ], 500 );
        }
    }

    public static function callbackIos( $request ) {
        try{
            DB::beginTransaction();

            $createLog = CallbackLog::create([
                'platform' => 'ios',
                'payload' => json_encode( $request->all() ),
            ]);

            $signedPayload = $request->input('signedPayload');

            if (!$signedPayload) {
                return response()->json(['error' => 'Missing signedPayload'], 400);
            }

            $payload = self::decodeAndVerify($signedPayload);

            // 用 original_transaction_id 作为唯一订阅标识
            $originalTransactionId =
                $payload->data->signedTransactionInfo->originalTransactionId
                ?? null;

            if ($originalTransactionId) {
                self::syncSubscription($originalTransactionId);
            }

        } catch (Exception $e) {
            DB::rollBack();

            Log::channel('payment')->error('iOS callback failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json( [ 
                'message' => $e->getMessage()
            ], 500 );
        }
    }

    public static function decodeAndVerify(string $signedPayload): \stdClass {
        $keys = Cache::remember('apple_jwks', 3600, function () {
            return Http::get('https://appleid.apple.com/auth/keys')->json();
        });

        return JWT::decode(
            $signedPayload,
            JWK::parseKeySet($keys),
            ['ES256']
        );
    }

    /**
     * 同步订阅状态（唯一真相）
     */
    public static function syncSubscription(string $originalTransactionId) {
        $userSubscription = UserSubscription::where(
            'original_transaction_id',
            $originalTransactionId
        )->first();

        if (!$userSubscription) {
            return;
        }

        // 🔥 关键：调用 Imdhemy 再 verify
        $receiptData = Subscription::verify($userSubscription->receipt_data);

        $latest = collect($receiptData->getLatestReceiptInfo())
            ->sortByDesc('expires_date_ms')
            ->first();

        if (!$latest) {
            return;
        }

        $expiresAt = Carbon::createFromTimestampMs(
            $latest['expires_date_ms']
        )->timezone('Asia/Kuala_Lumpur');

        // 状态判断
        if ( $expiresAt->isFuture() ) {
            $userSubscription->status = 10; // active
        } else {
            $userSubscription->status = 20; // expired
        }

        $userSubscription->end_date = $expiresAt;
        $userSubscription->save();
    }
}