<?php

namespace App\Listeners;

use Imdhemy\Purchases\Events\AppStore\DidRenew;
use App\Models\UserSubscription;
use App\Models\PaymentTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HandleAppStoreSubscriptionRenewed
{
    /**
     * Handle the event.
     */
    public function handle(DidRenew $event)
    {
        try {
            $notification = $event->getServerNotification();
            $latestReceipt = $notification->getLatestReceiptInfo();

            if (!$latestReceipt) {
                Log::channel('payment')->warning('No latest receipt info in renewal notification');
                return;
            }

            $transactionId = $latestReceipt->getTransactionId();
            $originalTransactionId = $latestReceipt->getOriginalTransactionId();
            $expiresDate = $latestReceipt->getExpiresDate();
            $productId = $latestReceipt->getProductId();

            Log::channel('payment')->info('App Store subscription renewed', [
                'transaction_id' => $transactionId,
                'original_transaction_id' => $originalTransactionId,
                'product_id' => $productId,
            ]);

            // 查找用户订阅
            $subscription = UserSubscription::where('platform', 'ios')
                ->where('platform_transaction_id', $originalTransactionId)
                ->first();

            if (!$subscription) {
                Log::channel('payment')->warning('Subscription not found for renewal', [
                    'original_transaction_id' => $originalTransactionId,
                ]);
                return;
            }

            // 检查交易是否已处理
            if (PaymentTransaction::exists($transactionId)) {
                Log::channel('payment')->info('Transaction already processed', [
                    'transaction_id' => $transactionId,
                ]);
                return;
            }

            // 更新订阅过期时间
            $newEndDate = Carbon::createFromTimestamp($expiresDate->getTimestamp());
            $subscription->update([
                'status' => 'active',
                'end_date' => $newEndDate,
                'last_renewal_check' => now(),
            ]);

            // 记录续费交易
            PaymentTransaction::create([
                'user_id' => $subscription->user_id,
                'user_subscription_id' => $subscription->id,
                'transaction_id' => $transactionId,
                'original_transaction_id' => $originalTransactionId,
                'amount' => 0, // iOS 不提供价格
                'currency' => 'USD',
                'platform' => 'ios',
                'product_id' => $productId,
                'status' => 'success',
                'verified_at' => now(),
                'event_type' => 'RENEWAL',
            ]);

            Log::channel('payment')->info('Subscription renewed successfully', [
                'subscription_id' => $subscription->id,
                'new_end_date' => $newEndDate,
            ]);

        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to handle App Store subscription renewal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}