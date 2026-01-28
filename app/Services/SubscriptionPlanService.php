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
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'image' => [ 'required' ],
            'color' => [ 'required' ],
        ] );

        $attributeName = [
            'en_name' => __( 'subscription_plan.name' ),
            'zh_name' => __( 'subscription_plan.name' ),
            'image' => __( 'subscription_plan.image' ),
            'color' => __( 'subscription_plan.color' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createSubscriptionPlan = SubscriptionPlan::create( [
                'en_name' => $request->en_name,
                'zh_name' => $request->zh_name,
                'image' => $request->image,
                'color' => $request->color,
                'type_id' => $request->type_id,
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
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'image' => [ 'required' ],
            'color' => [ 'required' ],
        ] );

        $attributeName = [
            'en_name' => __( 'subscription_plan.name' ),
            'zh_name' => __( 'subscription_plan.name' ),
            'color' => __( 'subscription_plan.color' ),
            'image' => __( 'subscription_plan.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateSubscriptionPlan = SubscriptionPlan::find( $request->id );
            $updateSubscriptionPlan->en_name = $request->en_name;
            $updateSubscriptionPlan->zh_name = $request->zh_name;
            $updateSubscriptionPlan->type_id = $request->type_id;
            if( $updateSubscriptionPlan->image != $request->image ) {
                Storage::disk('public')->delete( $updateSubscriptionPlan->image );
            }
            $updateSubscriptionPlan->image = $request->image;
            $updateSubscriptionPlan->color = $request->color;
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