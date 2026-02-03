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
            $user = User::findOrFail($user_id);
            $plan = SubscriptionPlan::findOrFail($data['plan_id']);
            $receipt = $data['receipt_data'];

            // ── 判断收据类型 ──
            if (self::isJWS($receipt)) {
                // -------- StoreKit 2 --------
                $payload = self::verifyJWSLocal($receipt, $plan);
                $applePayload = self::verifyJWSServer($payload['signedTransactionInfo']);

                if ($applePayload['transactionId'] !== $payload['transactionId']) {
                    throw new Exception('Transaction mismatch between local and Apple server');
                }

            } else {
                // -------- StoreKit 1 --------
                $applePayload = self::verifyReceiptAppleServer($receipt);
                self::validateReceiptPayload($applePayload, $plan);
                $payload = [
                    'transactionId'          => $applePayload['transaction_id'] ?? $applePayload['original_transaction_id'],
                    'originalTransactionId'  => $applePayload['original_transaction_id'],
                    'expiresDate'            => $applePayload['expires_date_ms'] ?? now()->addMonth()->timestamp * 1000,
                    'productId'              => $applePayload['product_id'],
                    'environment'            => $applePayload['environment'] ?? 'Production',
                    'price'                  => $plan->price * 100,
                    'currency'               => 'MYR',
                    'transactionReason'      => 'INITIAL_PURCHASE',
                ];

                // 如果你想本地也做验签，可以在这里解析 Base64 receipt 进行简单校验
            }

            // ── 创建/更新订阅 & 交易记录 ──
            return self::createSubscriptionFromPayload($user, $plan, $payload, $receipt);

        } catch (Exception $e) {
            Log::channel('payment')->error('iOS verification failed', [
                'user_id' => $user_id,
                'error' => $e->getMessage(),
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

            $plan = SubscriptionPlan::findByPlatformProductId( 2, $productId );
            if (!$plan) {
                throw new Exception("Invalid product ID: {$productId}");
            }

            $response = Subscription::googlePlay()
                ->packageName($packageName)
                ->id($productId)
                ->token($purchaseToken)
                ->get();

            $expiryTimeMillis = $response->getExpiryTimeMillis();
            $startTimeMillis = $response->getStartTimeMillis();
            $orderId = $response->getOrderId();
            $autoRenewing = $response->getAutoRenewing();

            $expiresDate = Carbon::createFromTimestampMs($expiryTimeMillis);

            if (PaymentTransaction::exists($orderId)) {
                return [
                    'success' => true,
                    'message' => 'Transaction already processed',
                    'subscription' => $user->subscriptions()->where( 'platform', 2 )->active()->first(),
                ];
            }

            $subscription = self::createOrUpdateSubscriptionAndroid( $user_id, $plan->id, 2, $orderId, $expiresDate, $autoRenewing );

            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'user_subscription_id' => $subscription->id,
                'transaction_id' => $orderId,
                'original_transaction_id' => $orderId,
                'amount' => 0,
                'currency' => 'USD',
                'platform' => 2,
                'product_id' => $productId,
                'receipt_data' => json_encode(['purchase_token' => $purchaseToken]),
                'status' => 10,
                'verified_at' => now(),
                'verification_response' => json_encode($response->toArray()),
            ]);

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

    protected static function createOrUpdateSubscriptionAndroid( $user_id, $plan_id, $platform, $transactionId, $endDate, $autoRenew = true ) {
        $user = User::find( $user_id );
        $plan = SubscriptionPlan::find( $plan_id );

        $subscription = $user->subscriptions()
            ->where('platform', $platform)
            ->where('platform_transaction_id', $transactionId)
            ->first();

        if ($subscription) {
            $subscription->update([
                'status' => 10,
                'end_date' => $endDate,
                'auto_renew' => $autoRenew,
            ]);
        } else {
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

    // ─────────────────────────────────────────────
    // StoreKit 2 JWS 相關
    // ─────────────────────────────────────────────
    private static function verifyReceiptAppleServer(string $receiptData) {
        $url = config('liap.appstore_sandbox', true)
            ? 'https://sandbox.itunes.apple.com/verifyReceipt'
            : 'https://buy.itunes.apple.com/verifyReceipt';

        $client = new \GuzzleHttp\Client(['timeout' => 5]);

        $response = $client->post($url, [
            'json' => [
                'receipt-data' => $receiptData,
                'password' => config('liap.appstore_shared_secret'),
                'exclude-old-transactions' => false
            ],
        ]);

        $json = json_decode($response->getBody()->getContents(), true);

        if (empty($json) || !isset($json['status']) || $json['status'] !== 0) {
            throw new Exception('Apple server verification failed: ' . json_encode($json));
        }

        // 取最新的交易信息
        return end($json['latest_receipt_info'] ?? $json['receipt']['in_app'] ?? []);
    }


    private static function isJWS($data) {
        $parts = explode('.', $data);
        if (count($parts) !== 3) return false;

        $header = json_decode(self::base64url_decode($parts[0]), true);
        return isset($header['x5c'], $header['alg']);
    }
    
    private static function validateReceiptPayload(array $payload, SubscriptionPlan $plan) {
        $expectedBundleId = config('liap.ios_bundle_id', 'com.sama2oye.ios');
        if (($payload['bundle_id'] ?? $payload['bid'] ?? '') !== $expectedBundleId) {
            throw new Exception("Bundle ID mismatch: expected {$expectedBundleId}, got " . ($payload['bundle_id'] ?? ''));
        }

        if (($payload['product_id'] ?? '') !== $plan->ios_product_id) {
            throw new Exception("Product ID mismatch: expected {$plan->ios_product_id}, got " . ($payload['product_id'] ?? ''));
        }
    }

}