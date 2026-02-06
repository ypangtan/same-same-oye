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
            $expiryDate = Carbon::createFromTimestampMs( $rawData['expires_date_ms'] )
                ->timezone('Asia/Kuala_Lumpur');

            return $expiryDate;
            // $expiryDate = Carbon::now()->timezone( 'Asia/Kuala_Lumpur' )->addYears( $plan->duration_in_years )->addMonths( $plan->duration_in_months )->addDays( $plan->duration_in_days );

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
            
            $expiryTime = $lineItem[0]->getExpiryTime();
            if ($expiryTime) {
                $expiredDate = Carbon::parse($expiryTime)->timezone('Asia/Kuala_Lumpur');
            } else {
                throw new \Exception('Invalid subscription purchase (no expiry time)');
            }
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
}