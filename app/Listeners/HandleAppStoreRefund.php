<?php

namespace App\Listeners;

use Imdhemy\Purchases\Events\AppStore\Refund;
use App\Models\UserSubscription;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class HandleAppStoreRefund
{
    /**
     * Handle the event.
     */
    public function handle(Refund $event)
    {
        try {
            // ✅ 正确的 API 用法（1.12 版本）
            $notification = $event->getServerNotification();
            $subscription = $notification->getSubscription();

            $uniqueIdentifier = $subscription->getUniqueIdentifier(); // original_transaction_id

            Log::channel('payment')->info('App Store refund received', [
                'original_transaction_id' => $uniqueIdentifier,
            ]);

            // 查找用户订阅
            $userSubscription = UserSubscription::where('platform', 1) // iOS
                ->where('platform_transaction_id', $uniqueIdentifier)
                ->first();

            if (!$userSubscription) {
                Log::channel('payment')->warning('Subscription not found for refund', [
                    'original_transaction_id' => $uniqueIdentifier,
                ]);
                return;
            }

            // 标记订阅为已退款
            $userSubscription->update([
                'status' => 30, // refunded
                'auto_renew' => false,
            ]);

            // 从现有订阅获取 product_id
            $productId = 'unknown';
            if ($userSubscription->plan) {
                $productId = $userSubscription->plan->ios_product_id ?? 'unknown';
            }

            // 记录退款事件
            PaymentTransaction::create([
                'user_id' => $userSubscription->user_id,
                'user_subscription_id' => $userSubscription->id,
                'transaction_id' => 'refund_ios_' . time() . '_' . $userSubscription->id,
                'original_transaction_id' => $uniqueIdentifier,
                'amount' => 0,
                'currency' => 'MYR',
                'platform' => 1, // iOS
                'product_id' => $productId,
                'status' => 30, // refunded
                'verified_at' => now(),
                'event_type' => 'REFUND',
            ]);

            Log::channel('payment')->info('Subscription refunded successfully', [
                'subscription_id' => $userSubscription->id,
            ]);

        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to handle App Store refund', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}