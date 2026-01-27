<?php

namespace App\Listeners;

use Imdhemy\Purchases\Events\GooglePlay\SubscriptionRenewed;
use App\Models\{
    UserSubscription,
    PaymentTransaction,
};
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HandleGooglePlaySubscriptionRenewed {

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
        // 
    }

    public function handle( SubscriptionRenewed $event ) {
        try {
            $notification = $event->getServerNotification();
            $subscriptionNotification = $notification->getSubscriptionNotification();
            
            // 获取订阅信息
            $purchaseToken = $subscriptionNotification->getPurchaseToken();
            $subscriptionId = $subscriptionNotification->getSubscriptionId();

            Log::channel('payment')->info('Google Play subscription renewed', [
                'subscription_id' => $subscriptionId,
                'purchase_token' => $purchaseToken,
            ]);

            // 查找用户订阅
            $subscription = UserSubscription::where('platform', 'android')
                ->where('platform_receipt', 'like', '%' . $purchaseToken . '%')
                ->first();

            if (!$subscription) {
                Log::channel('payment')->warning('Subscription not found for renewal', [
                    'purchase_token' => $purchaseToken,
                ]);
                return;
            }

            // 获取新的过期时间
            // 需要调用 Google Play API 获取最新信息
            $newExpiryTime = $this->getExpiryTimeFromGoogle($subscriptionId, $purchaseToken);

            if ($newExpiryTime) {
                // 续费订阅
                $daysToAdd = $subscription->plan->duration_days;
                $subscription->renew($daysToAdd);

                // 记录续费交易
                PaymentTransaction::create([
                    'user_id' => $subscription->user_id,
                    'user_subscription_id' => $subscription->id,
                    'transaction_id' => 'renewal_' . time() . '_' . $subscription->id,
                    'original_transaction_id' => $subscription->platform_transaction_id,
                    'amount' => $subscription->plan->price,
                    'currency' => 'USD',
                    'platform' => 'android',
                    'product_id' => $subscriptionId,
                    'receipt_data' => json_encode(['purchase_token' => $purchaseToken]),
                    'status' => 'success',
                    'verified_at' => now(),
                    'event_type' => 'RENEWAL',
                ]);

                Log::channel('payment')->info('Subscription renewed successfully', [
                    'subscription_id' => $subscription->id,
                    'new_end_date' => $subscription->end_date,
                ]);
            }

        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to handle subscription renewal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function getExpiryTimeFromGoogle($subscriptionId, $purchaseToken) {
        try {
            $response = \Imdhemy\Purchases\Facades\Subscription::googlePlay()
                ->packageName(config('liap.google_play_package_name'))
                ->id($subscriptionId)
                ->token($purchaseToken)
                ->get();

            return Carbon::createFromTimestampMs($response->getExpiryTimeMillis());
        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to get expiry time from Google', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}