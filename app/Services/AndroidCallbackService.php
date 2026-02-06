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
    Product,
};
use Carbon\Carbon;
use Exception;
use Imdhemy\AppStore\ClientFactory as AppStoreClientFactory;
use Google\Client as GoogleClient;
use Google\Service\AndroidPublisher;

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

class AndroidCallbackService {

    public static function verifySubscriptionV2($packageName, $subscriptionId, $purchaseToken, $eventType = null) {
        $credentialsPath = config('liap.google_application_credentials');
            
        // 检查文件是否存在
        if (!file_exists($credentialsPath)) {
            throw new \Exception('Google Play 凭据文件不存在: ' . $credentialsPath);
        }
        
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([
            AndroidPublisher::ANDROIDPUBLISHER,
        ]);

        $service = new AndroidPublisher($client);

        try {
            // ✅ Correct: Use only purchaseToken (which contains the subscription info)
            $subscriptionData = $service
                ->purchases_subscriptionsv2
                ->get($packageName, $purchaseToken); // Only 2 params!

            if ($subscriptionData) {
                // 更新用户订阅表

                $payment = PaymentTransaction::where( 'receipt_data', $purchaseToken )->first();

                if( !$payment ) {
                    Log::channel('payment')->warning('Payment Transaction not found', [
                        'purchase_token' => $purchaseToken,
                        'subscription_id' => $subscriptionId
                    ]);
                    return ;
                }

                $userSubscription = UserSubscription::find( $payment->user_subscription_id );
                
                if (!$userSubscription) {
                    Log::channel('payment')->warning('User subscription not found', [
                        'purchase_token' => $purchaseToken,
                        'subscription_id' => $subscriptionId
                    ]);
                    return null;
                }
                
                $user = $userSubscription->user;
                $subscriptionState = $subscriptionData->getSubscriptionState();
                
                // 处理订阅状态并发送通知
                self::handleAndroidSubscriptionState(
                    $userSubscription, 
                    $subscriptionState, 
                    $eventType,
                    $user
                );
                
                // 更新到期时间
                $lineItems = $subscriptionData->getLineItems();
                if (!empty($lineItems)) {
                    $expiryTime = $lineItems[0]->getExpiryTime();
                    if ($expiryTime) {
                        $userSubscription->end_date = Carbon::parse($expiryTime)
                            ->timezone('Asia/Kuala_Lumpur');
                    }
                }
                
                $userSubscription->save();
                
                Log::channel('payment')->info('Android subscription verified', [
                    'user_id' => $user->id ?? null,
                    'subscription_state' => $subscriptionState,
                    'event_type' => $eventType,
                    'purchase_token' => $purchaseToken
                ]);
                
                return $subscriptionData;
            }

        } catch (Exception $e) {
            Log::channel('payment')->error('Google subscription verify failed: ' . $e->getMessage(), [
                'purchase_token' => $purchaseToken,
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    /**
     * 处理 Android 订阅状态并发送通知
     */
    private static function handleAndroidSubscriptionState( UserSubscription $userSubscription, string $subscriptionState, ?string $eventType, $user ) {
        switch ($subscriptionState) {
            case 'SUBSCRIPTION_STATE_ACTIVE':
                $userSubscription->status = 10; // 激活
                
                // 如果是新订阅或恢复订阅，发送成功通知
                if ($eventType === 'SUBSCRIPTION_RECOVERED') {
                    if ($user) {
                        UserService::createUserNotification(
                            $user->id,
                            'notification.subscribed_success_title',
                            'notification.subscribed_success_content',
                            'subscription_recovered',
                            'subscription'
                        );
                    }
                    Log::channel('payment')->info('Subscription recovered', [
                        'user_id' => $user->id ?? null,
                        'user_subscription_id' => $userSubscription->id
                    ]);
                } elseif ($eventType === 'SUBSCRIPTION_RENEWED') {
                    // 续订成功（可以选择不发通知，避免打扰）
                    if ($user) {
                        UserService::createUserNotification(
                            $user->id,
                            'notification.subscribed_success_title',
                            'notification.subscribed_success_content',
                            'subscription_renewed',
                            'subscription'
                        );
                    }

                    Log::channel('payment')->info('Subscription renewed', [
                        'user_id' => $user->id ?? null,
                        'user_subscription_id' => $userSubscription->id
                    ]);
                }
                break;
                
            case 'SUBSCRIPTION_STATE_CANCELED':
                $userSubscription->status = 40; // 取消
                
                // 📱 发送订阅取消通知
                if ($user) {
                    UserService::createUserNotification(
                        $user->id,
                        'notification.subscription_cancelled_title',
                        'notification.subscription_cancelled_content',
                        'subscription_cancelled',
                        'subscription'
                    );
                }
                
                Log::channel('payment')->info('Subscription cancelled', [
                    'user_id' => $user->id ?? null,
                    'user_subscription_id' => $userSubscription->id
                ]);
                break;
                
            case 'SUBSCRIPTION_STATE_EXPIRED':
                $userSubscription->status = 20; // 过期
                
                // 📱 发送订阅过期通知（如果是被撤销）
                if ( $user ) {
                    UserService::createUserNotification(
                        $user->id,
                        'notification.subscription_cancelled_title',
                        'notification.subscription_cancelled_content',
                        'subscription_revoked',
                        'subscription'
                    );
                }
                
                Log::channel('payment')->info('Subscription expired', [
                    'user_id' => $user->id ?? null,
                    'user_subscription_id' => $userSubscription->id,
                    'event_type' => $eventType
                ]);
                break;
                
            case 'SUBSCRIPTION_STATE_IN_GRACE_PERIOD':
                $userSubscription->status = 30; // 宽限期
                
                // 📱 发送支付失败通知
                if ($user) {
                    UserService::createUserNotification(
                        $user->id,
                        'notification.subscription_payment_failed_title',
                        'notification.subscription_payment_failed_content',
                        'payment_failed_grace_period',
                        'subscription'
                    );
                }
                
                Log::channel('payment')->warning('Subscription in grace period', [
                    'user_id' => $user->id ?? null,
                    'user_subscription_id' => $userSubscription->id
                ]);
                break;
                
            case 'SUBSCRIPTION_STATE_PAUSED':
                break;
        }
    }

    public static function callbackAndroid($request) {
        try{ 
            $createLog = CallbackLog::create([
                'platform' => 'android',
                'payload' => json_encode($request->all()),
            ]);
        } catch ( \Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500); 
        }

        try {
            DB::beginTransaction();

            $message = $request->input('message');
            $data = json_decode(base64_decode($message['data']), true);

            // Extract subscription notification
            $subscriptionNotification = $data['subscriptionNotification'] ?? null;
            
            if (!$subscriptionNotification) {
                Log::channel('payment')->warning('No subscription notification in webhook');
                DB::commit();
                return response()->json(['status' => 'success'], 200);
            }

            $notificationType = $subscriptionNotification['notificationType'] ?? null;
            
            // Map notificationType to eventType
            $eventType = self::mapNotificationTypeToEvent($notificationType);
            
            // Record detailed event type
            Log::channel('payment')->info('Android webhook received', [
                'notification_type' => $notificationType,
                'event_type' => $eventType,
                'package_name' => $data['packageName'] ?? null,
                'subscription_id' => $subscriptionNotification['subscriptionId'] ?? null
            ]);

            // Process based on event type
            if ($eventType) {
                self::verifySubscriptionV2(
                    $data['packageName'],
                    $subscriptionNotification['subscriptionId'],
                    $subscriptionNotification['purchaseToken'],
                    $eventType
                );
            } else {
                Log::channel('payment')->warning('Unknown Android notification type', [
                    'notification_type' => $notificationType
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success'], 200);

        } catch (Exception $e) {
            DB::rollBack();

            Log::channel('payment')->error('Android callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private static function mapNotificationTypeToEvent(?int $notificationType): ?string {
        $mapping = [
            1 => 'SUBSCRIPTION_RECOVERED',
            2 => 'SUBSCRIPTION_RENEWED',
            3 => 'SUBSCRIPTION_CANCELED',
            4 => 'SUBSCRIPTION_PURCHASED',
            5 => 'SUBSCRIPTION_ON_HOLD',
            6 => 'SUBSCRIPTION_IN_GRACE_PERIOD',
            7 => 'SUBSCRIPTION_RESTARTED',
            8 => 'SUBSCRIPTION_PRICE_CHANGE_CONFIRMED',
            9 => 'SUBSCRIPTION_DEFERRED',
            10 => 'SUBSCRIPTION_PAUSED',
            11 => 'SUBSCRIPTION_PAUSE_SCHEDULE_CHANGED',
            12 => 'SUBSCRIPTION_REVOKED',
            13 => 'SUBSCRIPTION_EXPIRED',
        ];

        return $mapping[$notificationType] ?? null;
    }
}