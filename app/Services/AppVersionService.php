<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use App\Models\{
    AppVersion,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

class AppVersionService
{
    public static function allAppVersions( $request ) {

        $appVersion = AppVersion::select( 'app_versions.*' );

        $filterObject = self::filter( $request, $appVersion );
        $appVersion = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $appVersion->orderBy( 'created_at', $dir );
                    break;
                case 3:
                    $appVersion->orderBy( 'version', $dir );
                    break;
            }
        }

        $appVersionCount = $appVersion->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $appVersions = $appVersion->skip( $offset )->take( $limit )->get();

        if ( $appVersions ) {
            $appVersions->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = AppVersion::count();

        $data = [
            'app_versions' => $appVersions,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $appVersionCount : $totalRecord,
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

                $model->whereBetween( 'app_versions.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'app_versions.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->version ) ) {
            $model->where( 'version', $request->version );
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

    public static function oneAppVersion( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $app_version = AppVersion::find( $request->id );

        if( $app_version ) {
            $app_version->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $app_version );
    }

    public static function createAppVersion( $request ) {

        $validator = Validator::make( $request->all(), [
            'version' => [ 'required' ],
            'force_logout' => [ 'required', 'in:10,20' ],
            'en_notes' => [ 'required' ],
            'zh_notes' => [ 'nullable' ],
            'en_desc' => [ 'required' ],
            'zh_desc' => [ 'nullable' ],
            'platform' => [ 'required', 'in:1,2,3' ],
        ] );

        $attributeName = [
            'version' => __( 'app_version.version' ),
            'force_logout' => __( 'app_version.force_logout' ),
            'en_notes' => __( 'app_version.notes' ),
            'zh_notes' => __( 'app_version.notes' ),
            'en_desc' => __( 'app_version.desc' ),
            'zh_desc' => __( 'app_version.desc' ),
            'platform' => __( 'app_version.platform' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createappVersion = AppVersion::create( [
                'version' => $request->version,
                'force_logout' => $request->force_logout,
                'en_notes' => $request->en_notes,
                'zh_notes' => $request->zh_notes,
                'en_desc' => $request->en_desc,
                'zh_desc' => $request->zh_desc,
                'platform' => $request->platform,
            ] );
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.app_versions' ) ) ] ),
        ] );
    }

    public static function updateAppVersion( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'version' => [ 'required' ],
            'force_logout' => [ 'required', 'in:10,20' ],
            'en_notes' => [ 'required' ],
            'zh_notes' => [ 'nullable' ],
            'en_desc' => [ 'required' ],
            'zh_desc' => [ 'nullable' ],
            'platform' => [ 'required', 'in:1,2,3' ],
        ] );

        $attributeName = [
            'version' => __( 'app_version.version' ),
            'force_logout' => __( 'app_version.force_logout' ),
            'en_notes' => __( 'app_version.notes' ),
            'zh_notes' => __( 'app_version.notes' ),
            'en_desc' => __( 'app_version.desc' ),
            'zh_desc' => __( 'app_version.desc' ),
            'platform' => __( 'app_version.platform' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateapp_version = AppVersion::find( $request->id );
            $updateapp_version->version = $request->version;
            $updateapp_version->force_logout = $request->force_logout;
            $updateapp_version->en_notes = $request->en_notes;
            $updateapp_version->zh_notes = $request->zh_notes;
            $updateapp_version->en_desc = $request->en_desc;
            $updateapp_version->zh_desc = $request->zh_desc;
            $updateapp_version->platform = $request->platform;
            $updateapp_version->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.app_versions' ) ) ] ),
        ] );
    }

    public static function updateAppVersionStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateapp_version = AppVersion::find( $request->id );
        $updateapp_version->status = $request->status;
        $updateapp_version->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.app_versions' ) ) ] ),
        ] );
    }

    public static function lastestAppVersion( $request ) {

        $app_version = AppVersion::where( 'status', '10' )
            ->where( 'platform', $request->plaform )
            ->orderBy( 'version', 'desc' )
            ->first();

        return response()->json( [
            'data' => $app_version,
        ] );
    }
}