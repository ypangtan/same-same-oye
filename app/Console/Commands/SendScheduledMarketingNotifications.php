<?php

namespace App\Console\Commands;

use App\Models\UserNotification;
use App\Services\MarketingNotificationService;
use App\Services\SubscriptionVoucherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendScheduledMarketingNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push marketing notifications whose scheduled publishing_date has arrived';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {

        $dueAnnouncements = UserNotification::where( 'status', 1 )
            ->whereNotNull( 'system_title' )
            ->whereNotNull( 'publishing_date' )
            ->where( 'publishing_date', '<=', Carbon::now( 'Asia/Kuala_Lumpur' ) )
            ->get();

        foreach ( $dueAnnouncements as $announcement ) {
            try {
                MarketingNotificationService::dispatchScheduledAnnouncement( $announcement );

                Log::channel( 'cronjob' )->info( 'Scheduled marketing notification sent', [
                    'user_notification_id' => $announcement->id,
                    'publishing_date' => $announcement->publishing_date,
                ] );
            } catch ( \Exception $e ) {
                Log::channel( 'cronjob' )->error( 'Failed to send scheduled marketing notification', [
                    'user_notification_id' => $announcement->id,
                    'error' => $e->getMessage(),
                ] );
            }
        }

        // Subscription voucher notifications ( system_title is null, so not included above )
        $dueVoucherNotifications = UserNotification::where( 'status', 1 )
            ->whereNotNull( 'subscription_voucher_id' )
            ->whereNotNull( 'publishing_date' )
            ->where( 'publishing_date', '<=', Carbon::now( 'Asia/Kuala_Lumpur' ) )
            ->get();

        foreach ( $dueVoucherNotifications as $notification ) {
            try {
                SubscriptionVoucherService::dispatchScheduledVoucherNotification( $notification );

                Log::channel( 'cronjob' )->info( 'Scheduled voucher notification sent', [
                    'user_notification_id' => $notification->id,
                    'publishing_date' => $notification->publishing_date,
                ] );
            } catch ( \Exception $e ) {
                Log::channel( 'cronjob' )->error( 'Failed to send scheduled voucher notification', [
                    'user_notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ] );
            }
        }

        $this->info( 'Scheduled marketing notifications check completed.' );

        return 0;
    }
}
