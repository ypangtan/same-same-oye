<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class InAppPurchaseService {

    public static function verifyPayment( Request $request ) {

        $validator = Validator::make( $request->all(), [
            'platform' => [ 'required', 'in:1,2,3' ],
            'receipt_data' => [ $request->platform == 1 ? 'required' : 'nullable' ],
            'product_id' => [ 'required' ],
            'purchase_token' => [ in_array( $request->platform, [2,3] ) ? 'required' : 'nullable' ],
            'purchase_data' => [ $request->platform == 3 ? 'required' : 'nullable' ],
            'signature' => [ $request->platform == 3 ? 'required' : 'nullable' ],
        ] );

        $attributeName = [
            'platform' => __( 'payment.platform' ),
            'receipt_data' => __( 'payment.receipt_data' ),
            'product_id' => __( 'payment.product_id' ),
            'purchase_token' => __( 'payment.purchase_token' ),
            'purchase_data' => __( 'payment.purchase_data' ),
            'signature' => __( 'payment.signature' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        try {
            switch ( $request->platform ) {
                case 1:
                    $result = PaymentService::verifyIOSPurchase( auth()->user()->id, $request->all() );
                    break;
                case 2:
                    $result = PaymentService::verifyAndroidPurchase( auth()->user()->id, $request->all() );
                    break;
                case 3:
                    $result = PaymentService::verifyHuaweiPurchase( auth()->user()->id, $request->all() );
                    break;
                default:
                    return response()->json( [
                        'message' => 'Invalid platform',
                    ], 500 );

            }

            return response()->json( $result, 200 );

        } catch ( Exception $e ) {
            return response()->json([
                'message' => 'Verification failed',
                'error' => $e->getMessage(),
            ], 500 );
        }
    }

    public static function getCurrentSubscription() {
        $user = User::find( auth()->user()->id );
        
        $subscription = $user->subscriptions()
            ->with('plan')
            ->active()
            ->first();

        if (!$subscription) {
            return response()->json( [
                'has_subscription' => false,
                'message' => 'No active subscription',
            ] );
        }

        return response()->json( [
            'has_subscription' => true,
            'subscription' => [
                'id' => $subscription->id,
                'plan_name' => $subscription->plan->name,
                'status' => $subscription->status,
                'platform' => $subscription->platform,
                'start_date' => $subscription->start_date,
                'end_date' => $subscription->end_date,
                'auto_renew' => $subscription->auto_renew,
                'is_active' => $subscription->isActive(),
            ],
        ] );
    }

    public static function getPlans() {
        $plans = SubscriptionPlan::where( 'status', 10 )->get();

        return response()->json( [
            'plans' => $plans,
        ] );
    }

    public static function cancelSubscription() {
        $user = User::find( auth()->user()->id );
        
        $subscription = $user->subscriptions()->active()->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'No active subscription found',
            ], 500);
        }

        $subscription->cancel();

        return response()->json([
            'message' => 'Subscription cancelled successfully',
            'subscription' => $subscription->fresh(),
        ]);
    }
}