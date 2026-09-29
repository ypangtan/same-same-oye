<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};
use Illuminate\Validation\Rule;

use App\Models\{
    Collection,
    HomeCollection,
};

use Helper;

use Carbon\Carbon;

class HomeCollectionService
{
    const DISPLAY_TYPES = [ 1, 2, 3, 4, 5, 6, 7, 8, 9 ];

    public static function allHomeCollections( $request ) {

        $homeCollection = HomeCollection::with( [
            'collection.type',
        ] )->select( 'home_collections.*' )
            ->whereHas( 'collection' );

        $filterObject = self::filter( $request, $homeCollection );
        $homeCollection = $filterObject['model'];
        $filter = $filterObject['filter'];

        // Priority is the home order, so it is the only sort that makes sense here
        $homeCollection->orderBy( 'priority', 'asc' );

        $homeCollectionCount = $homeCollection->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $homeCollections = $homeCollection->skip( $offset )->take( $limit )->get();

        $homeCollections = $homeCollections->map( function ( $homeCollection ) {
            $collection = $homeCollection->collection;
            return [
                'encrypted_id' => $homeCollection->encrypted_id,
                'collection_id' => $collection->id,
                'priority' => $homeCollection->priority,
                'display_type' => $homeCollection->display_type ?? $collection->display_type,
                'name' => $collection->name,
                'type' => $collection->type ? $collection->type->name : null,
            ];
        } );

        $totalRecord = HomeCollection::whereHas( 'collection' )->count();

        $data = [
            'home_collections' => $homeCollections,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $homeCollectionCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->whereHas( 'collection', function ( $q ) use ( $request ) {
                $q->where( 'en_name', 'LIKE', '%' . $request->title . '%' )
                    ->orWhere( 'zh_name', 'LIKE', '%' . $request->title . '%' );
            } );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function addHomeCollection( $request ) {

        $validator = Validator::make( $request->all(), [
            'collection' => [ 'required', 'exists:collections,id', 'unique:home_collections,collection_id' ],
            'display_type' => [ 'required', 'in:' . implode( ',', self::DISPLAY_TYPES ), function ( $attribute, $value, $fail ) use ( $request ) {
                if ( $value == 8 && self::hasNonVideoPlaylist( $request->collection ) ) {
                    $fail( __( 'collection.type_8_playlists_must_be_video' ) );
                }
            } ],
        ] );

        $attributeName = [
            'collection' => Str::singular( __( 'template.collections' ) ),
            'display_type' => __( 'collection.display_type' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            HomeCollection::create( [
                'collection_id' => $request->collection,
                'display_type' => $request->display_type,
                'priority' => ( HomeCollection::max( 'priority' ) ?? 0 ) + 1,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => __( 'template.home' ) ] ),
        ] );
    }

    public static function updateHomeCollection( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'id' => [ 'required', 'exists:home_collections,id' ],
            'collection' => [ 'required', 'exists:collections,id', Rule::unique( 'home_collections', 'collection_id' )->ignore( $request->id ) ],
            'display_type' => [ 'required', 'in:' . implode( ',', self::DISPLAY_TYPES ), function ( $attribute, $value, $fail ) use ( $request ) {
                if ( $value == 8 && self::hasNonVideoPlaylist( $request->collection ) ) {
                    $fail( __( 'collection.type_8_playlists_must_be_video' ) );
                }
            } ],
        ] );

        $attributeName = [
            'collection' => Str::singular( __( 'template.collections' ) ),
            'display_type' => __( 'collection.display_type' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $homeCollection = HomeCollection::find( $request->id );
        $homeCollection->collection_id = $request->collection;
        $homeCollection->display_type = $request->display_type;
        $homeCollection->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => __( 'template.home' ) ] ),
        ] );
    }

    // Same rule as the collection form: display type 8 only accepts video playlists
    private static function hasNonVideoPlaylist( $collectionId ) {
        return Collection::where( 'id', $collectionId )
            ->whereHas( 'playlists', function ( $q ) {
                $q->where( function ( $sq ) {
                    $sq->where( 'playlists.file_type', '!=', 2 )
                        ->orWhereHas( 'items', function ( $iq ) {
                            $iq->where( 'items.upload_type', '!=', 1 );
                        } );
                } );
            } )
            ->exists();
    }

    public static function deleteHomeCollection( $request ) {

        $homeCollection = HomeCollection::find( Helper::decode( $request->id ) );

        if ( $homeCollection ) {
            $homeCollection->delete();
            self::resequence();
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => __( 'template.home' ) ] ),
        ] );
    }

    public static function updateOrder( $request ) {

        $updates = $request->input( 'updates' ) ?? [];

        foreach ( $updates as $update ) {
            $homeCollection = HomeCollection::find( Helper::decode( $update['id'] ) );
            if ( $homeCollection ) {
                $homeCollection->priority = $update['position'];
                $homeCollection->save();
            }
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => __( 'template.home' ) ] ),
        ] );
    }

    // Keep priorities as 1..n so the first row is always priority 1 (the recommended slot)
    private static function resequence() {

        $homeCollections = HomeCollection::orderBy( 'priority', 'asc' )->orderBy( 'id', 'asc' )->get();

        foreach ( $homeCollections as $index => $homeCollection ) {
            if ( $homeCollection->priority != $index + 1 ) {
                $homeCollection->priority = $index + 1;
                $homeCollection->save();
            }
        }
    }

    public static function getHomeCollections( $request ) {

        $now = Carbon::now()->timezone( 'Asia/Kuala_Lumpur' );

        $published = function ( $q ) use ( $now ) {
            $q->whereNull( 'publishing_date' )->orWhereDate( 'publishing_date', '<=', $now );
        };

        $collections = Collection::with( [
            'playlists' => function ( $q ) use ( $published ) {
                $q->where( $published );
            },
            'playlists.tags',
        ] )->select( 'collections.*', 'home_collections.display_type as home_display_type' )
            ->join( 'home_collections', 'home_collections.collection_id', '=', 'collections.id' )
            ->whereHas( 'playlists' )
            ->where( 'collections.status', 10 )
            ->where( function ( $q ) use ( $now ) {
                $q->whereNull( 'collections.publishing_date' )->orWhereDate( 'collections.publishing_date', '<=', $now );
            } )
            ->orderBy( 'home_collections.priority', 'asc' )
            ->get();

        $collections->transform( function ( $collection ) {
            // Home can override the collection's own display type
            $collection->display_type = $collection->home_display_type ?? $collection->display_type;
            unset( $collection->home_display_type );

            $collection->append( [
                'name',
                'image_url',
                'encrypted_id',
            ] );

            $collection->playlists->transform( function ( $playlist ) {
                $playlist->append( [
                    'encrypted_id',
                    'name',
                    'image_url',
                    'display_tag',
                    'total_likes',
                ] );
                return $playlist;
            } );

            return $collection;
        } );

        return response()->json( $collections );
    }
}
