<?php

namespace App\Observers;

use App\Models\Playlist;

class PlaylistObserver
{
    public function created( Playlist $playlist ) {
        $playlist->searchPlaylists()->create( [
            'keyword' => $playlist->en_name,
            'playlist_id' => $playlist->id,
        ] );

        foreach( $playlist->items as $item ) {
            $playlist->searchPlaylists()->create( [
                'keyword' => $item->title,
                'playlist_id' => $playlist->id,
            ] );

            $playlist->searchPlaylists()->create( [
                'keyword' => $item->author,
                'playlist_id' => $playlist->id,
            ] );
        }
    }

    public function updated( Playlist $playlist ) {
        if( $playlist->isDirty( 'en_name' ) ) {
            $playlist->searchPlaylists()->updateOrCreate( [
                'playlist_id' => $playlist->id,
                'keyword' => $playlist->en_name,
            ], [
                'keyword' => $playlist->en_name,
            ] );
        }

         if( $playlist->isDirty( 'items' ) ) {
            $currentKeywords = [$playlist->en_name];
            
            foreach( $playlist->items as $item ) {
                $currentKeywords[] = $item->title;
                $currentKeywords[] = $item->author;
                
                $playlist->searchPlaylists()->updateOrCreate( [
                    'playlist_id' => $playlist->id,
                    'keyword' => $item->title,
                ], [
                    'keyword' => $item->title,
                ] );

                $playlist->searchPlaylists()->updateOrCreate( [
                    'playlist_id' => $playlist->id,
                    'keyword' => $item->author,
                ], [
                    'keyword' => $item->author,
                ] );
            }
            
            $playlist->searchPlaylists()
                ->where('playlist_id', $playlist->id)
                ->whereNotIn('keyword', $currentKeywords)
                ->delete();
        }
    }

    public function deleted( Playlist $playlist ) {
        $playlist->searchPlaylists()->where( 'playlist_id', $playlist->id )->delete();
    }
}
