<?php

namespace App\Services;

use App\Models\PaymentLog;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Exception;

class InAppPurchaseService {

    public static function verifyPayment( $request ) {
        try{
            $createlog = PaymentLog::create( [
                'request' => json_encode( $request->all() ),
            ] );

        }catch( \Throwable $e){
            return response()->json( [
                'message' => $e->getMessage() . ' in line: ' . $e->getLine(),
            ], 500 );
        }

        if( !empty( $request->plan_id ) ) {
            $request->merge( [
                'plan_id' => \Helper::decode( $request->plan_id ),
            ] );
        }

        $validator = Validator::make( $request->all(), [
            'platform' => [ 'required', 'in:1,2,3' ],
            'receipt_data' => [ $request->platform == 1 ? 'required' : 'nullable' ],
            'plan_id' => [ 'required', 'exists:subscription_plans,id' ],
            'purchase_token' => [ in_array( $request->platform, [2,3] ) ? 'required' : 'nullable' ],
            'purchase_data' => [ $request->platform == 3 ? 'required' : 'nullable' ],
            'signature' => [ $request->platform == 3 ? 'required' : 'nullable' ],
        ] );

        $attributeName = [
            'platform' => __( 'payment.platform' ),
            'receipt_data' => __( 'payment.receipt_data' ),
            'plan_id' => __( 'payment.plan' ),
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
                    return $result;
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

            $createlog->response = json_encode( $result );
            $createlog->status = 10;
            $createlog->save();

            return response()->json( $result, 200 );

        } catch ( Exception $e ) {
            
            $createlog->response = json_encode( [
                'message' => $e->getMessage(),
            ] );
            $createlog->status = 20;
            $createlog->save();

            return response()->json([
                'message' => $e->getMessage(),
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
                'is_active' => $subscription->is_active(),
            ],
        ] );
    }

    public static function getPlans( $request ) {

        $per_page = $request->per_page ?? 10;
        $plans = SubscriptionPlan::where( 'status', 10 )->paginate( $per_page );

        if ( $plans ) {
            $plans->append( [
                'encrypted_id',
            ] );
        }
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