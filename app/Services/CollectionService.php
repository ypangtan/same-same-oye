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
    Collection,
    User,
    Role as RoleModel
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class CollectionService
{
    public static function allCollections( $request ) {

        $collection = Collection::with( [
            'type',
            'administrator',
            'playlists',
        ] )->select( 'collections.*' );

        $filterObject = self::filter( $request, $collection );
        $collection = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            switch ( $request->input( 'order.0.column' ) ) {
                default:
                    $collection->orderBy( 'priority', 'desc' );
                    break;
            }
        }

        $collectionCount = $collection->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $collections = $collection->skip( $offset )->take( $limit )->get();

        if ( $collections ) {
            $collections->append( [
                'encrypted_id',
                'name',
                'image_url',
            ] );
        }

        if( !empty( $request->type ) ) {
            $totalRecord = Collection::where( 'type_id', $request->type )->count();
        } else {
            $totalRecord = Collection::count();
        }

        $data = [
            'collections' => $collections,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $collectionCount : $totalRecord,
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

                $model->whereBetween( 'collections.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_at );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'collections.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
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

        if( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
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

    public static function oneCollection( $request ) {

        $collection = Collection::with( [
            'type',
            'administrator',
            'playlists',
        ] )->find( Helper::decode( $request->id ) );

        $collection->append( [
            'encrypted_id',
            'name',
            'image_url',
        ] );

        foreach( $collection->playlists as $p ) {
            $p->append( [
                'encrypted_id',
                'name',
            ] );
        }

        return response()->json( $collection );
    }

    public static function createCollection( $request ) {

        $validator = Validator::make( $request->all(), [
            'type_id' => [ 'required', 'exists:types,id' ],
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'membership_level' => [ 'nullable' ],
            'display_type' => [ 'required', 'in:1,2,3,4,5,6,7' ],
            'playlists' => [ 'required' ],
        ] );

        $attributeName = [
            'type_id' => __( 'collection.type' ),
            'en_name' => __( 'collection.name' ),
            'zh_name' => __( 'collection.name' ),
            'image' => __( 'collection.image' ),
            'membership_level' => __( 'collection.membership_level' ),
            'display_type' => __( 'collection.display_type' ),
            'playlists' => __( 'collection.playlists' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createCollection = Collection::create( [
                'add_by' => auth()->user()->id,
                'type_id' => $request->type_id,
                'en_name' => $request->en_name,
                'zh_name' => $request->zh_name,
                'image' => $request->image,
                'membership_level' => $request->membership_level,
                'display_type' => $request->display_type,
                'priority' => Collection::max( 'priority' ) + 1,
                'status' => 10,
            ] );

            $playlists = json_decode( $request->playlists, true );
            $syncData = [];
            foreach ( $playlists as $index => $item ) {
                $syncData[$item['id']] = ['priority' => $index + 1];
            }

            $createCollection->playlists()->sync( $syncData );
    
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.collections' ) ) ] ),
        ] );
    }

    public static function updateCollection( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'type_id' => [ 'required', 'exists:types,id' ],
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'membership_level' => [ 'nullable' ],
            'display_type' => [ 'required', 'in:1,2,3,4,5,6,7' ],
            'playlists' => [ 'required' ],
        ] );

        $attributeName = [
            'type_id' => __( 'collection.type' ),
            'en_name' => __( 'collection.name' ),
            'zh_name' => __( 'collection.name' ),
            'image' => __( 'collection.image' ),
            'priority' => __( 'collection.priority' ),
            'membership_level' => __( 'collection.membership_level' ),
            'display_type' => __( 'collection.display_type' ),
            'playlists' => __( 'collection.playlists' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateCollection = Collection::find( $request->id );
            $updateCollection->type_id = $request->type_id;
            $updateCollection->en_name = $request->en_name;
            $updateCollection->zh_name = $request->zh_name;
            $updateCollection->image = $request->image;
            $updateCollection->membership_level = $request->membership_level;
            $updateCollection->display_type = $request->display_type;
            $updateCollection->save();

            $playlists = json_decode( $request->playlists, true );
            $syncData = [];
            foreach ( $playlists as $index => $item ) {
                $syncData[$item['id']] = ['priority' => $index + 1];
            }

            $updateCollection->playlists()->sync( $syncData );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.collections' ) ) ] ),
        ] );
    }

    public static function updateCollectionStatus( $request ) {
        
        $request->merge( [
            'id' => \Helper::decode( $request->id ),
        ] );

        $updateCollection = Collection::find( $request->id );
        $updateCollection->status = $request->status;
        $updateCollection->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.collections' ) ) ] ),
        ] );
    }

    public static function deleteCollection( $request ) {
        
        $request->merge( [
            'id' => \Helper::decode( $request->id ),
        ] );

        $updateCollection = Collection::find( $request->id );
        
        $localPath = storage_path ('app/public/' . $updateCollection->image );
        if ( !file_exists( $localPath ) ) {
            StorageService::delete( $updateCollection->image );
        }
        $updateCollection->delete();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.collections' ) ) ] ),
        ] );
    }

    public static function updateOrder( $request ) {

        $updates = $request->input( 'updates' );

        foreach( $updates as $update ) {
            $ad = Collection::find( \Helper::decode( $update['id'] ) );
            if( $ad ) {
                $ad->priority = $update['position'];
                $ad->save();
            }
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.collections' ) ) ] ),
        ] );
    }

    public static function getCollections( $request ) {
        
        if ( !empty( $request->type_id ) ) {
            $request->merge( [
                'type_id' => \Helper::decode( $request->type_id )
            ] );
        }

        $collections = Collection::with( [
            'playlists' => function ( $q ) {
                $q->limit( 10 );
            },
            // 'playlists.item',
            // 'playlists.items',
        ] )->select( 'collections.*' )
            ->when( !empty( $request->type_id ), function ( $q ) use ( $request ) {
                $q->where( 'type_id', $request->type_id );
            } )
            ->where( 'status', 10 );
            
        if( !auth()->check() || auth()->user()->membership == 0 ) {
            // for membership level filter
            $collections->where( 'collections.membership_level', 0 );
        }

        $collections->orderBy( 'priority', 'desc' );

        $collections = $collections->paginate( empty( $request->per_page ) ? 100 : $request->per_page );

        $collections->getCollection()->transform(function ($collection) {
            $collection->append( [
                'name',
                'image_url',
                'encrypted_id',
            ] );

            if ( $collection->relationLoaded('playlists') && $collection->playlists ) {
                $collection->playlists->transform(function ($playlist) {
                    $playlist->append( [
                        'encrypted_id',
                        'name',
                        'image_url',
                    ] );

                    // if ( $playlist->relationLoaded('item') && $playlist->item ) {
                    //     $playlist->item->append( [
                    //         'encrypted_id',
                    //         'image_url',
                    //         'file_url',
                    //     ] );
                    // }

                    // if ( $playlist->relationLoaded('items')  && $playlist->items ) {
                    //     $playlist->items->transform(function ($item) {
                    //         $item->append( [
                    //             'encrypted_id',
                    //             'image_url',
                    //             'file_url',
                    //         ] );
                    //         return $item;
                    //     });
                    // }

                    return $playlist;
                });
            }

            return $collection;
        });

        return response()->json( $collections );
    }

    public static function getCollection( $request ) {
        $collection = Collection::with( [
            'playlists' => function ( $q ) {
                $q->limit( 10 );
            },
            // 'playlists.item',
            // 'playlists.items',
        ] )->find( \Helper::decode( $request->id ) );

        $collection->append( [
            'name',
            'image_url',
            'encrypted_id',
        ] );

        if ( $collection->playlists ) {
            foreach( $collection->playlists as $playlist ) {
                $playlist->append( [ 
                    'encrypted_id',
                    'name',
                    'image_url',
                ] );

                // if( $playlist->item ) {
                //     $playlist->item->append( [
                //         'encrypted_id',
                //         'image_url',
                //         'file_url',
                //     ] );    
                // }
                // if( $playlist->items ) {
                //     foreach ( $playlist->items as $item ) {
                //         $item->append( [
                //             'encrypted_id',
                //             'image_url',
                //             'file_url',
                //         ] );
                //     }
                // }

            }
        }

        return response()->json( [
            'data' => $collection
        ] );
    }
}