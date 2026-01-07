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
    PopAnnouncement,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

class PopAnnouncementService
{
    public static function allPopAnnouncements( $request ) {

        $rank = PopAnnouncement::select( 'pop_announcements.*' );

        $filterObject = self::filter( $request, $rank );
        $rank = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $rank->orderBy( 'created_at', $dir );
                    break;
            }
        }

        $rankCount = $rank->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $pop_announcements = $rank->skip( $offset )->take( $limit )->get();

        if ( $pop_announcements ) {
            $pop_announcements->append( [
                'encrypted_id',
                'image_path',
                'title',
                'text',
            ] );
        }

        $totalRecord = PopAnnouncement::count();

        $data = [
            'pop_announcements' => $pop_announcements,
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

                $model->whereBetween( 'pop_announcements.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'pop_announcements.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
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

    public static function onePopAnnouncement( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $rank = PopAnnouncement::find( $request->id );

        if( $rank ) {
            $rank->append( [
                'encrypted_id',
                'image_path',
                'title',
                'text',
            ] );
        }

        return response()->json( $rank );
    }

    public static function createPopAnnouncement( $request ) {

        $validator = Validator::make( $request->all(), [
            'en_title' => [ 'required' ],
            'zh_title' => [ 'nullable' ],
            'image' => [ 'required' ],
            'url' => [ 'nullable' ],
            'en_text' => [ 'nullable' ],
            'zh_text' => [ 'nullable' ],
        ] );

        $attributeName = [
            'en_title' => __( 'announcement.title' ),
            'zh_title' => __( 'announcement.title' ),
            'image' => __( 'announcement.image' ),
            'url' => __( 'announcement.image' ),
            'en_text' => __( 'announcement.text' ),
            'zh_text' => __( 'announcement.text' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createrank = PopAnnouncement::create( [
                'en_title' => $request->en_title,
                'zh_title' => $request->zh_title,
                'image' => $request->image,
                'url' => $request->url,
                'en_text' => $request->en_text,
                'zh_text' => $request->zh_text,
            ] );
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.pop_announcements' ) ) ] ),
        ] );
    }

    public static function updatePopAnnouncement( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'en_title' => [ 'required' ],
            'zh_title' => [ 'nullable' ],
            'url' => [ 'nullable' ],
            'image' => [ 'required' ],
            'en_text' => [ 'nullable' ],
            'zh_text' => [ 'nullable' ],
        ] );

        $attributeName = [
            'en_title' => __( 'announcement.title' ),
            'zh_title' => __( 'announcement.title' ),
            'en_text' => __( 'announcement.text' ),
            'zh_text' => __( 'announcement.text' ),
            'image' => __( 'announcement.image' ),
            'url' => __( 'announcement.url' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updaterank = PopAnnouncement::find( $request->id );
            $updaterank->en_title = $request->en_title;
            $updaterank->zh_title = $request->zh_title;
            if( $updaterank->image != $request->image ) {
                Storage::disk('public')->delete( $updaterank->image );
            }
            $updaterank->url = $request->url;
            $updaterank->image = $request->image;
            $updaterank->en_text = $request->en_text;
            $updaterank->zh_text = $request->zh_text;
            $updaterank->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.pop_announcements' ) ) ] ),
        ] );
    }

    public static function updatePopAnnouncementStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updaterank = PopAnnouncement::find( $request->id );
        $updaterank->status = $request->status;
        $updaterank->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.pop_announcements' ) ) ] ),
        ] );
    }

    public static function ckeUpload( $request ) {

        $file = $request->file( 'file' )->store( 'pop_announcement/ckeditor', [ 'disk' => 'public' ] );

        $data = [
            'url' => asset( 'storage/' . $file ),
        ];

        return response()->json( $data );
    }

    public static function imageUpload( $request ) {

        $file = $request->file( 'file' )->store( 'pop_announcement/image', [ 'disk' => 'public' ] );

        $data = [
            'url' => asset( 'storage/' . $file ),
            'file' => $file,
        ];

        return response()->json( $data );
    }

    public static function getAllPopAnnouncements() {
        $rank = PopAnnouncement::where( 'status', '10' )->get();

        $rank->append( [
            'encrypted_id',
            'image_path',
            'title',
            'text',
        ] );

        return response()->json( [
            'data' => $rank,
        ] );
    }
}