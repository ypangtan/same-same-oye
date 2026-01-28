<?php

namespace App\Listeners;

use Imdhemy\Purchases\Events\GooglePlay\SubscriptionRenewed;
use App\Models\UserSubscription;
use App\Models\PaymentTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HandleGooglePlaySubscriptionRenewed
{
    /**
     * Handle the event.
     */
    public function handle(SubscriptionRenewed $event)
    {
        try {
            // ✅ 正确的 API 用法（1.12 版本）
            $notification = $event->getServerNotification();
            $subscription = $notification->getSubscription();
            
            // 获取订阅信息
            $uniqueIdentifier = $subscription->getUniqueIdentifier(); // purchase token
            $expiryTime = $subscription->getExpiryTime();

            Log::channel('payment')->info('Google Play subscription renewed', [
                'unique_identifier' => substr($uniqueIdentifier, 0, 20) . '...',
                'expiry_time' => $expiryTime ? $expiryTime->getCarbon()->toDateTimeString() : null,
            ]);

            // 查找用户订阅 - 通过 purchase_token (unique_identifier)
            $userSubscription = UserSubscription::where('platform', 2) // Android
                ->where('platform_receipt', 'like', '%' . substr($uniqueIdentifier, 0, 50) . '%')
                ->first();

            if (!$userSubscription) {
                Log::channel('payment')->warning('Subscription not found for renewal', [
                    'unique_identifier' => substr($uniqueIdentifier, 0, 20) . '...',
                ]);
                return;
            }

            // 获取新的过期时间
            $newEndDate = $expiryTime->getCarbon();

            // 更新订阅
            $userSubscription->update([
                'status' => 10, // active
                'end_date' => $newEndDate,
                'last_renewal_check' => now(),
            ]);

            // 从现有订阅获取 product_id（因为 subscription 对象没有 getProductId()）
            $productId = 'unknown';
            if ($userSubscription->plan) {
                $productId = $userSubscription->plan->android_product_id ?? 'unknown';
            }

            // 记录续费交易
            PaymentTransaction::create([
                'user_id' => $userSubscription->user_id,
                'user_subscription_id' => $userSubscription->id,
                'transaction_id' => 'renewal_' . time() . '_' . $userSubscription->id,
                'original_transaction_id' => $userSubscription->platform_transaction_id,
                'amount' => $userSubscription->plan->price ?? 0,
                'currency' => 'MYR',
                'platform' => 2, // Android
                'product_id' => $productId,
                'receipt_data' => json_encode(['purchase_token' => $uniqueIdentifier]),
                'status' => 10, // success
                'verified_at' => now(),
                'event_type' => 'RENEWAL',
            ]);

            Log::channel('payment')->info('Subscription renewed successfully', [
                'subscription_id' => $userSubscription->id,
                'new_end_date' => $newEndDate->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to handle subscription renewal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}