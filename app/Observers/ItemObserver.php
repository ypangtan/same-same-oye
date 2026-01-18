<?php

namespace App\Observers;

use App\Models\Item;

class ItemObserver
{
    public function created(Item $item) {
        $item->searchItems()->create( [
            'keyword' => $item->title,
            'item_id' => $item->id,
        ] );
        $item->searchItems()->create( [
            'keyword' => $item->author,
            'item_id' => $item->id,
        ] );
    }

    public function updated(Item $item) {

        if( $item->isDirty( 'title' ) ) {
            $item->searchItems()->updateOrCreate( [
                'item_id' => $item->id,
                'keyword' => $item->title,
            ], [
                'keyword' => $item->title,
                'item_id' => $item->id,
            ] );
        }

        if( $item->isDirty( 'author' ) ) {
            $item->searchItems()->updateOrCreate( [
                'item_id' => $item->id,
                'keyword' => $item->author,
            ], [
                'keyword' => $item->author,
                'item_id' => $item->id,
            ] );
        }
    }

    public function deleted(Item $item) {
        $item->searchItems()->where( 'item_id', $item->id )->delete();
    }
}