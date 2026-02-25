<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\Item;
use App\Models\Playlist;
use App\Models\SearchItem;
use App\Services\StorageService;
use Illuminate\Console\Command;

class intiItemDuration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:intiItemDuration';

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

        $items = Item::whereNull( 'duration' )->get();
        foreach( $items as $item ) {
            try {
                $path = StorageService::get( $item->file );
                $ffprobe = \FFMpeg\FFProbe::create();
                $duration = (int) round(
                    $ffprobe->format( $path )->get('duration')
                );
                $item->update(['duration' => $duration]);
            } catch (\Exception $e) {
                \Log::error('Failed to get duration for item: ' . $item->id . ' - ' . $e->getMessage());
            }
        }

        return 0;
    }
}
