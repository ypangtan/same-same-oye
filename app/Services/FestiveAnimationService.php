<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    FileManager,
    FestiveAnimation,
};

use Helper;

use Carbon\Carbon;

class FestiveAnimationService
{
    public static function allFestiveAnimations( $request ) {

        $festiveAnimation = FestiveAnimation::select( 'festive_animations.*' );

        $filterObject = self::filter( $request, $festiveAnimation );
        $festiveAnimation = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            $festiveAnimation->orderBy( 'created_at', $dir );
        }

        $festiveAnimationCount = $festiveAnimation->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $festiveAnimations = $festiveAnimation->skip( $offset )->take( $limit )->get();

        if ( $festiveAnimations ) {
            $festiveAnimations->append( [
                'encrypted_id',
                'file_path',
                'title',
            ] );
        }

        $totalRecord = FestiveAnimation::count();

        $data = [
            'festive_animations' => $festiveAnimations,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $festiveAnimationCount : $totalRecord,
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

                $model->whereBetween( 'festive_animations.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'festive_animations.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->title ) ) {
            $model->where( function( $q ) use ( $request ) {
                $q->where( 'en_title', 'LIKE', '%' . $request->title . '%' )
                    ->orWhere( 'zh_title', 'LIKE', '%' . $request->title . '%' );
            } );
            $filter = true;
        }

        if ( !empty( $request->file_type ) ) {
            $model->where( 'file_type', $request->file_type );
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

    public static function oneFestiveAnimation( $request ) {

        $festiveAnimation = FestiveAnimation::find( Helper::decode( $request->id ) );

        if ( $festiveAnimation ) {
            $festiveAnimation->append( [
                'encrypted_id',
                'file_path',
                'title',
            ] );
        }

        return response()->json( $festiveAnimation );
    }

    private static function validateFestiveAnimation( $request ) {

        $validator = Validator::make( $request->all(), [
            'en_title' => [ 'required' ],
            'zh_title' => [ 'nullable' ],
            'file' => [ 'required' ],
            'file_name' => [ 'nullable' ],
        ] );

        $attributeName = [
            'en_title' => __( 'festive_animation.title' ),
            'zh_title' => __( 'festive_animation.title' ),
            'file' => __( 'festive_animation.file' ),
            'file_name' => __( 'festive_animation.file' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
    }

    public static function createFestiveAnimation( $request ) {

        self::validateFestiveAnimation( $request );

        DB::beginTransaction();

        try {

            // Only one festive animation can be active at a time
            FestiveAnimation::where( 'status', 10 )->update( [ 'status' => 20 ] );

            FestiveAnimation::create( [
                'en_title' => $request->en_title,
                'zh_title' => $request->zh_title,
                'file' => $request->file,
                'file_name' => $request->file_name,
                'file_type' => FestiveAnimation::resolveFileType( $request->file ),
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.festive_animations' ) ) ] ),
        ] );
    }

    public static function updateFestiveAnimation( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        self::validateFestiveAnimation( $request );

        DB::beginTransaction();

        try {

            $updateFestiveAnimation = FestiveAnimation::find( $request->id );

            $oldFile = $updateFestiveAnimation->file;

            $updateFestiveAnimation->en_title = $request->en_title;
            $updateFestiveAnimation->zh_title = $request->zh_title;
            if ( $oldFile != $request->file ) {
                $updateFestiveAnimation->file = $request->file;
                $updateFestiveAnimation->file_name = $request->file_name;
                $updateFestiveAnimation->file_type = FestiveAnimation::resolveFileType( $request->file );
            }
            $updateFestiveAnimation->save();

            DB::commit();

            if ( $oldFile && $oldFile != $request->file ) {
                StorageService::delete( $oldFile );
            }

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.festive_animations' ) ) ] ),
        ] );
    }

    public static function updateFestiveAnimationStatus( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            // Only one festive animation can be active at a time
            if ( $request->status == 10 ) {
                FestiveAnimation::where( 'status', 10 )
                    ->where( 'id', '!=', $request->id )
                    ->update( [ 'status' => 20 ] );
            }

            $updateFestiveAnimation = FestiveAnimation::find( $request->id );
            $updateFestiveAnimation->status = $request->status;
            $updateFestiveAnimation->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.festive_animations' ) ) ] ),
        ] );
    }

    public static function deleteFestiveAnimation( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $festiveAnimation = FestiveAnimation::find( $request->id );

        if ( $festiveAnimation ) {
            if ( $festiveAnimation->file ) {
                StorageService::delete( $festiveAnimation->file );
            }
            $festiveAnimation->delete();
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.festive_animations' ) ) ] ),
        ] );
    }

    public static function fileUpload( $request ) {

        $validator = Validator::make( $request->all(), [
            'file' => [ 'required', 'file', 'max:' . ( 100 * 1024 ) ],
        ] );

        $validator->setAttributeNames( [
            'file' => strtolower( __( 'festive_animation.file' ) ),
        ] )->validate();

        $file = $request->file( 'file' );
        $path = StorageService::upload( 'festive_animation', $file );

        $createFile = FileManager::create( [
            'name' => $file->getClientOriginalName(),
            'file' => $path,
            'type' => 3,
        ] );

        return response()->json( [
            'status' => 200,
            'data' => $createFile,
            'url' => StorageService::get( $path ),
            'file' => $path,
            'file_name' => $createFile->name,
            'file_type' => FestiveAnimation::resolveFileType( $path ),
        ] );
    }

    // Client API

    public static function getFestiveAnimations( $request ) {

        $festiveAnimations = FestiveAnimation::where( 'status', 10 );

        if ( !empty( $request->file_type ) ) {
            $festiveAnimations->where( 'file_type', $request->file_type );
        }

        $festiveAnimations = $festiveAnimations->orderBy( 'created_at', 'DESC' )
            ->paginate( empty( $request->per_page ) ? 10 : $request->per_page );

        $festiveAnimations->getCollection()->append( [
            'encrypted_id',
            'file_path',
            'title',
        ] );

        return response()->json( array_merge( [
            'message' => '',
            'message_key' => 'get_festive_animation_success',
        ], $festiveAnimations->toArray() ) );
    }

    public static function getFestiveAnimation( $request ) {

        $festiveAnimation = FestiveAnimation::where( 'status', 10 )
            ->find( Helper::decode( $request->id ) );

        if ( !$festiveAnimation ) {
            return response()->json( [
                'message' => __( 'festive_animation.not_found' ),
                'message_key' => 'get_festive_animation_failed',
                'data' => null,
            ], 404 );
        }

        $festiveAnimation->append( [
            'encrypted_id',
            'file_path',
            'title',
        ] );

        return response()->json( [
            'message' => '',
            'message_key' => 'get_festive_animation_success',
            'data' => $festiveAnimation,
        ] );
    }
}
