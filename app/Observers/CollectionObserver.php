<?php

namespace App\Observers;

use App\Models\Collection;

class CollectionObserver
{
    public function created( Collection $collection ) {
        $collection->searchCollections()->create( [
            'keyword' => $collection->en_name,
            'collection_id' => $collection->id,
        ] );

        foreach( $collection->playlists as $playlist ) {
            $collection->searchCollections()->create( [
                'keyword' => $playlist->en_name,
                'collection_id' => $collection->id,
            ] );

            foreach( $playlist->items as $item ) {
                $collection->searchCollections()->create( [
                    'keyword' => $item->title,
                    'collection_id' => $collection->id,
                ] );

                $collection->searchCollections()->create( [
                    'keyword' => $item->author,
                    'collection_id' => $collection->id,
                ] );
            }
        }
    }

    public function updated( Collection $collection ) {
        if( $collection->isDirty( 'en_name' ) ) {
            $collection->searchCollections()->updateOrCreate( [
                'collection_id' => $collection->id,
                'keyword' => $collection->en_name,
            ], [
                'keyword' => $collection->en_name,
            ] );
        }

        if( $collection->isDirty( 'playlists' ) ) {
            $currentKeywords = [$collection->en_name];

            foreach( $collection->playlists as $playlist ) {
                $currentKeywords[] = $playlist->en_name;

                $collection->searchCollections()->updateOrCreate( [
                    'collection_id' => $collection->id,
                    'keyword' => $playlist->en_name,
                ], [
                    'keyword' => $playlist->en_name,
                ] );

                foreach( $playlist->items as $item ) {
                    $currentKeywords[] = $item->title;
                    $currentKeywords[] = $item->author;

                    $collection->searchCollections()->updateOrCreate( [
                        'collection_id' => $collection->id,
                        'keyword' => $item->title,
                    ], [
                        'keyword' => $item->title,
                    ] );

                    $collection->searchCollections()->updateOrCreate( [
                        'collection_id' => $collection->id,
                        'keyword' => $item->author,
                    ], [
                        'keyword' => $item->author,
                    ] );
                }
            }

            $collection->searchCollections()
                ->where('collection_id', $collection->id)
                ->whereNotIn('keyword', $currentKeywords)
                ->delete();
        }
    }

    public function deleted( Collection $collection ) {
        $collection->searchCollections()->where( 'collection_id', $collection->id )->delete();
    }
}
