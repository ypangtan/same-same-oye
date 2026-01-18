<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\Item;
use App\Models\Playlist;
use App\Models\SearchItem;
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

        $searchItems = SearchItem::all();
        foreach( $searchItems as $searchItem ) {
            $searchItem->delete();
        }

        $playlists = Playlist::where( 'status', 10 )->get();
        foreach( $playlists as $playlist ) {
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

        $items = Item::where( 'status', 10 )->get();
        foreach( $items as $item ) {
            $item->searchItems()->create( [
                'keyword' => $item->title,
                'item_id' => $item->id,
            ] );

            $item->searchItems()->create( [
                'keyword' => $item->author,
                'item_id' => $item->id,
            ] );
        }

        return 0;
    }
}
