<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\Item;
use App\Models\Playlist;
use App\Models\SearchItem;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Console\Command;

class checkPlanValidity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkPlanValidity';

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

        $user = User::find( 5 );
        if( $user ) {
            $user->checkPlanValidity();
        }

        return 0;
    }
}
