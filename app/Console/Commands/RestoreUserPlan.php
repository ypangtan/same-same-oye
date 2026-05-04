<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Services\UserSubscriptionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RestoreUserPlan extends Command
{
    protected $signature = 'command:restoreUserPlan';

    protected $description = 'Restore cancelled plan for user id=32, plan id=51 (user-cancelled)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $planId = 51;

        try {
            DB::beginTransaction();

            $userSubscription = UserSubscription::find( $planId );

            $userSubscription->update([
                'status' => 10,
            ]);

            DB::commit();

            UserSubscriptionService::checkUserPlan($userSubscription);

            $this->info("Restored user_subscription id={$userSubscription->id} for user_id={$userSubscription->user_id}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
        }

        return 0;
    }
}
