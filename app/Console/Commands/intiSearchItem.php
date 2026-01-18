<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\Item;
use App\Models\Playlist;
use Illuminate\Console\Command;

class intiSearchItem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:intiSearchItem';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $collections = Collection::where( 'status', 10 )->get();
        foreach( $collections as $collection ) {
            $collection->searchCollection()->create( [
                'keyword' => $collection->en_name,
                'collection_id' => $collection->id,
            ] );

            foreach( $collection->playlists as $playlist ) {
                $collection->searchCollection()->create( [
                    'keyword' => $playlist->en_name,
                    'collection_id' => $collection->id,
                ] );

                foreach( $playlist->items as $item ) {
                    $collection->searchCollection()->create( [
                        'keyword' => $item->title,
                        'collection_id' => $collection->id,
                    ] );

                    $collection->searchCollection()->create( [
                        'keyword' => $item->author,
                        'collection_id' => $collection->id,
                    ] );
                }
            }
        }

        $playlists = Playlist::where( 'status', 10 )->get();
        foreach( $playlists as $playlist ) {
            $playlist->searchPlaylist()->create( [
                'keyword' => $playlist->en_name,
                'playlist_id' => $playlist->id,
            ] );

            foreach( $playlist->items as $item ) {
                $playlist->searchPlaylist()->create( [
                    'keyword' => $item->title,
                    'playlist_id' => $playlist->id,
                ] );

                $playlist->searchPlaylist()->create( [
                    'keyword' => $item->author,
                    'playlist_id' => $playlist->id,
                ] );
            }
        }

        $items = Item::where( 'status', 10 )->get();
        foreach( $items as $item ) {
            $item->searchItem()->create( [
                'keyword' => $item->title,
                'item_id' => $item->id,
            ] );

            $item->searchItem()->create( [
                'keyword' => $item->author,
                'item_id' => $item->id,
            ] );
        }

        return 0;
    }
}
