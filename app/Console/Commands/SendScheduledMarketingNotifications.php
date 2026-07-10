<?php

namespace App\Console\Commands;

use App\Models\UserNotification;
use App\Services\MarketingNotificationService;
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

        $this->info( 'Scheduled marketing notifications check completed.' );

        return 0;
    }
}
