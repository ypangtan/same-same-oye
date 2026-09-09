<?php

namespace App\Console\Commands;

use App\Models\RadioListenerSnapshot;
use App\Services\IcecastService;
use Illuminate\Console\Command;

class SnapshotRadioListeners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'radio:snapshot-listeners';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record the current Icecast listener count, for the backoffice radio listener graph';

    public function handle() {

        $status = IcecastService::getStatus();

        if ( !$status['online'] ) {
            // Icecast unreachable (down, or the engine hasn't been deployed yet) — skip rather
            // than writing a misleading "0 listeners" point into the graph.
            $this->info( 'Icecast not reachable, skipping this snapshot.' );
            return 0;
        }

        RadioListenerSnapshot::create( [
            'listeners' => $status['listeners'],
        ] );

        $this->info( "Recorded {$status['listeners']} listener(s)." );

        return 0;
    }
}
