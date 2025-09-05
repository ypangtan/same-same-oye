<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use App\Models\{
    FileManager,
    Rank,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

class RankService
{
    public static function allranks( $request ) {

        $rank = Rank::select( 'ranks.*' );

        $filterObject = self::filter( $request, $rank );
        $rank = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $rank->orderBy( 'created_at', $dir );
                    break;
                case 3:
                    $rank->orderBy( 'name', $dir );
                    break;
                case 4:
                    $rank->orderBy( 'phone_number', $dir );
                    break;
                case 5:
                    $rank->orderBy( 'identification_number', $dir );
                    break;
                case 6:
                    $rank->orderBy( 'license_number', $dir );
                    break;
                case 7:
                    $rank->orderBy( 'license_expiry_date', $dir );
                    break;
                case 8:
                    $rank->orderBy( 'designation', $dir );
                    break;
            }
        }

        $rankCount = $rank->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $ranks = $rank->skip( $offset )->take( $limit )->get();

        if ( $ranks ) {
            $ranks->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = Rank::count();

        $data = [
            'ranks' => $ranks,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $rankCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'ranks.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'ranks.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneRank( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $rank = Rank::find( $request->id );

        if( $rank ) {
            $rank->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $rank );
    }

    public static function createRank( $request ) {

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'description' => [ 'nullable' ],
            'priority' => [ 'required' ],
            'target_spending' => [ 'required' ],
            'reward_value' => [ 'required' ],
        ] );

        $attributeName = [
            'name' => __( 'rank.name' ),
            'description' => __( 'rank.description' ),
            'priority' => __( 'rank.priority' ),
            'target_spending' => __( 'rank.target_spending' ),
            'reward_value' => __( 'rank.reward_value' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createrank = Rank::create( [
                'title' => $request->name,
                'description' => $request->description,
                'priority' => $request->priority,
                'target_spending' => $request->target_spending,
                'reward_value' => $request->reward_value,
            ] );
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.ranks' ) ) ] ),
        ] );
    }

    public static function updateRank( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'description' => [ 'nullable' ],
            'priority' => [ 'required' ],
            'target_spending' => [ 'required' ],
            'reward_value' => [ 'required' ],
        ] );

        $attributeName = [
            'name' => __( 'rank.name' ),
            'description' => __( 'rank.description' ),
            'priority' => __( 'rank.priority' ),
            'target_spending' => __( 'rank.target_spending' ),
            'reward_value' => __( 'rank.reward_value' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updaterank = Rank::find( $request->id );
            $updaterank->title = $request->name;
            $updaterank->description = $request->description;
            $updaterank->priority = $request->priority;
            $updaterank->target_spending = $request->target_spending;
            $updaterank->reward_value = $request->reward_value;
            $updaterank->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.ranks' ) ) ] ),
        ] );
    }

    public static function updateRankStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updaterank = Rank::find( $request->id );
        $updaterank->status = $request->status;
        $updaterank->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.ranks' ) ) ] ),
        ] );
    }

    public static function getAllRanks( ) {
        $rank = Rank::where( 'status', '10' )->get();

        $rank->append( [
            'encrypted_id',
            'target_range',
        ] );

        return response()->json( [
            'data' => $rank,
        ] );
    }
}