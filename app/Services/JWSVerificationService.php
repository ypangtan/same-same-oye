<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JWSVerificationService
{
    // Apple 的 Root Certificate (Apple_inc_Root_Certificate)
    // https://www.apple.com/gpa/
    // 這個需要從 Apple 官網下載並放進來
    protected string $appleRootCertPath;

    public function __construct()
    {
        // 把 Apple Root Cert 放在 storage/app/certs/AppleIncRootCertificate.pem
        $this->appleRootCertPath = storage_path('app/certs/AppleIncRootCertificate.pem');
    }

    /**
     * Main entry: 驗證從 Flutter app 拿到的 JWS token
     * 
     * @param string $jws  完整的 JWS token (header.payload.signature)
     * @return array|false  驗證成功回傳 decoded payload，失敗回傳 false
     */
    public function verify(string $jws): array|false
    {
        // Step 1: 將 JWS 分成三段
        $parts = explode('.', $jws);
        if (count($parts) !== 3) {
            Log::error('JWS token is not in correct format (expected 3 parts)', [
                'parts_count' => count($parts)
            ]);
            return false;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        // Step 2: Decode header，拿出 x5c certificate chain
        $header = json_decode(base64url_decode($headerB64), true);
        if (!isset($header['alg']) || !isset($header['x5c'])) {
            Log::error('JWS header missing alg or x5c');
            return false;
        }

        // Step 3: 驗證 certificate chain → 鏈接到 Apple Root Cert
        $certs = $this->parseCertificateChain($header['x5c']);
        if (!$this->verifyCertificateChain($certs)) {
            Log::error('JWS certificate chain verification failed');
            return false;
        }

        // Step 4: 用 leaf cert 驗證 signature
        $leafCert = $certs[0]; // x5c[0] 是 leaf cert
        $signedData = $headerB64 . '.' . $payloadB64;
        $signature = base64url_decode($signatureB64);

        if (!$this->verifySignature($leafCert, $signedData, $signature, $header['alg'])) {
            Log::error('JWS signature verification failed');
            return false;
        }

        // Step 5: Decode payload
        $payload = json_decode(base64url_decode($payloadB64), true);

        // Step 6: 驗證 payload 裡的 bundleId、environment 等
        if (!$this->validatePayload($payload)) {
            Log::error('JWS payload validation failed', $payload);
            return false;
        }

        return $payload;
    }

    /**
     * 將 x5c 的 base64 cert 轉換成 PEM 格式
     */
    protected function parseCertificateChain(array $x5c): array
    {
        return array_map(function (string $certB64) {
            return "-----BEGIN CERTIFICATE-----\n"
                 . chunk_split($certB64, 64, "\n")
                 . "-----END CERTIFICATE-----";
        }, $x5c);
    }

    /**
     * 驗證 cert chain 是否鏈接到 Apple Root Certificate
     * 
     * x5c[0] = leaf cert (Prod ECC Mac App Store and iTunes Store Receipt Signing)
     * x5c[1] = intermediate cert (Apple Worldwide Developer Relations Certification Authority)
     * x5c[2] = root cert (Apple Inc Root Certificate)
     */
    protected function verifyCertificateChain(array $certs): bool
    {
        if (count($certs) < 3) {
            Log::error('Certificate chain too short', ['count' => count($certs)]);
            return false;
        }

        // 如果沒有 root cert 文件，先用 x5c[2] 和本地 root cert 比較
        if (file_exists($this->appleRootCertPath)) {
            $localRoot = file_get_contents($this->appleRootCertPath);
            $chainRoot = $certs[2];

            // 比較 root cert 的 public key 是否一致
            $localRootKey = openssl_pkey_get_public($localRoot);
            $chainRootKey = openssl_pkey_get_public($chainRoot);

            if (!$localRootKey || !$chainRootKey) {
                Log::error('Failed to parse root certificates');
                return false;
            }

            $localRootDetails = openssl_pkey_get_details($localRootKey);
            $chainRootDetails = openssl_pkey_get_details($chainRootKey);

            if ($localRootDetails['key'] !== $chainRootDetails['key']) {
                Log::error('Root certificate does not match Apple Root Certificate');
                return false;
            }
        }

        // 驗證 intermediate cert 由 root cert 簽署
        // 驗證 leaf cert 由 intermediate cert 簽署
        // openssl_x509_verify 需要 PHP 8.x
        // 用 openssl_x509_verify(leaf, intermediate) 會回傳 1 表示成功
        if (function_exists('openssl_x509_verify')) {
            // leaf signed by intermediate?
            if (openssl_x509_verify($certs[0], $certs[1]) !== 1) {
                Log::error('Leaf cert not signed by intermediate cert');
                return false;
            }
            // intermediate signed by root?
            if (openssl_x509_verify($certs[1], $certs[2]) !== 1) {
                Log::error('Intermediate cert not signed by root cert');
                return false;
            }
        }

        // 檢查 leaf cert 的 issuer 是否是 Apple
        $certInfo = openssl_x509_parse($certs[0]);
        if (!str_contains($certInfo['issuer']['O'] ?? '', 'Apple')) {
            Log::error('Leaf cert issuer is not Apple', ['issuer' => $certInfo['issuer']]);
            return false;
        }

        return true;
    }

    /**
     * 用 leaf cert 驗證 JWS signature
     */
    protected function verifySignature(string $leafCert, string $data, string $signature, string $alg): bool
    {
        // ES256 → OPENSSL_ALGO_SHA256
        $opensslAlg = match ($alg) {
            'ES256' => OPENSSL_ALGO_SHA256,
            'ES384' => OPENSSL_ALGO_SHA384,
            'ES512' => OPENSSL_ALGO_SHA512,
            default => null,
        };

        if ($opensslAlg === null) {
            Log::error('Unsupported algorithm', ['alg' => $alg]);
            return false;
        }

        $publicKey = openssl_pkey_get_public($leafCert);
        if (!$publicKey) {
            Log::error('Failed to extract public key from leaf cert');
            return false;
        }

        $result = openssl_verify($data, $signature, $publicKey, $opensslAlg);
        openssl_pkey_free($publicKey);

        return $result === 1;
    }

    /**
     * 驗證 payload 裡的必要欄位
     */
    protected function validatePayload(array $payload): bool
    {
        // 驗證 bundleId
        $expectedBundleId = config('iap.bundle_id', 'com.sama2oye.ios');
        if ($payload['bundleId'] !== $expectedBundleId) {
            Log::error('Bundle ID mismatch', [
                'expected' => $expectedBundleId,
                'received' => $payload['bundleId']
            ]);
            return false;
        }

        // 驗證 environment (Sandbox vs Production)
        $isSandbox = config('iap.sandbox', true);
        $expectedEnv = $isSandbox ? 'Sandbox' : 'Production';
        if ($payload['environment'] !== $expectedEnv) {
            Log::warning('Environment mismatch', [
                'expected' => $expectedEnv,
                'received' => $payload['environment']
            ]);
            // environment mismatch 可以選擇是否要 hard fail
            // Sandbox 模式收到 Production 通常是錯的
            return false;
        }

        // 檢查 expiresDate 是否已過期
        $expiresDate = $payload['expiresDate'] / 1000; // milliseconds → seconds
        if ($expiresDate < time()) {
            Log::warning('Subscription has expired', [
                'expiresDate' => date('Y-m-d H:i:s', $expiresDate)
            ]);
            // 過期不代表驗證失敗，視業務需求決定
            // 這裡先不 return false，讓調用者自己處理
        }

        return true;
    }
}

// ─── Helper: base64url decode (Laravel 不內建) ───
if (!function_exists('base64url_decode')) {
    function base64url_decode(string $data): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}