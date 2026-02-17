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
            'items',
            'item',
            'category',
            'type',
            'administrator',
        ] )->select( 'playlists.*' )
            ->where( 'is_item', 0 );

        $filterObject = self::filter( $request, $playlist );
        $playlist = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            switch ( $request->input( 'order.0.column' ) ) {
                default:
                    $playlist->orderBy( 'playlists.created_at', $dir );
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

        if( !empty( $request->type ) ) {
            $totalRecord = Playlist::where( 'playlists.is_item', 0 )->where( 'type_id', $request->type )->count();
        } else {
            $totalRecord = Playlist::where( 'playlists.is_item', 0 )->count();
        }

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
                $q->where( 'playlists.en_name', 'LIKE', '%' . $request->name . '%' )
                    ->orWhere( 'playlists.zh_name', 'LIKE', '%' . $request->name . '%' );
            } );
            $filter = true;
        }

        if ( !empty( $request->admin ) ) {
            $admin = \Helper::decode( $request->admin );
            $model->where( 'playlists.add_by', $admin );
            $filter = true;
        }

        if ( !empty( $request->collection ) ) {
            $collection = \Helper::decode( $request->collection );
            $model->where( 'playlists.collection_id', $collection );
            $filter = true;
        }

        if ( !empty( $request->category_id ) ) {
            $category_id = \Helper::decode( $request->category_id );
            $model->where( 'playlists.category_id', $category_id );
            $filter = true;
        }

        if( !empty( $request->status ) ) {
            $model->where( 'playlists.status', $request->status );
            $filter = true;
        }
        
        if( !empty( $request->type ) ) {
            $model->where( 'type_id', $request->type );
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
            'type',
            'item',
            'items',
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
            'type_id' => [ 'required', 'exists:types,id' ],
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'membership_level' => [ 'nullable' ],
            'items' => [ 'required', function ( $attribute, $value, $fail ) {
                $items = json_decode( $value, true );
                if ( empty( $items ) || !is_array( $items ) || count( $items ) == 0 ) {
                    $fail( __( 'validation.required' ) );
                    return false;
                }
            } ],
        ] );

        $attributeName = [
            'type_id' => __( 'playlist.type' ),
            'en_name' => __( 'playlist.name' ),
            'zh_name' => __( 'playlist.name' ),
            'image' => __( 'playlist.image' ),
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
                'type_id' => $request->type_id,
                'en_name' => $request->en_name,
                'zh_name' => $request->zh_name,
                'image' => $request->image,
                'membership_level' => $request->membership_level,
                'file_type' => $request->file_type,
                'status' => 10,
            ] );
    
            $items = json_decode( $request->items, true );
            $syncData = [];
            foreach ( $items as $index => $item ) {
                $syncData[$item['id']] = ['priority' => $index + 1];
            }

            $createPlaylist->items()->sync( $syncData );

            if( !empty( $request->category_id ) ) {
                $category = explode( ',', $request->category_id );
            } else {
                $category = [];
            }
            $createPlaylist->category()->sync( $category );

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
            'type_id' => [ 'required', 'exists:types,id' ],
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'membership_level' => [ 'nullable' ],
            'items' => [ 'required', function ( $attribute, $value, $fail ) {
                $items = json_decode( $value, true );
                if ( empty( $items ) || !is_array( $items ) || count( $items ) == 0 ) {
                    $fail( __( 'validation.required' ) );
                    return false;
                }
            } ],
        ] );

        $attributeName = [
            'type_id' => __( 'playlist.type' ),
            'en_name' => __( 'playlist.name' ),
            'zh_name' => __( 'playlist.name' ),
            'image' => __( 'playlist.image' ),
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
            $updatePlaylist->type_id = $request->type_id;
            $updatePlaylist->en_name = $request->en_name;
            $updatePlaylist->zh_name = $request->zh_name;
            $updatePlaylist->image = $request->image;
            $updatePlaylist->membership_level = $request->membership_level;
            $updatePlaylist->file_type = $request->file_type;
            $updatePlaylist->save();

            $items = json_decode( $request->items, true );
            $syncData = [];
            foreach ( $items as $index => $item ) {
                $syncData[$item['id']] = ['priority' => $index + 1];
            }

            $updatePlaylist->items()->sync( $syncData );

            if( !empty( $request->category_id ) ) {
                $category = explode( ',', $request->category_id );
            } else {
                $category = [];
            }
            $updatePlaylist->category()->sync( $category );

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

    public static function deletePlaylist( $request ) {
        
        $request->merge( [
            'id' => \Helper::decode( $request->id ),
        ] );

        $updatePlaylist = Playlist::find( $request->id );
        
        $localPath = storage_path ('app/public/' . $updatePlaylist->image );
        if ( !file_exists( $localPath ) ) {
            StorageService::delete( $updatePlaylist->image );
        }
        $updatePlaylist->delete();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.playlists' ) ) ] ),
        ] );
    }

    public static function getPlaylists( $request ) {

        if ( !empty( $request->collection_id ) ) {
            $request->merge( [
                'collection_id' => \Helper::decode( $request->collection_id )
            ] );
        }

        if ( !empty( $request->type_id ) ) {
            $request->merge( [
                'type_id' => \Helper::decode( $request->type_id )
            ] );
        }

        if ( !empty( $request->category_id ) ) {
            $request->merge( [
                'category_id' => \Helper::decode( $request->category_id )
            ] );
        }

        $playlists = Playlist::with([
            'item',
            'items',
        ])->select('playlists.*')
            ->when(!empty($request->collection_id), function ($q) use ($request) {
                $q->join('collection_playlists as pc', function ($join) use ($request) {
                    $join->on('pc.playlist_id', '=', 'playlists.id')
                        ->where('pc.collection_id', $request->collection_id)
                        ->where('pc.status', 10);
                });
            })
            ->when(!empty($request->type_id), function ($q) use ($request) {
                $q->where('playlists.type_id', $request->type_id);
            })
            ->when(!empty($request->category_id), function ($q) use ($request) {
                $q->whereHas('category', function( $sq ) use ( $request ) {
                    $sq->where( 'categories.id', $request->category_id );
                });
            })
            ->where('playlists.status', 10);

        if( !auth()->check() || auth()->user()->membership == 0 ) {
            // for membership level filter
            $playlists->where( 'playlists.membership_level', 0 );
        }

        if (empty($request->collection_id)) {
            $playlists->orderBy('playlists.created_at', 'desc');
        } else {
            $playlists->orderBy('pc.priority', 'asc'); // 或 desc
        }

        $playlists = $playlists->paginate( empty( $request->per_page ) ? 100 : $request->per_page );


        $playlists->getCollection()->transform(function ($playlist) {
            $playlist->append( [
                'encrypted_id',
                'image_url',
                'name',
            ] );

            if ( $playlist->relationLoaded('item') && $playlist->item ) {
                $playlist->item->append( [
                    'encrypted_id',
                    'image_url',
                    'file_url',
                ] );
            }

            if ( $playlist->relationLoaded('items') && $playlist->items ) {
                $playlist->items->transform(function ($item) {
                    $item->append( [
                        'encrypted_id',
                        'image_url',
                        'file_url',
                    ] );
                    return $item;
                });
            }


            return $playlist;
        });

        return response()->json( $playlists );
    }

    public static function getPlaylist( $request ) {

        $playlist = Playlist::with( [
            'item',
            'items',
        ] )->find( Helper::decode( $request->id ) );

        $playlist->append( [
            'encrypted_id',
            'image_url',
            'name',
        ] );

        if( $playlist->item ) {
            $playlist->item->append( [
                'encrypted_id',
                'image_url',
                'file_url',
            ] );
        }

        if( $playlist->items ) {
            foreach( $playlist->items as $item ) {
                $item->append( [
                    'encrypted_id',
                    'image_url',
                    'file_url',
                ] );
            }
        }
  
        return response()->json( $playlist );
    }

}