<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Hash,
    Storage,
    Validator,
};

use Illuminate\Validation\Rules\Password;

use App\Models\{
    SubscriptionPlan,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class SubscriptionPlanService
{
    public static function allSubscriptionPlans( $request ) {

        $subscriptionPlan = SubscriptionPlan::select( 'subscription_plans.*' );

        $filterObject = self::filter( $request, $subscriptionPlan );
        $subscriptionPlan = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            switch ( $request->input( 'order.0.column' ) ) {
                default:
                    $subscriptionPlan->orderBy( 'created_at', $dir );
                    break;
            }
        }

        $subscriptionPlanCount = $subscriptionPlan->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $subscriptionPlans = $subscriptionPlan->skip( $offset )->take( $limit )->get();

        if ( $subscriptionPlans ) {
            $subscriptionPlans->append( [
                'encrypted_id',
            ] );
        }

        if( !empty( $request->type ) ) {
            $totalRecord = SubscriptionPlan::where( 'type_id', $request->type )->count();
        } else {
            $totalRecord = SubscriptionPlan::count();
        }

        $data = [
            'subscription_plans' => $subscriptionPlans,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $subscriptionPlanCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_at ) ) {
            if ( str_contains( $request->created_at, 'to' ) ) {
                $dates = explode( ' to ', $request->created_at );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'subscription_plans.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_at );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'subscription_plans.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( function( $q ) use ( $request ) {
                $q->where( 'multi_lang_name', 'LIKE', '%' . $request->name . '%' );
            } );
            $filter = true;
        }

        if( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if( !empty( $request->type ) ) {
            $model->where( 'type_id', $request->type );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneSubscriptionPlan( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $subscriptionPlan = SubscriptionPlan::find( $request->id );

        if( $subscriptionPlan ) {
            $subscriptionPlan->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $subscriptionPlan );
    }

    public static function createSubscriptionPlan( $request ) {

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'description' => [ 'nullable' ],
            'price' => [ 'required' ],
            'duration_in_days' => [ 'required' ],
            'ios_product_id' => [ 'required' ],
            'android_product_id' => [ 'required' ],
            'huawei_product_id' => [ 'nullable' ],
        ] );

        $attributeName = [
            'name' => __( 'subscription_plan.name' ),
            'description' => __( 'subscription_plan.description' ),
            'price' => __( 'subscription_plan.price' ),
            'duration_in_days' => __( 'subscription_plan.duration_in_days' ),
            'ios_product_id' => __( 'subscription_plan.ios_product_id' ),
            'android_product_id' => __( 'subscription_plan.android_product_id' ),
            'huawei_product_id' => __( 'subscription_plan.huawei_product_id' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createSubscriptionPlan = SubscriptionPlan::create( [
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'duration_in_days' => $request->duration_in_days,
                'ios_product_id' => $request->ios_product_id,
                'android_product_id' => $request->android_product_id,
                'huawei_product_id' => $request->huawei_product_id,
            ] );
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.subscription_plans' ) ) ] ),
        ] );
    }

    public static function updateSubscriptionPlan( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        
        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'description' => [ 'nullable' ],
            'price' => [ 'required' ],
            'duration_in_days' => [ 'required' ],
            'ios_product_id' => [ 'required' ],
            'android_product_id' => [ 'required' ],
            'huawei_product_id' => [ 'nullable' ],
        ] );

        $attributeName = [
            'name' => __( 'subscription_plan.name' ),
            'description' => __( 'subscription_plan.description' ),
            'price' => __( 'subscription_plan.price' ),
            'duration_in_days' => __( 'subscription_plan.duration_in_days' ),
            'ios_product_id' => __( 'subscription_plan.ios_product_id' ),
            'android_product_id' => __( 'subscription_plan.android_product_id' ),
            'huawei_product_id' => __( 'subscription_plan.huawei_product_id' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateSubscriptionPlan = SubscriptionPlan::find( $request->id );
            $updateSubscriptionPlan->name = $request->name;
            $updateSubscriptionPlan->description = $request->description;
            $updateSubscriptionPlan->price = $request->price;
            $updateSubscriptionPlan->duration_in_days = $request->duration_in_days;
            $updateSubscriptionPlan->ios_product_id = $request->ios_product_id;
            $updateSubscriptionPlan->android_product_id = $request->android_product_id;
            $updateSubscriptionPlan->huawei_product_id = $request->huawei_product_id;
            $updateSubscriptionPlan->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.subscription_plans' ) ) ] ),
        ] );
    }

    public static function updateSubscriptionPlanStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateSubscriptionPlan = SubscriptionPlan::find( $request->id );
        $updateSubscriptionPlan->status = $request->status;
        $updateSubscriptionPlan->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.subscription_plans' ) ) ] ),
        ] );
    }
}