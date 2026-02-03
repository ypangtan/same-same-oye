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

            $user = User::find( $user_id );
            $receiptData = $data['receipt_data'];
            $plan = SubscriptionPlan::find( $data['plan_id'] );
            $productId = $plan->ios_product_id;

            // ── 判斷是 JWS（StoreKit 2）還是傳統 receipt（StoreKit 1）──
            if (self::isJWS($receiptData)) {
                return self::verifyJWS($receiptData, $user, $plan);
            }

            // ── 以下是原來的 imdhemy 流程（StoreKit 1）──
            $isSandbox = config('liap.appstore_sandbox', true);
            $client = AppStoreClientFactory::create($isSandbox);

            $response = Subscription::appStore( $client )
                ->receiptData($receiptData)
                ->verifyRenewable();

            $status = $response->getStatus();

            if ($status === 21007) {
                Log::channel('payment')->warning('Sandbox receipt sent to production');
            }

            if ($status === 21008) {
                Log::channel('payment')->warning('Production receipt sent to sandbox environment');
            }

            if ($status !== 0) {
                throw new Exception("Receipt verification failed with status: " . $status);
            }

            $latestReceipt = $response->getLatestReceiptInfo();
            if (empty($latestReceipt)) {
                throw new Exception("No receipt info found");
            }

            $receiptInfo = $latestReceipt[0];
            $transactionId = $receiptInfo->getTransactionId();
            $originalTransactionId = $receiptInfo->getOriginalTransactionId();
            $expiresDate = $receiptInfo->getExpiresDate();

            if ( PaymentTransaction::exists( $transactionId ) ) {
                return [
                    'success' => true,
                    'message' => 'Transaction already processed',
                    'subscription' => $user->subscriptions()->where('platform', 1)->active()->first(),
                ];
            }

            $expiredDate = Carbon::createFromTimestamp( $expiresDate->getTimestamp() );
            $isRenew = $receiptInfo->getAutoRenewStatus() === '1';
            $subscription = self::createOrUpdateSubscription( $user_id, $plan->id, 1, $originalTransactionId, $expiredDate, $isRenew );

            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'user_subscription_id' => $subscription->id,
                'transaction_id' => $transactionId,
                'original_transaction_id' => $originalTransactionId,
                'amount' => 0,
                'currency' => 'USD',
                'platform' => 1,
                'product_id' => $productId,
                'receipt_data' => $receiptData,
                'status' => 10,
                'verified_at' => now(),
                'verification_response' => json_encode($response->toArray()),
            ]);

            Log::channel('payment')->info('iOS purchase verified (legacy receipt)', [
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => true,
                'message' => 'Subscription activated successfully',
                'subscription' => $subscription->fresh(),
                'transaction' => $transaction,
            ];

        } catch (Exception $e) {
            Log::channel('payment')->error('iOS verification failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'previous' => $e->getPrevious() ? [
                    'message' => $e->getPrevious()->getMessage(),
                    'file' => $e->getPrevious()->getFile(),
                    'line' => $e->getPrevious()->getLine(),
                    'class' => get_class($e->getPrevious()),
                ] : null,
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

            $subscription = self::createOrUpdateSubscription( $user_id, $plan->id, 2, $orderId, $expiresDate, $autoRenewing );

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

    protected static function createOrUpdateSubscription( $user_id, $plan_id, $platform, $transactionId, $endDate, $autoRenew = true ) {
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

    private static function isJWS( string $data ): bool
    {
        return substr_count($data, '.') === 2 && str_starts_with($data, 'eyJhbGci');
    }

    private static function verifyJWS(string $jws, User $user, SubscriptionPlan $plan): array
    {
        // Step 1: 拆開三段
        $parts = explode('.', $jws);
        if (count($parts) !== 3) {
            throw new Exception('Invalid JWS format');
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        // Step 2: Decode header，拿 x5c cert chain
        $header = json_decode(self::base64url_decode($headerB64), true);
        if (empty($header['x5c']) || empty($header['alg'])) {
            throw new Exception('JWS header missing x5c or alg');
        }

        // Step 3: 驗證 certificate chain
        self::verifyCertChain($header['x5c']);

        // Step 4: 用 leaf cert 驗證 signature
        $leafPem     = self::x5cToPem($header['x5c'][0]);
        $signedData  = $headerB64 . '.' . $payloadB64;
        $signature   = self::base64url_decode($signatureB64);

        $algo = match($header['alg']) {
            'ES256' => OPENSSL_ALGO_SHA256,
            'ES384' => OPENSSL_ALGO_SHA384,
            'ES512' => OPENSSL_ALGO_SHA512,
            default => throw new Exception('Unsupported JWS algorithm: ' . $header['alg']),
        };

        $pubKey = openssl_pkey_get_public($leafPem);
        if (!$pubKey) {
            throw new Exception('Failed to extract public key from leaf cert');
        }

        if (openssl_verify($signedData, $signature, $pubKey, $algo) !== 1) {
            throw new Exception('JWS signature verification failed');
        }
        openssl_pkey_free($pubKey);

        // Step 5: Decode payload
        $payload = json_decode(self::base64url_decode($payloadB64), true);

        // Step 6: 驗證 payload 欄位
        self::validateJWSPayload($payload, $plan);

        // ── 處理訂閱和交易記錄 ──

        $transactionId         = $payload['transactionId'];
        $originalTransactionId = $payload['originalTransactionId'];
        $expiresDate           = Carbon::createFromTimestampMs($payload['expiresDate']);

        if ( PaymentTransaction::exists($transactionId) ) {
            return [
                'success' => true,
                'message' => 'Transaction already processed',
                'subscription' => $user->subscriptions()->where('platform', 1)->active()->first(),
            ];
        }

        $isRenew = ($payload['transactionReason'] ?? '') === 'RENEWAL';
        $subscription = self::createOrUpdateSubscription(
            $user->id, $plan->id, 1, $originalTransactionId, $expiresDate, $isRenew
        );

        // Apple price 是 milliunits → 除以 100 才是正常金額
        // 例如 24900 = 249.00 MYR... 不對，24900 milliunits = 24.90 MYR (price 的單位是 1/1000)
        // Apple docs: price is in the smallest currency unit (e.g., cents)
        // MYR 沒有 sub-unit，所以直接除 100
        $amount = ($payload['price'] ?? 0) / 100;

        $transaction = PaymentTransaction::create([
            'user_id'                  => $user->id,
            'user_subscription_id'     => $subscription->id,
            'transaction_id'           => $transactionId,
            'original_transaction_id'  => $originalTransactionId,
            'amount'                   => $amount,
            'currency'                 => $payload['currency'] ?? 'MYR',
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
            'currency'       => $payload['currency'] ?? 'MYR',
        ]);

        return [
            'success'      => true,
            'message'      => 'Subscription activated successfully',
            'subscription' => $subscription->fresh(),
            'transaction'  => $transaction,
        ];
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

    private static function validateJWSPayload(array $payload, SubscriptionPlan $plan): void
    {
        // bundleId — 用和原來同一個 config namespace (liap)
        $expectedBundleId = config('liap.ios_bundle_id', 'com.sama2oye.ios');
        if ($payload['bundleId'] !== $expectedBundleId) {
            throw new Exception("Bundle ID mismatch: expected {$expectedBundleId}, got {$payload['bundleId']}");
        }

        // productId
        if ($payload['productId'] !== $plan->ios_product_id) {
            throw new Exception("Product ID mismatch: expected {$plan->ios_product_id}, got {$payload['productId']}");
        }

        // environment — 用和原來同一個 config (liap.appstore_sandbox)
        $isSandbox   = config('liap.appstore_sandbox', true);
        $expectedEnv = $isSandbox ? 'Sandbox' : 'Production';
        if ($payload['environment'] !== $expectedEnv) {
            throw new Exception("Environment mismatch: expected {$expectedEnv}, got {$payload['environment']}");
        }
    }

    // ─── Utility ───

    private static function base64url_decode(string $data): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    private static function x5cToPem(string $certB64): string
    {
        return "-----BEGIN CERTIFICATE-----\n"
            . chunk_split($certB64, 64, "\n")
            . "-----END CERTIFICATE-----";
    }
}