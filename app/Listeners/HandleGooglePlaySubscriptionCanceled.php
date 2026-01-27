<?php

namespace App\Listeners;

use Imdhemy\Purchases\Events\AppStore\Cancel;
use App\Models\UserSubscription;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class HandleAppStoreSubscriptionCanceled
{
    /**
     * Handle the event.
     */
    public function handle(Cancel $event)
    {
        try {
            $notification = $event->getServerNotification();
            $latestReceipt = $notification->getLatestReceiptInfo();

            if (!$latestReceipt) {
                Log::channel('payment')->warning('No latest receipt info in cancellation notification');
                return;
            }

            $transactionId = $latestReceipt->getTransactionId();
            $originalTransactionId = $latestReceipt->getOriginalTransactionId();
            $productId = $latestReceipt->getProductId();
            $cancellationDate = $latestReceipt->getCancellationDate();

            Log::channel('payment')->info('App Store subscription canceled', [
                'transaction_id' => $transactionId,
                'original_transaction_id' => $originalTransactionId,
                'cancellation_date' => $cancellationDate,
            ]);

            // 查找用户订阅
            $subscription = UserSubscription::where('platform', 'ios')
                ->where('platform_transaction_id', $originalTransactionId)
                ->first();

            if (!$subscription) {
                Log::channel('payment')->warning('Subscription not found for cancellation', [
                    'original_transaction_id' => $originalTransactionId,
                ]);
                return;
            }

            // 取消订阅
            $subscription->cancel();

            // 记录取消事件
            PaymentTransaction::create([
                'user_id' => $subscription->user_id,
                'user_subscription_id' => $subscription->id,
                'transaction_id' => 'cancel_' . $transactionId,
                'original_transaction_id' => $originalTransactionId,
                'amount' => 0,
                'currency' => 'USD',
                'platform' => 'ios',
                'product_id' => $productId,
                'status' => 'success',
                'verified_at' => now(),
                'event_type' => 'CANCELLATION',
            ]);

            Log::channel('payment')->info('Subscription cancelled successfully', [
                'subscription_id' => $subscription->id,
            ]);

        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to handle App Store subscription cancellation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}