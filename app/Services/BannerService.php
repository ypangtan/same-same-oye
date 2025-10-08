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
    Banner,
    User,
    Role as RoleModel
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class BannerService
{
    public static function allBanners( $request ) {

        $banner = Banner::select( 'banners.*' );

        $filterObject = self::filter( $request, $banner );
        $banner = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            switch ( $request->input( 'order.0.column' ) ) {
                default:
                    $banner->orderBy( 'created_at', $dir );
                    break;
            }
        }

        $bannerCount = $banner->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $banners = $banner->skip( $offset )->take( $limit )->get();

        if ( $banners ) {
            $banners->append( [
                'encrypted_id',
                'name',
                'desc',
            ] );
        }

        $totalRecord = Banner::count();

        $data = [
            'banners' => $banners,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $bannerCount : $totalRecord,
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

                $model->whereBetween( 'banners.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_at );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'banners.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
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

    public static function oneBanner( $request ) {

        $banner = Banner::find( Helper::decode( $request->id ) );

        $banner->append( [
            'encrypted_id',
            'name',
            'desc',
        ] );

        return response()->json( $banner );
    }

    public static function createBanner( $request ) {

        $validator = Validator::make( $request->all(), [
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'en_desc' => [ 'nullable' ],
            'zh_desc' => [ 'nullable' ],
            'image' => [ 'nullable' ],
        ] );

        $attributeName = [
            'en_name' => __( 'banner.name' ),
            'zh_name' => __( 'banner.name' ),
            'en_desc' => __( 'banner.desc' ),
            'zh_desc' => __( 'banner.desc' ),
            'image' => __( 'banner.image' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createBanner = Banner::create( [
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.banners' ) ) ] ),
        ] );
    }

    public static function updateBanner( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'en_desc' => [ 'nullable' ],
            'zh_desc' => [ 'nullable' ],
            'image' => [ 'nullable' ],
        ] );

        $attributeName = [
            'en_name' => __( 'banner.name' ),
            'zh_name' => __( 'banner.name' ),
            'en_desc' => __( 'banner.desc' ),
            'zh_desc' => __( 'banner.desc' ),
            'image' => __( 'banner.image' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateBanner = Banner::find( $request->id );
            $updateBanner->en_name = $request->en_name;
            $updateBanner->zh_name = $request->zh_name;
            $updateBanner->en_desc = $request->en_desc;
            $updateBanner->zh_desc = $request->zh_desc;
            $updateBanner->image = $request->image;
            $updateBanner->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.banners' ) ) ] ),
        ] );
    }

    public static function updateBannerStatus( $request ) {
        
        $request->merge( [
            'id' => \Helper::decode( $request->id ),
        ] );

        $updateBanner = Banner::find( $request->id );
        $updateBanner->status = $request->status;
        $updateBanner->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.banners' ) ) ] ),
        ] );
    }

    public static function getBanners( $request ) {

        $banner = Banner::select( 'banners.*' );

        $filterObject = self::filter( $request, $banner );
        $banner = $filterObject['model'];
        $filter = $filterObject['filter'];

        $banner->orderBy( 'sequence' );

        $banners = $banner->paginate( empty( $request->per_page ) ? 100 : $request->per_page );

        return response()->json( $banners );
    }

}