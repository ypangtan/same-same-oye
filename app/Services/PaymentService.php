<?php

namespace App\Services;

use App\Models\{
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
use Illuminate\Support\Facades\Log;
use Imdhemy\AppStore\ClientFactory as AppStoreClientFactory;

class PaymentService {

    public static function verifyIOSPurchase( $user_id, $data ) {
        try {
            $user = User::find( $user_id );
            $receiptData = $data['receipt_data'];
            $plan = SubscriptionPlan::find( $data['plan_id'] );
            $productId = $plan->ios_product_id;
            

            // plugin 没有处理sandbox和生产环境切换，这里手动处理
            $isSandbox = config('liap.appstore_sandbox', true);
            $client = $isSandbox
                ? AppStoreClientFactory::createForITunesSandbox()
                : AppStoreClientFactory::createForITunes();

            // 验证收据
            $response = Subscription::appStore( $client )
                ->receiptData($receiptData)
                ->verifyRenewable();

            $status = $response->getStatus();

            // 处理状态码 21007 (沙盒收据发到了生产环境)
            if ($status === 21007) {
                Log::channel('payment')->warning('Sandbox receipt sent to production');
            }

            // 处理状态码 21008 (生产收据发到了沙盒环境)
            if ($status === 21008) {
                Log::channel('payment')->warning('Production receipt sent to sandbox environment');
            }

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
            $expiresDate = $receiptInfo->getExpiresDate();

            // 检查交易是否已存在
            if ( PaymentTransaction::exists( $transactionId ) ) {
                return [
                    'success' => true,
                    'message' => 'Transaction already processed',
                    'subscription' => $user->subscriptions()->where('platform', 'ios')->active()->first(),
                ];
            }

            // 创建或更新订阅
            $expiredDate = Carbon::createFromTimestamp( $expiresDate->getTimestamp() );
            $isRenew = $receiptInfo->getAutoRenewStatus() === '1';
            $subscription = self::createOrUpdateSubscription( $user_id, $plan->id, 1, $originalTransactionId, $expiredDate, $isRenew );

            // 记录交易
            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'user_subscription_id' => $subscription->id,
                'transaction_id' => $transactionId,
                'original_transaction_id' => $originalTransactionId,
                'amount' => 0,
                'currency' => 'USD',
                'platform' => 1,
                'product_id' => $productId,
                'receipt_data' => $receiptData,
                'status' => 10,
                'verified_at' => now(),
                'verification_response' => json_encode($response->toArray()),
            ]);

            Log::channel('payment')->info('iOS purchase verified', [
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => true,
                'message' => 'Subscription activated successfully',
                'subscription' => $subscription->fresh(),
                'transaction' => $transaction,
            ];

        } catch (Exception $e) {
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
            $user = User::find( $user_id );
            $plan = SubscriptionPlan::find( $data['plan_id'] );
            $productId = $plan->android_product_id;
            $purchaseToken = $data['purchase_token'];
            $packageName = config('liap.google_play_package_name');

            // 查找订阅方案
            $plan = SubscriptionPlan::findByPlatformProductId( 2, $productId );
            if (!$plan) {
                throw new Exception("Invalid product ID: {$productId}");
            }

            // 验证订阅
            $response = Subscription::googlePlay()
                ->packageName($packageName)
                ->id($productId)
                ->token($purchaseToken)
                ->get();

            // 获取订阅信息
            $expiryTimeMillis = $response->getExpiryTimeMillis();
            $startTimeMillis = $response->getStartTimeMillis();
            $orderId = $response->getOrderId();
            $autoRenewing = $response->getAutoRenewing();

            $expiresDate = Carbon::createFromTimestampMs($expiryTimeMillis);

            // 检查交易是否已存在
            if (PaymentTransaction::exists($orderId)) {
                return [
                    'success' => true,
                    'message' => 'Transaction already processed',
                    'subscription' => $user->subscriptions()->where( 'platform', 2 )->active()->first(),
                ];
            }

            // 创建或更新订阅
            $subscription = self::createOrUpdateSubscription( $user_id, $plan->id, 2, $orderId, $expiresDate, $autoRenewing );

            // 记录交易
            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'user_subscription_id' => $subscription->id,
                'transaction_id' => $orderId,
                'original_transaction_id' => $orderId,
                'amount' => 0, // Google 不直接提供价格
                'currency' => 'USD',
                'platform' => 2,
                'product_id' => $productId,
                'receipt_data' => json_encode(['purchase_token' => $purchaseToken]),
                'status' => 10,
                'verified_at' => now(),
                'verification_response' => json_encode($response->toArray()),
            ]);

            // 确认购买（告诉 Google 已经处理）
            Subscription::googlePlay()
                ->packageName($packageName)
                ->id($productId)
                ->token($purchaseToken)
                ->acknowledge();

            Log::channel('payment')->info('Android purchase verified', [
                'user_id' => $user->id,
                'transaction_id' => $orderId,
            ]);

            return [
                'success' => true,
                'message' => 'Subscription activated successfully',
                'subscription' => $subscription->fresh(),
                'transaction' => $transaction,
            ];

        } catch (Exception $e) {
            Log::channel('payment')->error('Android verification failed', [
                'user_id' => $user->id,
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
                'auto_renew' => $autoRenew,
            ]);
        } else {
            // 创建新订阅
            $subscription = UserSubscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => 10,
                'start_date' => now(),
                'end_date' => $endDate,
                'platform' => $platform,
                'platform_transaction_id' => $transactionId,
                'auto_renew' => $autoRenew,
            ]);
        }

        return $subscription;
    }
}