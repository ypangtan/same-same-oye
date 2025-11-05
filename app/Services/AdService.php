<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Hash,
    Validator,
};

use Illuminate\Validation\Rules\Password;

use App\Models\{
    FileManager,
    Ad,
    User,
    Role as RoleModel
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class AdService
{
    public static function allAds( $request ) {

        $ad = Ad::select( 'ads.*' );

        $filterObject = self::filter( $request, $ad );
        $ad = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            switch ( $request->input( 'order.0.column' ) ) {
                default:
                    $ad->orderBy( 'created_at', $dir );
                    break;
            }
        }

        $adCount = $ad->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $ads = $ad->skip( $offset )->take( $limit )->get();

        if ( $ads ) {
            $ads->append( [
                'encrypted_id',
                'name',
                'desc',
            ] );
        }

        $totalRecord = Ad::count();

        $data = [
            'ads' => $ads,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $adCount : $totalRecord,
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

                $model->whereBetween( 'ads.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_at );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'ads.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( function( $q ) use ( $request ) {
                $q->where( 'en_name', 'LIKE', '%' . $request->name . '%' )
                    ->orWhere( 'zh_name', 'LIKE', '%' . $request->name . '%' );
            } );
            $filter = true;
        }

        if( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneAd( $request ) {

        $ad = Ad::find( Helper::decode( $request->id ) );

        $ad->append( [
            'encrypted_id',
            'name',
            'desc',
        ] );

        return response()->json( $ad );
    }

    public static function createAd( $request ) {

        $validator = Validator::make( $request->all(), [
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'en_desc' => [ 'required' ],
            'zh_desc' => [ 'nullable' ],
            'image' => [ 'nullable' ],
        ] );

        $attributeName = [
            'en_name' => __( 'ad.name' ),
            'zh_name' => __( 'ad.name' ),
            'en_desc' => __( 'ad.desc' ),
            'zh_desc' => __( 'ad.desc' ),
            'image' => __( 'ad.image' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createAd = Ad::create( [
                'en_name' => $request->en_name,
                'zh_name' => $request->zh_name,
                'en_desc' => $request->en_desc,
                'zh_desc' => $request->zh_desc,
                'image' => $request->image,
                'status' => 10,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.ads' ) ) ] ),
        ] );
    }

    public static function updateAd( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'en_desc' => [ 'required' ],
            'zh_desc' => [ 'nullable' ],
            'image' => [ 'nullable' ],
        ] );

        $attributeName = [
            'en_name' => __( 'ad.name' ),
            'zh_name' => __( 'ad.name' ),
            'en_desc' => __( 'ad.desc' ),
            'zh_desc' => __( 'ad.desc' ),
            'image' => __( 'ad.image' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateAd = Ad::find( $request->id );
            $updateAd->en_name = $request->en_name;
            $updateAd->zh_name = $request->zh_name;
            $updateAd->en_desc = $request->en_desc;
            $updateAd->zh_desc = $request->zh_desc;
            $updateAd->image = $request->image;
            $updateAd->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.ads' ) ) ] ),
        ] );
    }

    public static function updateAdStatus( $request ) {
        
        $request->merge( [
            'id' => \Helper::decode( $request->id ),
        ] );

        $updateAd = Ad::find( $request->id );
        $updateAd->status = $request->status;
        $updateAd->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.ads' ) ) ] ),
        ] );
    }

    public static function getAds( $request ) {

    }

    public static function getAd ( $request ) {
        
    }
}