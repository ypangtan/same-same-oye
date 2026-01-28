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
            // ✅ 正确的 API 用法（1.12 版本）
            $notification = $event->getServerNotification();
            $subscription = $notification->getSubscription();

            $uniqueIdentifier = $subscription->getUniqueIdentifier(); // original_transaction_id
            $expiryTime = $subscription->getExpiryTime();

            Log::channel('payment')->info('App Store subscription renewed', [
                'original_transaction_id' => $uniqueIdentifier,
                'expiry_time' => $expiryTime ? $expiryTime->getCarbon()->toDateTimeString() : null,
            ]);

            // 查找用户订阅
            $userSubscription = UserSubscription::where('platform', 1) // iOS
                ->where('platform_transaction_id', $uniqueIdentifier)
                ->first();

            if (!$userSubscription) {
                Log::channel('payment')->warning('Subscription not found for renewal', [
                    'original_transaction_id' => $uniqueIdentifier,
                ]);
                return;
            }

            // 更新订阅过期时间
            $newEndDate = $expiryTime->getCarbon();
            $userSubscription->update([
                'status' => 10, // active
                'end_date' => $newEndDate,
                'last_renewal_check' => now(),
            ]);

            // 从现有订阅获取 product_id
            $productId = 'unknown';
            if ($userSubscription->plan) {
                $productId = $userSubscription->plan->ios_product_id ?? 'unknown';
            }

            // 记录续费交易
            PaymentTransaction::create([
                'user_id' => $userSubscription->user_id,
                'user_subscription_id' => $userSubscription->id,
                'transaction_id' => 'renewal_ios_' . time() . '_' . $userSubscription->id,
                'original_transaction_id' => $uniqueIdentifier,
                'amount' => $userSubscription->plan->price ?? 0,
                'currency' => 'MYR',
                'platform' => 1, // iOS
                'product_id' => $productId,
                'status' => 10, // success
                'verified_at' => now(),
                'event_type' => 'RENEWAL',
            ]);

            Log::channel('payment')->info('Subscription renewed successfully', [
                'subscription_id' => $userSubscription->id,
                'new_end_date' => $newEndDate->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to handle App Store subscription renewal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}