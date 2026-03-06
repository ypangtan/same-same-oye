<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class addPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

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
        $user_id = 15;
        $plan_id = 1;

        try{

            DB::beginTransaction();

            $createuserSubscription = UserSubscription::create([
                'user_id' => $user_id,
                'subscription_plan_id' => $plan_id,
                'status' => 10,
                'start_date' => Carbon::now(),
                'end_date' => '2026-04-01',
                'cancelled_at' => null,
                'type' => '1',
            ]);

            DB::commit();
            $this->info('Success: ');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->info('Error: ' . $e->getMessage());
        }

        return 0;
    }
}
