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
            // JWS: 三段 dot 分隔，開頭是 eyJhbGci
            // 傳統: 一段 base64，開頭是 MII
            if (self::isJWS($receiptData)) {
                return self::verifyJWS($receiptData, $user, $plan);
            }

            // plugin 没有处理sandbox和生产环境切换，这里手动处理
            $isSandbox = config('liap.appstore_sandbox', true);
            $client = AppStoreClientFactory::create($isSandbox);

            // 验证收据
            $response = Subscription::appStore( $client )
                ->receiptData($receiptData)
                ->verifyRenewable();

            $status = $response->getStatus();

            // 处理状态码 21007 (沙盒收据发到了生产环境)
            if ($status === 21007) {
                Log::channel('payment')->warning('Sandbox receipt sent to production');
            }

            // 处理状态码 21008 (生产收据发到了沙盒环境)
            if ($status === 21008) {
                Log::channel('payment')->warning('Production receipt sent to sandbox environment');
            }

            // 检查验证状态
            if ($status !== 0) {
                throw new Exception("Receipt verification failed with status: " . $status);
            }

            // 获取最新的收据信息
            $latestReceipt = $response->getLatestReceiptInfo();
            if (empty($latestReceipt)) {
                throw new Exception("No receipt info found");
            }

            $receiptInfo = $latestReceipt[0];
            $transactionId = $receiptInfo->getTransactionId();
            $originalTransactionId = $receiptInfo->getOriginalTransactionId();
            $expiresDate = $receiptInfo->getExpiresDate();

            // 检查交易是否已存在
            if ( PaymentTransaction::exists( $transactionId ) ) {
                return [
                    'success' => true,
                    'message' => 'Transaction already processed',
                    'subscription' => $user->subscriptions()->where('platform', 'ios')->active()->first(),
                ];
            }

            // 创建或更新订阅
            $expiredDate = Carbon::createFromTimestamp( $expiresDate->getTimestamp() );
            $isRenew = $receiptInfo->getAutoRenewStatus() === '1';
            $subscription = self::createOrUpdateSubscription( $user_id, $plan->id, 1, $originalTransactionId, $expiredDate, $isRenew );

            // 记录交易
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

            Log::channel('payment')->info('iOS purchase verified', [
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

            // 查找订阅方案
            $plan = SubscriptionPlan::findByPlatformProductId( 2, $productId );
            if (!$plan) {
                throw new Exception("Invalid product ID: {$productId}");
            }

            // 验证订阅
            $response = Subscription::googlePlay()
                ->packageName($packageName)
                ->id($productId)
                ->token($purchaseToken)
                ->get();

            // 获取订阅信息
            $expiryTimeMillis = $response->getExpiryTimeMillis();
            $startTimeMillis = $response->getStartTimeMillis();
            $orderId = $response->getOrderId();
            $autoRenewing = $response->getAutoRenewing();

            $expiresDate = Carbon::createFromTimestampMs($expiryTimeMillis);

            // 检查交易是否已存在
            if (PaymentTransaction::exists($orderId)) {
                return [
                    'success' => true,
                    'message' => 'Transaction already processed',
                    'subscription' => $user->subscriptions()->where( 'platform', 2 )->active()->first(),
                ];
            }

            // 创建或更新订阅
            $subscription = self::createOrUpdateSubscription( $user_id, $plan->id, 2, $orderId, $expiresDate, $autoRenewing );

            // 记录交易
            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'user_subscription_id' => $subscription->id,
                'transaction_id' => $orderId,
                'original_transaction_id' => $orderId,
                'amount' => 0, // Google 不直接提供价格
                'currency' => 'USD',
                'platform' => 2,
                'product_id' => $productId,
                'receipt_data' => json_encode(['purchase_token' => $purchaseToken]),
                'status' => 10,
                'verified_at' => now(),
                'verification_response' => json_encode($response->toArray()),
            ]);

            // 确认购买（告诉 Google 已经处理）
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
            // TODO: 
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
            // 更新现有订阅
            $subscription->update([
                'status' => 10,
                'end_date' => $endDate,
                'auto_renew' => $autoRenew,
            ]);
        } else {
            // 创建新订阅
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

    
    private static function isJWS( string $data ) {
        return substr_count($data, '.') === 2 && str_starts_with($data, 'eyJhbGci');
    }

    /**
     * StoreKit 2 JWS 驗證流程
     */
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
        if (!$pubKey) {
            throw new Exception('Failed to extract public key from leaf cert');
        }

        if (openssl_verify($signedData, $signature, $pubKey, $algo) !== 1) {
            throw new Exception('JWS signature verification failed');
        }
        openssl_pkey_free($pubKey);

        // Step 5: Decode payload
        $payload = json_decode(self::base64url_decode($payloadB64), true);

        // Step 6: 驗證 payload
        self::validateJWSPayload($payload, $plan);

        // ── 以下和原來的流程一樣：檢查重複、建立訂閱、記錄交易 ──

        $transactionId         = $payload['transactionId'];
        $originalTransactionId = $payload['originalTransactionId'];
        $expiresDate           = Carbon::createFromTimestampMs($payload['expiresDate']);

        // 检查交易是否已存在
        if ( PaymentTransaction::exists($transactionId) ) {
            return [
                'success' => true,
                'message' => 'Transaction already processed',
                'subscription' => $user->subscriptions()->where('platform', 'ios')->active()->first(),
            ];
        }

        // 创建或更新订阅
        $isRenew = ($payload['transactionReason'] ?? '') === 'RENEWAL';
        $subscription = self::createOrUpdateSubscription(
            $user->id, $plan->id, 1, $originalTransactionId, $expiresDate, $isRenew
        );

        // 记录交易
        $transaction = PaymentTransaction::create([
            'user_id'                  => $user->id,
            'user_subscription_id'     => $subscription->id,
            'transaction_id'           => $transactionId,
            'original_transaction_id'  => $originalTransactionId,
            'amount'                   => $payload['price'] ?? 0,
            'currency'                 => $payload['currency'] ?? 'MYR',
            'platform'                 => 1,
            'product_id'               => $payload['productId'],
            'receipt_data'             => $jws,
            'status'                   => 10,
            'verified_at'              => now(),
            'verification_response'    => json_encode($payload),
        ]);

        Log::channel('payment')->info('iOS purchase verified (JWS / StoreKit 2)', [
            'user_id'       => $user->id,
            'transaction_id' => $transactionId,
            'environment'   => $payload['environment'],
        ]);

        return [
            'success'      => true,
            'message'      => 'Subscription activated successfully',
            'subscription' => $subscription->fresh(),
            'transaction'  => $transaction,
        ];
    }

    /**
     * 驗證 cert chain 是否鏈接到 Apple Root Cert
     */
    private static function verifyCertChain(array $x5c): void
    {
        if (count($x5c) < 3) {
            throw new Exception('JWS cert chain too short, expected 3 certs');
        }

        $leaf         = self::x5cToPem($x5c[0]);
        $intermediate = self::x5cToPem($x5c[1]);
        $root         = self::x5cToPem($x5c[2]);

        // 確認 leaf 由 intermediate 簽署
        if (openssl_x509_verify($leaf, $intermediate) !== 1) {
            throw new Exception('Leaf cert not signed by intermediate');
        }

        // 確認 intermediate 由 root 簽署
        if (openssl_x509_verify($intermediate, $root) !== 1) {
            throw new Exception('Intermediate cert not signed by root');
        }

        // 確認 root cert 的 Subject 包含 "Apple Inc"
        $rootInfo = openssl_x509_parse($root);
        if (!str_contains($rootInfo['subject']['O'] ?? '', 'Apple')) {
            throw new Exception('Root cert is not issued by Apple');
        }

        // (Optional) 如果你放了本地的 Apple Root Cert，可以開啟以下比較
        // $localRoot = file_get_contents(storage_path('app/certs/AppleIncRootCertificate.pem'));
        // if (openssl_x509_fingerprint($root) !== openssl_x509_fingerprint($localRoot)) {
        //     throw new Exception('Root cert fingerprint does not match local Apple Root Cert');
        // }
    }

    /**
     * 驗證 JWS payload 裡的欄位
     */
    private static function validateJWSPayload(array $payload, SubscriptionPlan $plan): void
    {
        // bundleId
        $expectedBundleId = config('iap.bundle_id', 'com.sama2oye.ios');
        if ($payload['bundleId'] !== $expectedBundleId) {
            throw new Exception("Bundle ID mismatch: expected {$expectedBundleId}, got {$payload['bundleId']}");
        }

        // productId
        if ($payload['productId'] !== $plan->ios_product_id) {
            throw new Exception("Product ID mismatch: expected {$plan->ios_product_id}, got {$payload['productId']}");
        }

        // environment (Sandbox vs Production)
        $isSandbox   = config('iap.sandbox', true);
        $expectedEnv = $isSandbox ? 'Sandbox' : 'Production';
        if ($payload['environment'] !== $expectedEnv) {
            throw new Exception("Environment mismatch: expected {$expectedEnv}, got {$payload['environment']}");
        }
    }

    // ─── Utility methods ───

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