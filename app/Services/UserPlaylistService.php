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
    Item,
    Playlist,
    User,
    Role as RoleModel,
    UserPlaylist,
    UserPlaylistItem
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class UserPlaylistService
{
    public static function getUserPlaylists( $request ) {

        $playlists = UserPlaylist::with( [
            'items',
        ] )->select( 'user_playlists.*' );

        if( !empty( $request->type_id ) ) {
            $playlists->where( 'user_playlists.type_id', $request->type_id );
        }

        $playlists->orderBy( 'created_at', 'desc' );

        $playlists = $playlists->paginate( empty( $request->per_page ) ? 100 : $request->per_page );

        $playlists->getCollection()->transform(function ($playlist) {
            $playlist->append( [
                'encrypted_id',
            ] );

            if ($playlist->relationLoaded('items')) {
                $playlist->items->transform(function ($item) {
                    $item->append( [
                        'encrypted_id',
                        'image_url',
                        'song_url',
                    ] );
                    return $item;
                });
            }


            return $playlist;
        });

        return response()->json( $playlists );
    }

    public static function getUserPlaylist( $request ) {

        $playlist = UserPlaylist::with( [
            'items',
        ] )->find( Helper::decode( $request->id ) );

        $playlist->append( [
            'encrypted_id',
            'image_url',
        ] );

        if( $playlist->items ) {
            foreach( $playlist->items as $item ) {
                $item->append( [
                    'encrypted_id',
                    'image_url',
                ] );
            }
        }
  
        return response()->json( $playlist );
    }

    public static function createUserPlayList( $request ) {

        if( !empty( $request->type_id ) ) {
            $request->merge( [
                'type_id' => \Helper::decode( $request->type_id )
            ] );
        }

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'type_id' => [ 'required', 'exists:types,id'],
        ] );

        $attributeName = [
            'name' => __( 'playlist.name' ),
            'type_id' => __( 'playlist.type' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createPlaylist = UserPlaylist::create( [
                'user_id' => auth()->user()->id,
                'name' => $request->name,
                'type_id' => $request->type_id,
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.user_playlists' ) ) ] ),
        ] );
    }

    public static function updateUserPlayList( $request ) {

        $request->merge( [
            'id' => \Helper::decode( $request->id )
        ] );

        if( !empty( $request->type_id ) ) {
            $request->merge( [
                'type_id' => \Helper::decode( $request->type_id )
            ] );
        }

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'type_id' => [ 'required', 'exists:types,id'],
        ] );

        $attributeName = [
            'name' => __( 'playlist.name' ),
            'type_id' => __( 'playlist.type' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createPlaylist = UserPlaylist::find( $request->id );
            $createPlaylist->name = $request->name;
            $createPlaylist->type_id = $request->type_id;
            $createPlaylist->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.user_playlists' ) ) ] ),
        ] );
    }

    public static function deleteUserPlayList( $request ) {

        $request->merge( [
            'id' => \Helper::decode( $request->id )
        ] );

        DB::beginTransaction();

        try {

            $createPlaylist = UserPlaylist::find( $request->id );
            $createPlaylist->delete();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.user_playlists' ) ) ] ),
        ] );
    }

    public static function addSongToUserPlayList( $request ) {

        $request->merge( [
            'song_id' => \Helper::decode( $request->song_id )
        ] );

        $request->merge( [
            'user_playlist_id' => \Helper::decode( $request->user_playlist_id )
        ] );

        $validator = Validator::make( $request->all(), [
            'user_playlist_id' => [ 'required', 'exists:user_playlists,id' ],
            'song_id' => [ 'required', 'exists:items,id', function ( $attribute, $value, $fail ) use ( $request ) {
                $playlist = UserPlaylist::find( $request->user_playlist_id );
                $song = Item::find( $value );
                if( $playlist && $song ) {
                    if( $playlist->type != $song->type ) {
                        $fail( __( 'playlist.playlist_type_mismatch' ) );
                        return false;
                    }
                }
            } ],
        ]);

        $attributeName = [
            'user_playlist_id' => __( 'playlist.user_playlist' ),
            'song_id' => __( 'playlist.song' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $playlist  = UserPlaylist::find( $request->user_playlist_id );
            $playlist ->items()->attach( $request->song_id );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.user_playlists' ) ) ] ),
        ] );
    }

    public static function removeSongToUserPlayList( $request ) {

        $request->merge( [
            'id' => \Helper::decode( $request->id )
        ] );

        $validator = Validator::make( $request->all(), [
            'id' => [ 'required', 'exists:user_playlist_items,id' ],
        ] );

        $attributeName = [
            'id' => __( 'playlist.user_playlist_item' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $playlist = UserPlaylistItem::find( $request->id );
            $playlist->delete();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.user_playlists' ) ) ] ),
        ] );
    }

    public static function addPlaylistToUserPlayList( $request ) {

        $request->merge( [
            'playlist_id' => \Helper::decode( $request->playlist_id )
        ] );

        $validator = Validator::make( $request->all(), [
            'playlist_id' => [ 'required', 'exists:playlists,id' ],
        ] );

        $attributeName = [
            'playlist_id' => __( 'playlist.playlist' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $playlist = Playlist::with( 'items' )->find( $request->playlist_id );

            $userPlaylist = UserPlaylist::create( [
                'user_id' => auth()->user()->id,
                'name' => $playlist->name,
                'type_id' => $playlist->type_id,
                'status' => 10,
            ] );
            $userPlaylist->items()->attach( $playlist->items->pluck( 'id' )->toArray() );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.user_playlists' ) ) ] ),
        ] );
    }
}