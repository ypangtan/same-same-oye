<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CountdownTrial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:count-down-trial';

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

        $now = Carbon::now()->timezone( 'Asia/Kuala_Lumpur' );
        $day1 = $now->copy()->subDay();
        $day3 = $now->copy()->subDays(3);

        $notifyUserSubscription = UserSubscription::where( 'type', 2 )
            ->where( 'status', 10 )
            ->whereDate( 'end_date', $day3 )
            ->get();

        foreach( $notifyUserSubscription as $userSubscription ) {
            try {
                UserService::createUserNotification(
                    $userSubscription->user_id,
                    'notification.notify_trial_end_title',
                    'notification.notify_trial_3_end_content',
                    'trial_end',
                    'subscription'
                );
            } catch ( \Throwable $th ) {
                
                Log::channel('payment')->info('Trial Subscription notify 3 day fail', [
                    'subscription_id' => $userSubscription->id,
                    'user_id' => $userSubscription->user_id,
                    'end_date' => $userSubscription->end_date,
                ]);
            }
        }

        $notifyUserSubscription = UserSubscription::where( 'type', 2 )
            ->where( 'status', 10 )
            ->whereDate( 'end_date', $day1 )
            ->get();

        foreach( $notifyUserSubscription as $userSubscription ) {
            try {
                UserService::createUserNotification(
                    $userSubscription->user_id,
                    'notification.notify_trial_end_title',
                    'notification.notify_trial_1_end_content',
                    'trial_end',
                    'subscription'
                );
            } catch ( \Throwable $th ) {
                
                Log::channel('payment')->info('Trial Subscription notify 1 day fail', [
                    'subscription_id' => $userSubscription->id,
                    'user_id' => $userSubscription->user_id,
                    'end_date' => $userSubscription->end_date,
                ]);
            }
        }
    
        return 0;
    }
}
