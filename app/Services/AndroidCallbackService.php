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

    public static function verifySubscriptionV2( $packageName, $subscriptionId, $purchaseToken, $eventType = null ) {
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
            $subscriptionData = $service
                ->purchases_subscriptionsv2
                ->get($packageName, $subscriptionId, $purchaseToken);

            if ($subscriptionData) {
                // 更新用户订阅表
                $userSubscription = UserSubscription::where('purchase_token', $purchaseToken)->first();
                
                if ($userSubscription) {
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
                }
            }

        } catch (Exception $e) {
            Log::channel('payment')->error('Google subscription verify failed: ' . $e->getMessage());
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
                if ($eventType === 'SUBSCRIPTION_REVOKED' && $user) {
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

            $eventType = $data['eventType'] ?? null;
            
            // 记录详细的事件类型
            Log::channel('payment')->info('Android webhook received', [
                'event_type' => $eventType,
                'package_name' => $data['packageName'] ?? null,
                'subscription_id' => $data['subscriptionId'] ?? null
            ]);

            switch ($eventType) {
                case 'SUBSCRIPTION_PURCHASED':
                case 'SUBSCRIPTION_RECOVERED':
                case 'SUBSCRIPTION_RENEWED':
                case 'SUBSCRIPTION_IN_GRACE_PERIOD':
                case 'SUBSCRIPTION_RESTARTED':
                case 'SUBSCRIPTION_PRICE_CHANGE_CONFIRMED':
                    // 查询最新订阅状态
                    self::verifySubscriptionV2(
                        $data['packageName'],
                        $data['subscriptionId'],
                        $data['purchaseToken'],
                        $eventType
                    );
                    break;

                case 'SUBSCRIPTION_CANCELED':
                case 'SUBSCRIPTION_REVOKED':
                case 'SUBSCRIPTION_EXPIRED':
                case 'SUBSCRIPTION_PAUSED':
                case 'SUBSCRIPTION_PAUSE_SCHEDULE_CHANGED':
                case 'SUBSCRIPTION_DEFERRED':
                    // 标记用户订阅为取消/过期
                    self::verifySubscriptionV2(
                        $data['packageName'],
                        $data['subscriptionId'],
                        $data['purchaseToken'],
                        $eventType
                    );
                    break;
                    
                case 'SUBSCRIPTION_ON_HOLD':
                    // 订阅被暂停（支付问题）
                    self::verifySubscriptionV2(
                        $data['packageName'],
                        $data['subscriptionId'],
                        $data['purchaseToken'],
                        $eventType
                    );
                    break;

                default:
                    Log::channel('payment')->warning('Unknown Android event type', [
                        'event_type' => $eventType
                    ]);
                    break;
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
}