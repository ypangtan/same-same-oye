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
    Playlist,
    User,
    Role as RoleModel
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class PlaylistService
{
    public static function allPlaylists( $request ) {

        $playlist = Playlist::with( [
            'collection',
            'category',
            'administrator',
        ] )->select( 'playlists.*' );

        $filterObject = self::filter( $request, $playlist );
        $playlist = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            switch ( $request->input( 'order.0.column' ) ) {
                default:
                    $playlist->orderBy( 'created_at', $dir );
                    break;
            }
        }

        $playlistCount = $playlist->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $playlists = $playlist->skip( $offset )->take( $limit )->get();

        if ( $playlists ) {
            $playlists->append( [
                'encrypted_id',
                'name',
                'image_url',
            ] );
        }

        $totalRecord = Playlist::count();

        $data = [
            'playlists' => $playlists,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $playlistCount : $totalRecord,
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

                $model->whereBetween( 'playlists.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_at );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'playlists.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
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

        if ( !empty( $request->admin ) ) {
            $admin = \Helper::decode( $request->admin );
            $model->where( 'add_by', $admin );
            $filter = true;
        }

        if ( !empty( $request->collection ) ) {
            $collection = \Helper::decode( $request->collection );
            $model->where( 'collection_id', $collection );
            $filter = true;
        }

        if ( !empty( $request->category_id ) ) {
            $category_id = \Helper::decode( $request->category_id );
            $model->where( 'category_id', $category_id );
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

    public static function onePlaylist( $request ) {

        $playlist = Playlist::with( [
            'collection',
            'category',
            'administrator',
        ] )->find( Helper::decode( $request->id ) );

        $playlist->append( [
            'encrypted_id',
            'name',
            'image_url',
        ] );

        return response()->json( $playlist );
    }

    public static function createPlaylist( $request ) {

        $validator = Validator::make( $request->all(), [
            'category_id' => [ 'required', 'exists:categories,id' ],
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'priority' => [ 'nullable' ],
            'membership_level' => [ 'nullable' ],
            'items' => [ 'nullable' ],
        ] );

        $attributeName = [
            'category_id' => __( 'playlist.category' ),
            'en_name' => __( 'playlist.en_name' ),
            'zh_name' => __( 'playlist.zh_name' ),
            'image' => __( 'playlist.image' ),
            'priority' => __( 'playlist.priority' ),
            'membership_level' => __( 'playlist.membership_level' ),
            'items' => __( 'playlist.items' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createPlaylist = Playlist::create( [
                'add_by' => auth()->user()->id,
                'category_id' => $request->category_id,
                'en_name' => $request->en_name,
                'zh_name' => $request->zh_name,
                'image' => $request->image,
                'priority' => $request->priority ?? 0,
                'membership_level' => $request->membership_level,
                'status' => 10,
            ] );
    
            $createPlaylist->items()->sync( $request->items ?? [] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.playlists' ) ) ] ),
        ] );
    }

    public static function updatePlaylist( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'category_id' => [ 'required', 'exists:categories,id' ],
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'priority' => [ 'nullable' ],
            'membership_level' => [ 'nullable' ],
            'items' => [ 'nullable' ],
        ] );

        $attributeName = [
            'category_id' => __( 'playlist.category' ),
            'en_name' => __( 'playlist.en_name' ),
            'zh_name' => __( 'playlist.zh_name' ),
            'image' => __( 'playlist.image' ),
            'priority' => __( 'playlist.priority' ),
            'membership_level' => __( 'playlist.membership_level' ),
            'items' => __( 'playlist.items' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updatePlaylist = Playlist::find( $request->id );
            $updatePlaylist->category_id = $request->category_id;
            $updatePlaylist->en_name = $request->en_name;
            $updatePlaylist->zh_name = $request->zh_name;
            $updatePlaylist->image = $request->image;
            $updatePlaylist->priority = $request->priority ?? 0;
            $updatePlaylist->membership_level = $request->membership_level;
            $updatePlaylist->save();

            $updatePlaylist->items()->sync( $request->items ?? [] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.playlists' ) ) ] ),
        ] );
    }

    public static function updatePlaylistStatus( $request ) {
        
        $request->merge( [
            'id' => \Helper::decode( $request->id ),
        ] );

        $updatePlaylist = Playlist::find( $request->id );
        $updatePlaylist->status = $request->status;
        $updatePlaylist->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.playlists' ) ) ] ),
        ] );
    }

}