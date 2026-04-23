<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

class updateTiralDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updateTiralDate {--dry-run}';

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
        $dryrun = $this->option('dry-run') ?? false;
        
        $expiredSubscriptions = UserSubscription::where( 'type', 2 )
            ->get();

        foreach( $expiredSubscriptions as $subscription ) {
            $endDate = Carbon::parse( $subscription->end_date )->subDay();

            $this->info( 'Tiral ' . $subscription->id . ' end date at' . $subscription->end_date . ' should expiry date at ' . $endDate  );

            if( !$dryrun ) {
                $subscription->end_date = $endDate;
                $subscription->save();
                $this->info( 'Tiral ' . $subscription->id . ' updated'  );
            }
        }

        return 0;
    }
}
