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
            'category',
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

        $totalRecord = Item::count();

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

        if ( !empty( $request->category ) ) {
            $category = \Helper::decode( $request->category );
            $model->where( 'category_id', $category );
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

    public static function oneItem( $request ) {

        $item = Item::with( [
            'category',
            'administrator',
            'playLists',
        ] )->find( Helper::decode( $request->id ) );

        $item->append( [
            'image_url',
        ] );

        return response()->json( $item );
    }

    public static function createItem( $request ) {

        $validator = Validator::make( $request->all(), [
            'category_id' => [ 'required', 'exists:categories,id' ],
            'title' => [ 'required' ],
            'lyrics' => [ 'nullable' ],
            'file' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'author' => [ 'nullable' ],
            'membership_level' => [ 'required' ],
        ] );

        $attributeName = [
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

            $createItem = Item::create( [
                'add_by' => auth()->user()->id,
                'category_id' => $request->category_id,
                'title' => $request->title,
                'lyrics' => $request->lyrics,
                'file' => $request->file,
                'image' => $request->image,
                'author' => $request->author,
                'membership_level' => $request->membership_level,
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
            'category_id' => [ 'required', 'exists:categories,id' ],
            'title' => [ 'required' ],
            'lyrics' => [ 'nullable' ],
            'file' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'author' => [ 'nullable' ],
            'membership_level' => [ 'required' ],
        ] );

        $attributeName = [
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
            $updateItem->category_id = $request->category_id;
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

}