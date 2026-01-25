<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Hash,
    Storage,
    Validator,
};

use Illuminate\Validation\Rules\Password;

use App\Models\{
    FileManager,
    Item,
    Playlist,
    User,
    Role as RoleModel
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class ItemService
{
    public static function allItems( $request ) {

        $item = Item::with( [
            'type',
            'administrator',
            'playLists',
        ] )->select( 'items.*' );

        $filterObject = self::filter( $request, $item );
        $item = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            switch ( $request->input( 'order.0.column' ) ) {
                default:
                    $item->orderBy( 'created_at', $dir );
                    break;
            }
        }

        $itemCount = $item->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $items = $item->skip( $offset )->take( $limit )->get();

        if ( $items ) {
            $items->append( [
                'encrypted_id',
                'image_url',
            ] );
        }

        if( !empty( $request->type ) ) {
            $totalRecord = Item::where( 'type_id', $request->type )->count();
        } else {
            $totalRecord = Item::count();
        }

        $data = [
            'items' => $items,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $itemCount : $totalRecord,
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

                $model->whereBetween( 'items.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_at );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'items.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->title ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->author ) ) {
            $model->where( 'author', 'LIKE', '%' . $request->author . '%' );
            $filter = true;
        }

        if ( !empty( $request->admin ) ) {
            $admin = \Helper::decode( $request->admin );
            $model->where( 'add_by', $admin );
            $filter = true;
        }
        
        if( !empty( $request->type ) ) {
            $model->where( 'type_id', $request->type );
        }

        if( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if( !empty( $request->file_type ) ) {
            $model->where( 'file_type', $request->file_type );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneItem( $request ) {

        $item = Item::with( [
            'type',
            'administrator',
        ] )->find( Helper::decode( $request->id ) );

        $item->append( [
            'image_url',
            'song_url',
        ] );

        if( $item->type ) {
            $item->type->append( 'name' );
        }

        return response()->json( $item );
    }

    public static function createItem( $request ) {

        $validator = Validator::make( $request->all(), [
            'type_id' => [ 'required', 'exists:types,id' ],
            'title' => [ 'required' ],
            'lyrics' => [ 'nullable' ],
            'file' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'author' => [ 'nullable' ],
            'membership_level' => [ 'required' ],
        ] );

        $attributeName = [
            'type_id' => __( 'item.type' ),
            'title' => __( 'item.title' ),
            'lyrics' => __( 'item.lyrics' ),
            'file' => __( 'item.file' ),
            'image' => __( 'item.image' ),
            'author' => __( 'item.author' ),
            'membership_level' => __( 'item.membership_level' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $createItem = Item::create( [
                'add_by' => auth()->user()->id,
                'type_id' => $request->type_id,
                'title' => $request->title,
                'lyrics' => $request->lyrics,
                'file' => $request->file,
                'image' => $request->image,
                'author' => $request->author,
                'membership_level' => $request->membership_level,
                'file_name' => $request->file_name,
                'file_type' => $request->file_type,
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.items' ) ) ] ),
        ] );
    }

    public static function updateItem( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'type_id' => [ 'required', 'exists:types,id' ],
            // 'category_id' => [ 'required' ],
            'title' => [ 'required' ],
            'lyrics' => [ 'nullable' ],
            'file' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'author' => [ 'nullable' ],
            'membership_level' => [ 'required' ],
        ] );

        $attributeName = [
            'type_id' => __( 'item.type' ),
            'category_id' => __( 'item.category' ),
            'title' => __( 'item.title' ),
            'lyrics' => __( 'item.lyrics' ),
            'file' => __( 'item.file' ),
            'image' => __( 'item.image' ),
            'author' => __( 'item.author' ),
            'membership_level' => __( 'item.membership_level' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateItem = Item::find( $request->id );
            $updateItem->file_name = $request->file_name;
            $updateItem->file_type = $request->file_type;
            $updateItem->type_id = $request->type_id;
            $updateItem->title = $request->title;
            $updateItem->lyrics = $request->lyrics;
            $updateItem->file = $request->file;
            $updateItem->image = $request->image;
            $updateItem->author = $request->author;
            $updateItem->membership_level = $request->membership_level;
            $updateItem->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.items' ) ) ] ),
        ] );
    }

    public static function updateItemStatus( $request ) {
        
        $request->merge( [
            'id' => \Helper::decode( $request->id ),
        ] );

        $updateItem = Item::find( $request->id );
        $updateItem->status = $request->status;
        $updateItem->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.items' ) ) ] ),
        ] );
    }

    public static function deleteItem( $request ) {
        
        $request->merge( [
            'id' => \Helper::decode( $request->id ),
        ] );

        $updateItem = Item::find( $request->id );
        
        $localPath = storage_path ('app/public/' . $updateItem->file );
        if ( !file_exists( $localPath ) ) {
            StorageService::delete( $updateItem->file );
        }
        
        $localPath = storage_path ('app/public/' . $updateItem->image );
        if ( !file_exists( $localPath ) ) {
            StorageService::delete( $updateItem->image );
        }
        $updateItem->delete();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.items' ) ) ] ),
        ] );
    }

    public static function getItems( $request ) {

        if ( !empty( $request->playlist_id ) ) {
            $request->merge( [
                'playlist_id' => \Helper::decode( $request->playlist_id )
            ] );
        }

        if ( !empty( $request->type_id ) ) {
            $request->merge( [
                'type_id' => \Helper::decode( $request->type_id )
            ] );
        }

        $items = Item::select( 'items.*' )
            ->when( !empty( $request->playlist_id ), function ( $q ) use ( $request ) {
                $q->whereHas( 'playlists', function ( $sub ) use ( $request ) {
                    $sub->where( 'playlists.id', $request->playlist_id );
                } );

                // join pivot 排序
                $q->join( 'playlist_items', 'items.id', '=', 'playlist_items.item_id' )
                    ->where( 'playlist_items.playlist_id', $request->playlist_id )
                    ->orderBy( 'playlist_items.priority', 'asc' );
            } )
            ->when( !empty( $request->type_id ), function( $q ) use ( $request ) {
                $q->where( 'items.type_id', $request->type_id );
            } )
            ->where( 'items.status', 10 );

        
        if( !auth()->check() || auth()->user()->membership == 0 ) {
            // for membership level filter
            $items->where( 'items.membership_level', 0 );
        }

        if ( empty( $request->playlist_id ) ) {
            $items->orderBy( 'items.created_at', 'desc' );
        }
                
        $items = $items->paginate( empty( $request->per_page ) ? 100 : $request->per_page );

        $items->getCollection()->transform( function ( $item ) {
            $item->append( [
                'encrypted_id',
                'image_url',
                'file_url',
            ] );
            return $item;
        } );

        return response()->json( $items );
    }

    public static function getItem( $request ) {

        $item = Item::find( \Helper::decode( $request->id ) );

        $item->append( [
            'encrypted_id',
            'image_url',
            'file_url',
        ] );
  
        return response()->json( $item );
    }

}