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
    UserSubscription,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

class UserSubscriptionService
{
    public static function allUserSubscriptions( $request ) {

        $userSubscription = UserSubscription::with( [
            'user',
            'plan',
        ] )->select( 'user_subscriptions.*' );

        $filterObject = self::filter( $request, $userSubscription );
        $userSubscription = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            switch ( $request->input( 'order.0.column' ) ) {
                default:
                    $userSubscription->orderBy( 'created_at', $dir );
                    break;
            }
        }

        $userSubscriptionCount = $userSubscription->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $userSubscriptions = $userSubscription->skip( $offset )->take( $limit )->get();

        foreach ( $userSubscriptions as $userSubscription ) {
            $userSubscription->append( [
                'encrypted_id',
            ] );

            if( $userSubscription->user ) {
                $userSubscription->user->append( [
                    'encrypted_id',
                ] );
            }

            if( $userSubscription->plan ) {
                $userSubscription->plan->append( [
                    'encrypted_id',
                ] );
            }
        }

        $totalRecord = UserSubscription::count();

        $data = [
            'user_subscriptions' => $userSubscriptions,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $userSubscriptionCount : $totalRecord,
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

                $model->whereBetween( 'user_subscriptions.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_at );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'user_subscriptions.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if( !empty( $request->user ) ) {
            $user = \Helper::decode( $request->user );
            $model->where( 'user_subscriptions.user_id', $user );
            $filter = true;
        }

        if( !empty( $request->plan ) ) {
            $plan = \Helper::decode( $request->plan );
            $model->where( 'user_subscriptions.subscription_plan_id', $plan );
            $filter = true;
        }

        if( !empty( $request->status ) ) {
            $model->where( 'user_subscriptions.status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneUserSubscription( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $userSubscription = UserSubscription::with( [
            'user',
            'plan',
        ] )->find( $request->id );

        if( $userSubscription ) {
            $userSubscription->append( [
                'encrypted_id',
            ] );
            if( $userSubscription->user ) {
                $userSubscription->user->append( [
                    'encrypted_id',
                ] );
            }

            if( $userSubscription->plan ) {
                $userSubscription->plan->append( [
                    'encrypted_id',
                ] );
            }
        }

        return response()->json( $userSubscription );
    }

    public static function createUserSubscription( $request ) {

        if( !empty( $request->user_id ) ) {
            $request->merge( [
                'user_id' => \Helper::decode( $request->user_id )
            ] );
        }

        $validator = Validator::make( $request->all(), [
            'user_id' => [ 'required','exists:users,id' ],
            'end_date' => [ 'required' ],
        ] );


        $attributeName = [
            'user_id' => __( 'subscription_group_member.user' ),
            'end_date' => __( 'subscription_group_member.end_date' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $userSubscriptionCreate = UserSubscription::create([
                'user_id' => $request->user_id,
                'subscription_plan_id' => null,
                'status' => 10,
                'start_date' => Carbon::now()->timezone( 'Asia/Kuala_Lumpur' ),
                'end_date' => $request->end_date,
                'type' => 2,
            ]);

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.user_subscriptions' ) ) ] ),
            'status' => 200
        ] );
    }

    public static function updateUserSubscription( $request ) {

        if( !empty( $request->user_id ) ) {
            $request->merge( [
                'user_id' => \Helper::decode( $request->user_id )
            ] );
        }

        $validator = Validator::make( $request->all(), [
            'id' => [ 'required', 'exists:user_subscriptions,id', function ( $attribute, $value, $fail ) {
                $userSubscription = UserSubscription::find( $value );
                if( $userSubscription && $userSubscription->type != 2 ) {
                    $fail( __( 'subscription_plan.unable_to_edit_real_plan' ) );
                    return ;
                }
            } ],
            'user_id' => [ 'required','exists:users,id' ],
            'end_date' => [ 'required' ],
        ] );

        $attributeName = [
            'user_id' => __( 'subscription_group_member.user' ),
            'end_date' => __( 'subscription_group_member.end_date' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();

        try {
            $updateUserSubscription = UserSubscription::lockForUpdate()->find( $request->id );
            $updateUserSubscription->user_id = $request->user_id;
            $updateUserSubscription->end_date = $request->end_date;
            $updateUserSubscription->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.user_subscriptions' ) ) ] ),
        ] );
    }

    public static function updateUserSubscriptionStatus( $request ) {
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'subscriptionGroupMember.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateUserSubscription = UserSubscription::lockForUpdate()->find( $request->id );
            $updateUserSubscription->status = 20;
            $updateUserSubscription->save();
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.subscription_group_members' ) ) ] ),
        ] );
    }
}