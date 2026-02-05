<?php

namespace App\Listeners;

use App\Models\CallbackLog;
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
            $createLog = CallbackLog::create([
                'platform' => 'ios',
                'payload' => json_encode( $event->getServerNotification() ),
            ]);
            // ✅ 正确的 API 用法（1.12 版本）
            $notification = $event->getServerNotification();
            $subscription = $notification->getSubscription();

            $uniqueIdentifier = $subscription->getUniqueIdentifier(); // original_transaction_id

            Log::channel('payment')->info('App Store subscription canceled', [
                'original_transaction_id' => $uniqueIdentifier,
            ]);

            // 查找用户订阅
            $userSubscription = UserSubscription::where('platform', 1) // iOS
                ->where('platform_transaction_id', $uniqueIdentifier)
                ->first();

            if (!$userSubscription) {
                Log::channel('payment')->warning('Subscription not found for cancellation', [
                    'original_transaction_id' => $uniqueIdentifier,
                ]);
                return;
            }

            // 取消订阅
            $userSubscription->update([
                'status' => 40, // cancelled
                'cancelled_at' => now(),
                'auto_renew' => false,
            ]);

            // 从现有订阅获取 product_id
            $productId = 'unknown';
            if ($userSubscription->plan) {
                $productId = $userSubscription->plan->ios_product_id ?? 'unknown';
            }

            // 记录取消事件
            PaymentTransaction::create([
                'user_id' => $userSubscription->user_id,
                'user_subscription_id' => $userSubscription->id,
                'transaction_id' => 'cancel_ios_' . time() . '_' . $userSubscription->id,
                'original_transaction_id' => $uniqueIdentifier,
                'amount' => 0,
                'currency' => 'MYR',
                'platform' => 1, // iOS
                'product_id' => $productId,
                'status' => 10, // success
                'verified_at' => now(),
                'event_type' => 'CANCELLATION',
            ]);

            Log::channel('payment')->info('Subscription cancelled successfully', [
                'subscription_id' => $userSubscription->id,
            ]);

        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to handle App Store subscription cancellation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}