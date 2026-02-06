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
        try{
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

            if ($originalTransactionId) {
                self::syncSubscription($originalTransactionId, $notificationType, $subtype, $transactionPayload);
            }

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
            // Apple 的 JWT 可能包含 x5c (certificate chain)
            $parts = explode('.', $signedPayload);
            $header = json_decode(JWT::urlsafeB64Decode($parts[0]), true);
            
            Log::info('JWT Header Full', ['header' => $header]);

            // 检查是否有 x5c (certificate chain)
            if (isset($header['x5c']) && is_array($header['x5c'])) {
                // 使用证书链中的第一个证书
                $cert = "-----BEGIN CERTIFICATE-----\n" . 
                        chunk_split($header['x5c'][0], 64, "\n") . 
                        "-----END CERTIFICATE-----";
                
                Log::info('Using certificate from x5c');
                
                // 从证书提取公钥
                $publicKey = openssl_pkey_get_public($cert);
                
                if ($publicKey === false) {
                    throw new \Exception('Failed to extract public key from certificate');
                }

                // 使用公钥解码
                return JWT::decode(
                    $signedPayload, 
                    new Key($publicKey, $header['alg'] ?? 'ES256')
                );
            }

            // 如果没有 x5c，回退到使用 JWKS
            throw new \Exception('No x5c in header and no kid found');
            
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

        if (!$payment) {
            return;
        }

        $userSubscription = UserSubscription::find($payment->user_subscription_id);

        if (!$userSubscription) {
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
            $userSubscription->auto_renew = true;
            
            // 📱 发送订阅成功通知
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
                'user_id' => $user->id,
                'user_subscription_id' => $userSubscription->id,
                'expires_at' => $expiresAt
            ]);
        }
    }

    /**
     * 处理订阅续订
     */
    private static function handleRenewal( UserSubscription $userSubscription,  ?\stdClass $transactionPayload, ?string $subtype, $user ) {
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
            
            Log::channel('payment')->info('Subscription renewed', [
                'original_transaction_id' => $transactionPayload->originalTransactionId,
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
        
        // 📱 发送支付失败通知
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
            'VOLUNTARY' => 'User cancelled',
            'BILLING_RETRY' => 'Billing issue',
            'PRICE_INCREASE' => 'Did not agree to price increase',
            'PRODUCT_NOT_FOR_SALE' => 'Product no longer available',
            default => 'Unknown reason'
        };
        
        // 📱 根据原因发送不同通知
        if ($user) {
            if ($subtype === 'VOLUNTARY') {
                // 用户主动取消
                UserService::createUserNotification(
                    $user->id,
                    'notification.subscription_cancelled_title',
                    'notification.subscription_cancelled_content',
                    'subscription_cancelled',
                    'subscription'
                );
            } elseif ($subtype === 'BILLING_RETRY') {
                // 支付被取消
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
        
        // 📱 发送退款通知（可选）
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
     * 验证最新状态
     */
    private static function verifyLatestStatus( UserSubscription $userSubscription ) {
        try {
            $receiptData = Subscription::verify($userSubscription->receipt_data);

            $latest = collect($receiptData->getLatestReceiptInfo())
                ->sortByDesc('expires_date_ms')
                ->first();

            if (!$latest) {
                return;
            }

            $expiresAt = Carbon::createFromTimestampMs($latest['expires_date_ms'])
                ->timezone('Asia/Kuala_Lumpur');

            // 状态判断
            if ($expiresAt->isFuture()) {
                $userSubscription->status = 10; // active
            } else {
                $userSubscription->status = 20; // expired
            }

            $userSubscription->end_date = $expiresAt;
            $userSubscription->save();
            
            Log::channel('payment')->info('Subscription status verified', [
                'user_subscription_id' => $userSubscription->id,
                'status' => $userSubscription->status,
                'expires_at' => $expiresAt
            ]);
            
        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to verify subscription status', [
                'error' => $e->getMessage(),
                'user_subscription_id' => $userSubscription->id
            ]);
        }
    }

}