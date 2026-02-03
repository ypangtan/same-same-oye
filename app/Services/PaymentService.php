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
            $jws  = $data['receipt_data'];

            return $jws;

            if (!self::isJWS($jws)) {
                throw new Exception('Invalid receipt format: not a StoreKit 2 JWS');
            }

            // Step 1: 本地验证 JWS
            $payload = self::verifyJWSLocal($jws, $plan);

            // Step 2: 调用 Apple Server API 验证
            $applePayload = self::verifyJWSServer($payload['signedTransactionInfo']);

            // Step 3: 对比交易信息
            if ($applePayload['transactionId'] !== $payload['transactionId']) {
                throw new Exception('Transaction mismatch between local and Apple server');
            }

            // Step 4: 创建/更新订阅 & 交易记录
            return self::createSubscriptionFromPayload($user, $plan, $payload, $jws);

        } catch (Exception $e) {
            Log::channel('payment')->error('iOS JWS verification failed', [
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
    private static function verifyJWSLocal(string $jws, SubscriptionPlan $plan) {
        $parts = explode('.', $jws);
        if (count($parts) !== 3) {
            throw new Exception('Invalid JWS format');
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $header = json_decode(self::base64url_decode($headerB64), true);

        // 验证证书链
        self::verifyCertChain($header['x5c']);

        // 验签
        $leafPem = self::x5cToPem($header['x5c'][0]);
        $signedData = $headerB64 . '.' . $payloadB64;
        $signature = self::base64url_decode($signatureB64);

        $algo = match($header['alg']) {
            'ES256' => OPENSSL_ALGO_SHA256,
            'ES384' => OPENSSL_ALGO_SHA384,
            'ES512' => OPENSSL_ALGO_SHA512,
            default => throw new Exception('Unsupported JWS algorithm: ' . $header['alg']),
        };

        $pubKey = openssl_pkey_get_public($leafPem);
        if (!$pubKey) throw new Exception('Failed to extract public key');

        if (openssl_verify($signedData, $signature, $pubKey, $algo) !== 1) {
            throw new Exception('JWS signature verification failed');
        }
        openssl_pkey_free($pubKey);

        $payload = json_decode(self::base64url_decode($payloadB64), true);

        self::validateJWSPayload($payload, $plan);

        return $payload;
    }

    /**
     * Call Apple Server API to validate signedTransactionInfo
     */
    private static function verifyJWSServer(string $signedTransactionInfo) {
        $client = new Client([
            'base_uri' => 'https://buy.itunes.apple.com/', // Production URL
            'timeout' => 5,
        ]);

        $url = config('liap.appstore_sandbox', true)
            ? 'https://sandbox.itunes.apple.com/verifyReceipt'
            : 'https://buy.itunes.apple.com/verifyReceipt';

        $response = $client->post($url, [
            'json' => [
                'signedTransactionInfo' => $signedTransactionInfo,
            ],
        ]);

        $json = json_decode($response->getBody()->getContents(), true);

        if (empty($json) || !isset($json['status']) || $json['status'] !== 0) {
            throw new Exception('Apple server verification failed: ' . json_encode($json));
        }

        // signedTransactionInfo inside response
        if (empty($json['signedTransactionInfo'])) {
            throw new Exception('Apple server response missing signedTransactionInfo');
        }

        return json_decode(base64_decode($json['signedTransactionInfo']), true);
    }

    /**
     * Create or update subscription & transaction
     */
    private static function createSubscriptionFromPayload(User $user, SubscriptionPlan $plan, array $payload, string $jws) {
        $transactionId         = $payload['transactionId'];
        $originalTransactionId = $payload['originalTransactionId'];
        $expiresDate           = Carbon::createFromTimestampMs($payload['expiresDate']);
        $isRenew               = ($payload['transactionReason'] ?? '') === 'RENEWAL';
        $amount                = ($payload['price'] ?? 0) / 100;
        $currency              = $payload['currency'] ?? 'MYR';

        if (PaymentTransaction::exists($transactionId)) {
            return [
                'success' => true,
                'message' => 'Transaction already processed',
                'subscription' => $user->subscriptions()->where('platform', 1)->active()->first(),
            ];
        }

        $subscription = self::createOrUpdateSubscription(
            $user->id, $plan->id, 1, $originalTransactionId, $expiresDate, $isRenew
        );

        $transaction = PaymentTransaction::create([
            'user_id'                  => $user->id,
            'user_subscription_id'     => $subscription->id,
            'transaction_id'           => $transactionId,
            'original_transaction_id'  => $originalTransactionId,
            'amount'                   => $amount,
            'currency'                 => $currency,
            'platform'                 => 1,
            'product_id'               => $payload['productId'],
            'receipt_data'             => $jws,
            'status'                   => 10,
            'verified_at'              => now(),
            'verification_response'    => json_encode($payload),
        ]);

        Log::channel('payment')->info('iOS purchase verified (JWS / StoreKit 2)', [
            'user_id'        => $user->id,
            'transaction_id' => $transactionId,
            'environment'    => $payload['environment'],
            'amount'         => $amount,
            'currency'       => $currency,
        ]);

        return [
            'success'      => true,
            'message'      => 'Subscription activated successfully',
            'subscription' => $subscription->fresh(),
            'transaction'  => $transaction,
        ];
    }

    // ─── Helper / Validation ───

    private static function createOrUpdateSubscription(int $user_id, int $plan_id, int $platform, string $transactionId, Carbon $endDate, bool $autoRenew = true)
    {
        $user = User::findOrFail($user_id);

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
                'subscription_plan_id' => $plan_id,
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

    private static function isJWS(string $data) {
        $parts = explode('.', $data);
        if (count($parts) !== 3) return false;

        $header = json_decode(self::base64url_decode($parts[0]), true);
        return isset($header['x5c'], $header['alg']);
    }

    private static function verifyCertChain(array $x5c): void
    {
        if (count($x5c) < 3) {
            throw new Exception('JWS cert chain too short, expected 3 certs');
        }

        $leaf         = self::x5cToPem($x5c[0]);
        $intermediate = self::x5cToPem($x5c[1]);
        $root         = self::x5cToPem($x5c[2]);

        if (openssl_x509_verify($leaf, $intermediate) !== 1) {
            throw new Exception('Leaf cert not signed by intermediate');
        }

        if (openssl_x509_verify($intermediate, $root) !== 1) {
            throw new Exception('Intermediate cert not signed by root');
        }

        $rootInfo = openssl_x509_parse($root);
        if (!str_contains($rootInfo['subject']['O'] ?? '', 'Apple')) {
            throw new Exception('Root cert is not issued by Apple');
        }
    }

    private static function validateJWSPayload(array $payload, SubscriptionPlan $plan) {
        $expectedBundleId = config('liap.ios_bundle_id', 'com.sama2oye.ios');
        if ($payload['bundleId'] !== $expectedBundleId) {
            throw new Exception("Bundle ID mismatch: expected {$expectedBundleId}, got {$payload['bundleId']}");
        }

        if ($payload['productId'] !== $plan->ios_product_id) {
            throw new Exception("Product ID mismatch: expected {$plan->ios_product_id}, got {$payload['productId']}");
        }

        $isSandbox   = config('liap.appstore_sandbox', true);
        $expectedEnv = $isSandbox ? 'Sandbox' : 'Production';
        if ($payload['environment'] !== $expectedEnv) {
            throw new Exception("Environment mismatch: expected {$expectedEnv}, got {$payload['environment']}");
        }
    }

    private static function base64url_decode(string $data) {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    private static function x5cToPem(string $certB64) {
        return "-----BEGIN CERTIFICATE-----\n"
            . chunk_split($certB64, 64, "\n")
            . "-----END CERTIFICATE-----";
    }
}