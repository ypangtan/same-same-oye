<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Cache,
    Validator,
    Log
};

use App\Models\{
    Item,
    Playlist,
    Collection,
    SearchItem,
    SearchLog
};

use Helper;
use Carbon\Carbon;

class SearchService {
    
    public static function search( $request ) {

        if( !empty( $request->category_id ) ) {
            $request->merge( [
                'category_id' => Helper::decode( $request->category_id )
            ] );
        }

        if( !empty( $request->type_id ) ) {
            $request->merge( [
                'type_id' => Helper::decode( $request->type_id )
            ] );
        }

        $per_page = $request->input( 'per_page', 10 );
        $text = $request->text ?? '';

        $search = SearchItem::with( [
            'item',
            'playlist',
        ] )->where( 'keyword', 'like', '%' . $text . '%' );

        $search = $search->where( function ( $query ) use ( $request ) {
            $query->when( !empty( $request->category_id ), function ( $q ) use ( $request ) {
                $q->where( function ( $sq ) use ( $request ) {
                    $sq->whereHas( 'item', function ( $ssq ) use ( $request ) {
                        $ssq->where( 'category_id', $request->input( 'category_id' ) );
                    } )->orWhereHas( 'playlist', function ( $ssq ) use ( $request ) {
                        $ssq->where( 'category_id', $request->input( 'category_id' ) );
                    } );
                } );
            } );
            $query->when( !empty( $request->type_id ), function ( $q ) use ( $request ) {
                $q->where( function ( $sq ) use ( $request ) {
                    $sq->whereHas( 'item', function ( $ssq ) use ( $request ) {
                        $ssq->where( 'type_id', $request->input( 'type_id' ) );
                    } )->orWhereHas( 'playlist', function ( $ssq ) use ( $request ) {
                        $ssq->where( 'type_id', $request->input( 'type_id' ) );
                    } );
                } );
            } );
        } );

        $search = $search->paginate( $per_page );

        $search->getCollection()->transform(function ($value) {

            if ($value->relationLoaded('item')) {
                $value->item->append( [
                    'encrypted_id',
                    'image_url',
                    'song_url',
                ] );
            }

            // if ($value->relationLoaded('playlist')) {
            //     $value->playlist->append( [
            //         'encrypted_id',
            //         // 'image_url',
            //     ] );
            // }


            return $value;
        });

        return $search;
    }
}