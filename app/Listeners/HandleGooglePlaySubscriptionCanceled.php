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
            // ✅ 正确的 API 用法（1.12 版本）
            $notification = $event->getServerNotification();
            $subscription = $notification->getSubscription();
            
            $uniqueIdentifier = $subscription->getUniqueIdentifier(); // purchase token

            Log::channel('payment')->info('Google Play subscription canceled', [
                'unique_identifier' => substr($uniqueIdentifier, 0, 20) . '...',
            ]);

            // 查找用户订阅
            $userSubscription = UserSubscription::where('platform', 2) // Android
                ->where('platform_receipt', 'like', '%' . substr($uniqueIdentifier, 0, 50) . '%')
                ->first();

            if (!$userSubscription) {
                Log::channel('payment')->warning('Subscription not found for cancellation', [
                    'unique_identifier' => substr($uniqueIdentifier, 0, 20) . '...',
                ]);
                return;
            }

            // 取消订阅
            $userSubscription->update([
                'status' => 30, // cancelled
                'cancelled_at' => now(),
                'auto_renew' => false,
            ]);

            // 从现有订阅获取 product_id
            $productId = 'unknown';
            if ($userSubscription->plan) {
                $productId = $userSubscription->plan->android_product_id ?? 'unknown';
            }

            // 记录取消事件
            PaymentTransaction::create([
                'user_id' => $userSubscription->user_id,
                'user_subscription_id' => $userSubscription->id,
                'transaction_id' => 'cancel_' . time() . '_' . $userSubscription->id,
                'original_transaction_id' => $userSubscription->platform_transaction_id,
                'amount' => 0,
                'currency' => 'MYR',
                'platform' => 2, // Android
                'product_id' => $productId,
                'status' => 10, // success
                'verified_at' => now(),
                'event_type' => 'CANCELLATION',
            ]);

            Log::channel('payment')->info('Subscription cancelled successfully', [
                'subscription_id' => $userSubscription->id,
            ]);

        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to handle subscription cancellation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}