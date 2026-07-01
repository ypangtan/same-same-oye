<?php

namespace App\Services;

use App\Models\{
    StreamLog,
    BannerClick,
    PopAnnouncementClick
};

class StreamService {

    public static function recordStream( $request ) {
        
        if( !empty( $request->item_id ) ) {
            $request->merge( [ 
                'item_id' => \Helper::decode( $request->item_id )
            ] );
        }
        
        if( !empty( $request->playlist_id ) ) {
            $request->merge( [ 
                'playlist_id' => \Helper::decode( $request->playlist_id )
            ] );
        }
        
        if( !empty( $request->collection_id ) ) {
            $request->merge( [ 
                'collection_id' => \Helper::decode( $request->collection_id )
            ] );
        }
        
        if( !empty( $request->banner_id ) ) {
            $request->merge( [ 
                'banner_id' => \Helper::decode( $request->banner_id )
            ] );
        }
        
        if( !empty( $request->pop_announcement_id ) ) {
            $request->merge( [ 
                'pop_announcement_id' => \Helper::decode( $request->pop_announcement_id )
            ] );
        }
    
        $request->validate( [
            'content_type' => [ 'required', 'in:1,2,3,4,5,6' ],
            'radio_name'    => [ 'nullable' ],
            'item_id'     => [ 'required_if:content_type,2', 'exists:items,id' ],
            'playlist_id' => [ 'required_if:content_type,3', 'exists:playlists,id' ],
            'collection_id' => [ 'required_if:content_type,4', 'exists:collections,id' ],
            'banner_id' => [ 'required_if:content_type,5', 'exists:banners,id' ],
            'pop_announcement_id' => [ 'required_if:content_type,6', 'exists:pop_announcements,id' ],
        ] );

        switch( $request->content_type ) {
            case 1:
            case 2:
            case 3:
            case 4:
                StreamLog::create( [
                    'user_id'      => auth()->id() ?? null,
                    'content_type' => $request->content_type,
                    'radio_name'   => $request->radio_name ?? null,
                    'item_id'    => $request->item_id ?? null,
                    'playlist_id' => $request->playlist_id ?? null,
                    'collection_id' => $request->collection_id ?? null,
                    'banner_id' => $request->banner_id ?? null,
                    'pop_announcement_id' => $request->pop_announcement_id ?? null,
                ] );

                if( $request->content_type == 2 ) {
                    $playlistIds = \DB::table( 'playlist_items' )
                        ->where( 'item_id', $request->item_id )
                        ->pluck( 'playlist_id' );
                    foreach( $playlistIds as $playlistId ) {
                        StreamLog::create( [
                            'user_id'      => auth()->id() ?? null,
                            'content_type' => 3,
                            'playlist_id' => $playlistId,
                        ] );

                        $collectionIds = \DB::table( 'collection_playlists' )
                            ->where( 'playlist_id', $playlistId )
                            ->pluck( 'collection_id' );
                        foreach( $collectionIds as $collectionId ) {
                            StreamLog::create( [
                                'user_id'      => auth()->id() ?? null,
                                'content_type' => 4,
                                'collection_id' => $collectionId,
                            ] );
                        }
                    }
                }

                if( $request->content_type == 3 ) {
                    $collectionIds = \DB::table( 'collection_playlists' )
                        ->where( 'playlist_id', $request->playlist_id )
                        ->pluck( 'collection_id' );
                    foreach( $collectionIds as $collectionId ) {
                        StreamLog::create( [
                            'user_id'      => auth()->id() ?? null,
                            'content_type' => 4,
                            'collection_id' => $collectionId,
                        ] );
                    }
                }
                break;
            case 5:
                BannerClick::create( [
                    'user_id'      => auth()->id() ?? null,
                    'banner_id' => $request->banner_id,
                ] );
                break;
            case 6:
                PopAnnouncementClick::create( [
                    'user_id'      => auth()->id() ?? null,
                    'pop_announcement_id' => $request->pop_announcement_id,
                ] );
                break;
        }

        return response()->json( [ 'message' => 'Stream recorded.' ] );
    }
}
