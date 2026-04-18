<?php

namespace App\Services;

use Helper;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    TrendingContent,
};

class TrendingContentService
{
    public static function allTrendingContents( $request ) {

        $trending_content = TrendingContent::select( 'trending_contents.*' );

        $filterObject = self::filter( $request, $trending_content );
        $trending_content = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            switch ( $request->input( 'order.0.column' ) ) {
                default:
                    $trending_content->orderBy( 'created_at', $dir );
                    break;
            }
        }

        $trending_contentCount = $trending_content->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $trending_contents = $trending_content->skip( $offset )->take( $limit )->get();

        if ( $trending_contents ) {
            $trending_contents->append( [
                'encrypted_id',
                'image_url',
                'song_url',
            ] );
        }

        $totalRecord = TrendingContent::count();

        $data = [
            'trending_contents' => $trending_contents,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $trending_contentCount : $totalRecord,
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

                $model->whereBetween( 'trending_contents.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_at );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'trending_contents.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
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

    public static function oneTrendingContent( $request ) {

        $trending_content = TrendingContent::find( Helper::decode( $request->id ) );

        $trending_content->append( [
            'encrypted_id',
            'image_url',
            'song_url',
        ] );

        return response()->json( $trending_content );
    }

    public static function createTrendingContent( $request ) {

        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'desc' => [ 'required' ],
            'image' => [ 'nullable' ],
            'file' => [ $request->upload_type == 1 ? 'required' : 'nullable' ],
            'url' => [ $request->upload_type == 2 ? 'required' : 'nullable' ],
            'upload_type' => [ 'required', 'in:1,2' ],
        ] );

        $attributeName = [
            'title' => __( 'trending_content.title' ),
            'desc' => __( 'trending_content.desc' ),
            'image' => __( 'trending_content.image' ),
            'file' => __( 'trending_content.file' ),
            'url' => __( 'trending_content.url' ),
            'upload_type' => __( 'trending_content.upload_type' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createTrendingContent = TrendingContent::create( [
                'title' => $request->title,
                'desc' => $request->desc,
                'image' => $request->image,
                'file' => $request->file,
                'url' => $request->url,
                'upload_type' => $request->upload_type,
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.trending_contents' ) ) ] ),
        ] );
    }

    public static function updateTrendingContent( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'desc' => [ 'required' ],
            'image' => [ 'nullable' ],
            'file' => [ $request->upload_type == 1 ? 'required' : 'nullable' ],
            'url' => [ $request->upload_type == 2 ? 'required' : 'nullable' ],
            'upload_type' => [ 'required', 'in:1,2' ],
        ] );

        $attributeName = [
            'title' => __( 'trending_content.title' ),
            'desc' => __( 'trending_content.desc' ),
            'image' => __( 'trending_content.image' ),
            'file' => __( 'trending_content.file' ),
            'url' => __( 'trending_content.url' ),
            'upload_type' => __( 'trending_content.upload_type' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateTrendingContent = TrendingContent::find( $request->id );
            $updateTrendingContent->title = $request->title;
            $updateTrendingContent->desc = $request->desc;
            $updateTrendingContent->image = $request->image;
            $updateTrendingContent->file = $request->file;
            $updateTrendingContent->url = $request->url;
            $updateTrendingContent->upload_type = $request->upload_type;
            $updateTrendingContent->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.trending_contents' ) ) ] ),
        ] );
    }

    public static function updateTrendingContentStatus( $request ) {
        
        $request->merge( [
            'id' => \Helper::decode( $request->id ),
        ] );

        $updateTrendingContent = TrendingContent::find( $request->id );
        $updateTrendingContent->status = $request->status;
        $updateTrendingContent->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.trending_contents' ) ) ] ),
        ] );
    }

    public static function getTrendingContents( $request ) {

        $trending_contents = TrendingContent::select( 'trending_contents.*' )->where( 'trending_contents.status', 10 );

        $trending_contents = $trending_contents->paginate( empty( $request->per_page ) ? 100 : $request->per_page );

        $trending_contents->getCollection()->transform( function ( $trending_content ) {
            $trending_content->append( [
                'encrypted_id',
                'image_url',
                'file_url',
            ] );
            return $trending_content;
        } );

        return response()->json( $trending_contents );
    }

    public static function getTrendingContent( $request ) {

        $trending_content = TrendingContent::find( \Helper::decode( $request->id ) );

        $trending_content->append( [
            'encrypted_id',
            'image_url',
            'file_url',
        ] );
  
        return response()->json( $trending_content );
    }
}