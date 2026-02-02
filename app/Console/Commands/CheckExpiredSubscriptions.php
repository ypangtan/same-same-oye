<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update expired subscriptions';

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
    public function handle() {

        // 查找所有已过期但状态还是 active 的订阅
        $expiredSubscriptions = UserSubscription::where( 'status', 10 )
            ->whereDate( 'end_date', '<', now() )
            ->get();

        $count = 0;

        foreach ($expiredSubscriptions as $subscription) {
            try {
                $subscription->markAsExpired();
                $count++;

                Log::channel('payment')->info('Subscription marked as expired', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'end_date' => $subscription->end_date,
                ]);

            } catch (\Exception $e) {
                Log::channel('payment')->error('Failed to mark subscription as expired', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return 0;
    }
}
