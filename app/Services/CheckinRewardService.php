<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
};

use Helper;

use App\Models\{
    Company,
    Customer,
    CheckinReward,
    Booking,
    FileManager,
    VendingMachine,
    VendingMachineStock
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CheckinRewardService
{

    public static function createCheckinReward( $request ) {

        $validator = Validator::make( $request->all(), [
            'reward_type' => [ 'required' ],
            'consecutive_days' => [ 'required' ],
            'voucher_quantity' => [ 'nullable', 'min:0' ],
            'voucher' => [ 'nullable', 'exists:vouchers,id' ],
            'points' => [ 'nullable', 'min:0' ],
        ] );

        $attributeName = [
            'reward_type' => __( 'checkin_reward.reward_type' ),
            'consecutive_days' => __( 'checkin_reward.consecutive_days' ),
            'voucher_quantity' => __( 'checkin_reward.voucher_quantity' ),
            'voucher' => __( 'checkin_reward.voucher' ),
            'points' => __( 'checkin_reward.points' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $checkinRewardCreate = CheckinReward::create([
                'consecutive_days' => $request->consecutive_days,
                'reward_type' => $request->reward_type,
                'reward_value' => $request->reward_type == 1 ? $request->points : $request->voucher_quantity,
                'validity_days' => null,
                'voucher_id' => $request->voucher,
            ]);

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.checkin_rewards' ) ) ] ),
        ] );
    }
    
    public static function updateCheckinReward( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'reward_type' => [ 'required' ],
            'consecutive_days' => [ 'required' ],
            'voucher_quantity' => [ 'nullable', 'min:0' ],
            'voucher' => [ 'nullable', 'exists:vouchers,id' ],
            'points' => [ 'nullable', 'min:0' ],
        ] );

        $attributeName = [
            'reward_type' => __( 'checkin_reward.reward_type' ),
            'consecutive_days' => __( 'checkin_reward.consecutive_days' ),
            'voucher_quantity' => __( 'checkin_reward.voucher_quantity' ),
            'voucher' => __( 'checkin_reward.voucher' ),
            'points' => __( 'checkin_reward.points' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateCheckinReward = CheckinReward::find( $request->id );
    
            $updateCheckinReward->consecutive_days = $request->consecutive_days;
            $updateCheckinReward->reward_type = $request->reward_type;
            $updateCheckinReward->reward_value = $request->reward_value;
            $updateCheckinReward->validity_days = $request->validity_days;
            $updateCheckinReward->voucher_id = $request->voucher;
            $updateCheckinReward->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.checkin_rewards' ) ) ] ),
        ] );
    }

    public static function allCheckinRewards( $request ) {

        $checkinRewards = CheckinReward::with( ['voucher'] )->select( 'checkin_rewards.*');

        $filterObject = self::filter( $request, $checkinRewards );
        $checkinReward = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $checkinReward->orderBy( 'checkin_rewards.created_at', $dir );
                    break;
                case 2:
                    $checkinReward->orderBy( 'checkin_rewards.title', $dir );
                    break;
                case 3:
                    $checkinReward->orderBy( 'checkin_rewards.description', $dir );
                    break;
            }
        }

            $checkinRewardCount = $checkinReward->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $checkinRewards = $checkinReward->skip( $offset )->take( $limit )->get();

            if ( $checkinRewards ) {
                $checkinRewards->append( [
                    'encrypted_id',
                ] );
            }

            $totalRecord = CheckinReward::count();

            $data = [
                'checkin_rewards' => $checkinRewards,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $checkinRewardCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->id ) ) {
            $model->where( 'checkin_reward.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->reward_type ) ) {
            $model->where( 'reward_type', $request->reward_type );
            $filter = true;
        }

        if ( !empty( $request->voucher ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'voucher', function ( $q ) use ( $request ) {
                    $q->where( 'title', 'LIKE', '%' . $request->voucher . '%' );
                });
            });
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneCheckinReward( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $checkinReward = CheckinReward::with( ['voucher'] )->find( $request->id );

        $checkinReward->append( ['encrypted_id'] );
        
        return response()->json( $checkinReward );
    }

    public static function deleteCheckinReward( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'checkin_reward.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            CheckinReward::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.checkin_rewards' ) ) ] ),
        ] );
    }

    public static function updateCheckinRewardStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateCheckinReward = CheckinReward::find( $request->id );
            $updateCheckinReward->status = $updateCheckinReward->status == 10 ? 20 : 10;

            $updateCheckinReward->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'checkin_reward' => $updateCheckinReward,
                    'message_key' => 'update_checkinreward_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_checkinreward_failed',
            ], 500 );
        }
    }

    public static function removeCheckinRewardGalleryImage( $request ) {

        $updateFarm = CheckinReward::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
    
}