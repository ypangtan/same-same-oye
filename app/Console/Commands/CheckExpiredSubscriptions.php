<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Services\UserService;
use App\Services\UserSubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expired {--dry-run : Run the command without making any changes}';

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

        $dryrun = $this->option('dry-run') ?? false;

        // 查找所有已过期但状态还是 active 的订阅
        $expiredSubscriptions = UserSubscription::where( 'status', 10 )
            ->whereDate( 'end_date', '<', now() )
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            try {
                if( $dryrun ) {
                    $this->info( "Processing subscription ID: {$subscription->id}, User ID: {$subscription->user_id}" );
                } else {
                    $subscription->status = 20;
                    $subscription->save();

                    UserSubscriptionService::checkUserPlan( $subscription );

                    if ( $subscription->type == 2 ) {
                        // trial need send notification
                        UserService::createUserNotification(
                            $subscription->user_id,
                            'notification.trial_end_title',
                            'notification.trial_end_content',
                            'trial_end',
                            'subscription'
                        );
                    }

                    Log::channel('cronjob')->info('Subscription marked as expired', [
                        'subscription_id' => $subscription->id,
                        'user_id' => $subscription->user_id,
                        'end_date' => $subscription->end_date,
                    ]);
                }

            } catch (\Exception $e) {
                Log::channel('cronjob')->error('Failed to mark subscription as expired', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        $this->info( 'Expired subscriptions check completed.' );

        return 0;
    }
}
