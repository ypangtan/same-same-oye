<?php

namespace App\Listeners;

use Imdhemy\Purchases\Events\GooglePlay\SubscriptionCanceled;
use App\Models\UserSubscription;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class HandleGooglePlaySubscriptionCanceled
{
    /**
     * Handle the event.
     */
    public function handle(SubscriptionCanceled $event)
    {
        try {
            $notification = $event->getServerNotification();
            $subscriptionNotification = $notification->getSubscriptionNotification();
            
            $purchaseToken = $subscriptionNotification->getPurchaseToken();
            $subscriptionId = $subscriptionNotification->getSubscriptionId();

            Log::channel('payment')->info('Google Play subscription canceled', [
                'subscription_id' => $subscriptionId,
                'purchase_token' => $purchaseToken,
            ]);

            // 查找用户订阅
            $subscription = UserSubscription::where('platform', 'android')
                ->where('platform_receipt', 'like', '%' . $purchaseToken . '%')
                ->first();

            if (!$subscription) {
                Log::channel('payment')->warning('Subscription not found for cancellation', [
                    'purchase_token' => $purchaseToken,
                ]);
                return;
            }

            // 取消订阅
            $subscription->cancel();

            // 记录取消事件
            PaymentTransaction::create([
                'user_id' => $subscription->user_id,
                'user_subscription_id' => $subscription->id,
                'transaction_id' => 'cancel_' . time() . '_' . $subscription->id,
                'original_transaction_id' => $subscription->platform_transaction_id,
                'amount' => 0,
                'currency' => 'USD',
                'platform' => 'android',
                'product_id' => $subscriptionId,
                'status' => 'success',
                'verified_at' => now(),
                'event_type' => 'CANCELLATION',
            ]);

            Log::channel('payment')->info('Subscription cancelled successfully', [
                'subscription_id' => $subscription->id,
            ]);

        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to handle subscription cancellation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}