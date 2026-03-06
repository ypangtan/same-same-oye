<?php

namespace App\Services;

use App\Models\{
    CallbackLog,
    SubscriptionPlan,
    UserSubscription,
    PaymentTransaction,
    User,
};
use Imdhemy\Purchases\Facades\{
    Subscription,
};
use Carbon\Carbon;
use Exception;

use Illuminate\Support\Facades\{
    Http,
    DB,
    Log,
    Cache,
};

use Firebase\JWT\{
    JWT,
    JWK,
    Key,
};

class IosCallbackService {

    public static function callbackIos( $request ) {
        try{ 
            $createLog = CallbackLog::create([
                'platform' => 'ios',
                'payload' => json_encode($request->all()),
            ]);
        } catch ( \Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500); 
        }

        try {
            DB::beginTransaction();

            $signedPayload = $request->input('signedPayload');

            if (!$signedPayload) {
                return response()->json(['error' => 'Missing signedPayload'], 400);
            }

            $payload = self::decodeAndVerify($signedPayload);

            // 获取通知类型和子类型
            $notificationType = data_get($payload, 'notificationType');
            $subtype = data_get($payload, 'subtype');
            
            // 获取 transaction info
            $signedTransactionInfo = data_get($payload, 'data.signedTransactionInfo');
            $transactionPayload = null;
            
            if ($signedTransactionInfo) {
                $transactionPayload = self::decodeAndVerify($signedTransactionInfo);
            }
            
            $originalTransactionId = data_get($transactionPayload, 'originalTransactionId');

            Log::channel('payment')->info('iOS webhook received', [
                'notification_type' => $notificationType,
                'subtype' => $subtype,
                'original_transaction_id' => $originalTransactionId,
            ]);

            if ($originalTransactionId) {
                self::syncSubscription($originalTransactionId, $notificationType, $subtype, $transactionPayload);
            }

            // ✅ Fix 1: 补上缺失的 commit
            DB::commit();
            return response()->json(['status' => 'success'], 200);

        } catch (Exception $e) {
            DB::rollBack();

            Log::channel('payment')->error('iOS callback failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json( [ 
                'message' => $e->getMessage()
            ], 500 );
        }
    }

    public static function decodeAndVerify(string $signedPayload): \stdClass {
        try {
            $parts = explode('.', $signedPayload);
            $header = json_decode(JWT::urlsafeB64Decode($parts[0]), true);
            
            Log::info('JWT Header Full', ['header' => $header]);

            if (isset($header['x5c']) && is_array($header['x5c']) && count($header['x5c']) >= 2) {
                $leafCert = "-----BEGIN CERTIFICATE-----\n" . 
                            chunk_split($header['x5c'][0], 64, "\n") . 
                            "-----END CERTIFICATE-----";

                $intermCert = "-----BEGIN CERTIFICATE-----\n" . 
                              chunk_split($header['x5c'][1], 64, "\n") . 
                              "-----END CERTIFICATE-----";

                // ✅ Fix 5: 验证 leaf cert 由 intermediate cert 签发
                $leafX509  = openssl_x509_read($leafCert);
                $intermX509 = openssl_x509_read($intermCert);

                if ($leafX509 === false || $intermX509 === false) {
                    throw new \Exception('Failed to parse certificates from x5c');
                }

                $intermPublicKey = openssl_pkey_get_public($intermX509);
                $verified = openssl_x509_verify($leafX509, $intermPublicKey);

                if ($verified !== 1) {
                    throw new \Exception('Certificate chain verification failed: leaf cert not signed by intermediate');
                }

                $publicKey = openssl_pkey_get_public($leafCert);

                if ($publicKey === false) {
                    throw new \Exception('Failed to extract public key from certificate');
                }

                return JWT::decode(
                    $signedPayload, 
                    new Key($publicKey, $header['alg'] ?? 'ES256')
                );
            }

            throw new \Exception('No valid x5c chain found in JWT header');
            
        } catch (\Exception $e) {
            Log::error('JWT decode error', [
                'error' => $e->getMessage(),
                'payload_start' => substr($signedPayload, 0, 100)
            ]);
            throw $e;
        }
    }

    /**
     * 同步订阅状态（唯一真相）
     * 根据通知类型处理订阅并发送相应通知
     */
    public static function syncSubscription( $originalTransactionId, $notificationType = null, $subtype = null, ?\stdClass $transactionPayload = null ) {
        $payment = PaymentTransaction::where('original_transaction_id', $originalTransactionId)->first();

        // ✅ Fix 2: 补上缺失的 warning log
        if (!$payment) {
            Log::channel('payment')->warning('iOS Payment Transaction not found', [
                'original_transaction_id' => $originalTransactionId,
                'notification_type' => $notificationType,
                'subtype' => $subtype,
            ]);
            return;
        }

        $userSubscription = UserSubscription::find($payment->user_subscription_id);

        if (!$userSubscription) {
            Log::channel('payment')->warning('iOS UserSubscription not found', [
                'payment_id' => $payment->id,
                'user_subscription_id' => $payment->user_subscription_id,
                'notification_type' => $notificationType,
            ]);
            return;
        }

        $user = $userSubscription->user;

        // 根据通知类型处理并发送通知
        switch ($notificationType) {
            case 'SUBSCRIBED':
                self::handleNewSubscription( $userSubscription, $transactionPayload, $user );
                break;
                
            case 'DID_RENEW':
                self::handleRenewal( $userSubscription, $transactionPayload, $subtype, $user );
                break;
                
            case 'DID_CHANGE_RENEWAL_STATUS':
                break;
                
            case 'DID_FAIL_TO_RENEW':
                self::handleRenewalFailure( $userSubscription, $subtype, $user );
                break;
                
            case 'EXPIRED':
                self::handleExpiration( $userSubscription, $subtype, $user );
                break;
                
            case 'REFUND':
                self::handleRefund( $userSubscription, $transactionPayload, $user );
                break;
                
            default:
                Log::channel('payment')->info('iOS unhandled notification type, falling back to verifyLatestStatus', [
                    'notification_type' => $notificationType,
                    'subtype' => $subtype,
                ]);
                self::verifyLatestStatus( $userSubscription );
                break;
        }

        $userSubscription->save();
    }

    /**
     * 处理新订阅 - 发送成功通知
     */
    private static function handleNewSubscription( UserSubscription $userSubscription, ?\stdClass $transactionPayload, $user ) {
        if ($transactionPayload && isset($transactionPayload->expiresDate)) {
            $expiresAt = Carbon::createFromTimestampMs($transactionPayload->expiresDate)
                ->timezone('Asia/Kuala_Lumpur');
            
            $userSubscription->status = 10; // active
            $userSubscription->end_date = $expiresAt;
            
            if ($user) {
                UserService::createUserNotification(
                    $user->id,
                    'notification.subscribed_success_title',
                    'notification.subscribed_success_content',
                    'subscription_success',
                    'subscription'
                );
            }
            
            Log::channel('payment')->info('New subscription created', [
                'user_id' => $user->id ?? null,
                'user_subscription_id' => $userSubscription->id,
                'expires_at' => $expiresAt
            ]);
        }
    }

    /**
     * 处理订阅续订
     */
    private static function handleRenewal( UserSubscription $userSubscription, ?\stdClass $transactionPayload, ?string $subtype, $user ) {
        if ($transactionPayload && isset($transactionPayload->expiresDate)) {
            $expiresAt = Carbon::createFromTimestampMs($transactionPayload->expiresDate)
                ->timezone('Asia/Kuala_Lumpur');
            
            $userSubscription->status = 10; // active
            $userSubscription->end_date = $expiresAt;

            // ✅ Fix 3: 检查 productId 是否有变更（降级生效时更新 plan）
            $currentProductId = $transactionPayload->productId ?? null;
            if ($currentProductId) {
                $newPlan = SubscriptionPlan::where('ios_product_id', $currentProductId)->first();
                if ($newPlan && $userSubscription->subscription_plan_id !== $newPlan->id) {
                    Log::channel('payment')->info('iOS subscription plan changed on renewal', [
                        'user_id' => $user->id ?? null,
                        'old_plan_id' => $userSubscription->subscription_plan_id,
                        'new_plan_id' => $newPlan->id,
                        'product_id' => $currentProductId,
                    ]);
                    $userSubscription->subscription_plan_id = $newPlan->id;

                    // 把旧的 pending deferred subscription 清掉
                    UserSubscription::where('user_id', $userSubscription->user_id)
                        ->where('subscription_plan_id', $newPlan->id)
                        ->where('status', 1)
                        ->delete();
                }
            }
            
            if ($user) {
                UserService::createUserNotification(
                    $user->id,
                    'notification.subscribed_success_title',
                    'notification.subscribed_success_content',
                    'subscription_success',
                    'subscription'
                );
            }
            
            Log::channel('payment')->info('Subscription renewed', [
                'user_id' => $user->id ?? null,
                'original_transaction_id' => $transactionPayload->originalTransactionId ?? null,
                'expires_at' => $expiresAt,
                'subtype' => $subtype
            ]);
        }
    }

    /**
     * 处理续订失败 - 发送支付失败通知
     */
    private static function handleRenewalFailure( UserSubscription $userSubscription, ?string $subtype, $user ) {
        $userSubscription->status = 30; // grace_period
        
        if ($user) {
            UserService::createUserNotification(
                $user->id,
                'notification.subscription_payment_failed_title',
                'notification.subscription_payment_failed_content',
                'payment_failed',
                'subscription'
            );
        }
        
        Log::channel('payment')->warning('Subscription renewal failed', [
            'user_id' => $user->id ?? null,
            'user_subscription_id' => $userSubscription->id,
            'subtype' => $subtype
        ]);
    }

    /**
     * 处理订阅过期 - 发送取消通知
     */
    private static function handleExpiration( UserSubscription $userSubscription, ?string $subtype, $user ) {
        $userSubscription->status = 20; // expired
        
        $reason = match($subtype) {
            'VOLUNTARY'         => 'User cancelled',
            'BILLING_RETRY'     => 'Billing issue',
            'PRICE_INCREASE'    => 'Did not agree to price increase',
            'PRODUCT_NOT_FOR_SALE' => 'Product no longer available',
            default             => 'Unknown reason',
        };
        
        if ($user) {
            if ($subtype === 'VOLUNTARY') {
                UserService::createUserNotification(
                    $user->id,
                    'notification.subscription_cancelled_title',
                    'notification.subscription_cancelled_content',
                    'subscription_cancelled',
                    'subscription'
                );
            } elseif ($subtype === 'BILLING_RETRY') {
                UserService::createUserNotification(
                    $user->id,
                    'notification.subscription_payment_cancelled_title',
                    'notification.subscription_payment_cancelled_content',
                    'payment_cancelled',
                    'subscription'
                );
            }
        }
        
        Log::channel('payment')->info('Subscription expired', [
            'user_id' => $user->id ?? null,
            'user_subscription_id' => $userSubscription->id,
            'reason' => $reason,
            'subtype' => $subtype
        ]);
    }

    /**
     * 处理退款
     */
    private static function handleRefund( UserSubscription $userSubscription, ?\stdClass $transactionPayload, $user ) {
        $userSubscription->status = 40; // refunded
        
        if ($user) {
            UserService::createUserNotification(
                $user->id,
                'notification.subscription_cancelled_title',
                'notification.subscription_cancelled_content',
                'subscription_refunded',
                'subscription'
            );
        }
        
        Log::channel('payment')->warning('Subscription refunded', [
            'user_id' => $user->id ?? null,
            'user_subscription_id' => $userSubscription->id,
            'transaction_id' => $transactionPayload->transactionId ?? null
        ]);
    }

    /**
     * 验证最新状态（fallback）
     */
    private static function verifyLatestStatus( UserSubscription $userSubscription ) {
        try {
            // ✅ Fix 4: receipt_data 从 PaymentTransaction 取，UserSubscription 没有这个字段
            $payment = PaymentTransaction::where('user_subscription_id', $userSubscription->id)
                ->latest()
                ->first();

            if (!$payment || !$payment->receipt_data) {
                Log::channel('payment')->warning('verifyLatestStatus: no payment/receipt found', [
                    'user_subscription_id' => $userSubscription->id,
                ]);
                return;
            }

            $isSandbox = config('liap.appstore_sandbox', true);
            $client = \Imdhemy\AppStore\ClientFactory::create($isSandbox);

            $response = Subscription::appStore($client)
                ->receiptData($payment->receipt_data)
                ->verifyRenewable();

            $latestReceipts = $response->getLatestReceiptInfo();

            if (empty($latestReceipts)) {
                return;
            }

            $latest = collect($latestReceipts)
                ->sortByDesc(fn($r) => $r->toArray()['expires_date_ms'] ?? 0)
                ->first();

            $rawData = $latest->toArray();
            $expiresAt = Carbon::createFromTimestampMs($rawData['expires_date_ms'])
                ->timezone('Asia/Kuala_Lumpur');

            $userSubscription->status = $expiresAt->isFuture() ? 10 : 20;
            $userSubscription->end_date = $expiresAt;
            
            Log::channel('payment')->info('iOS subscription status verified via fallback', [
                'user_subscription_id' => $userSubscription->id,
                'status' => $userSubscription->status,
                'expires_at' => $expiresAt
            ]);
            
        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to verify iOS subscription status', [
                'error' => $e->getMessage(),
                'user_subscription_id' => $userSubscription->id
            ]);
        }
    }
}