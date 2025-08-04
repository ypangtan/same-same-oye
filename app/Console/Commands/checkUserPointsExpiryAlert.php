<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SalesRecord;
use Carbon\Carbon;
use App\Services\UserService;
use Helper;

class checkUserPointsExpiryAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:check-points-expiry-alert {--days=3 : Number of days before expiry to send alerts} {--dry-run : Simulate the process without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to users whose points will expire soon (default 3 days).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $alertDate = Carbon::today('Asia/Kuala_Lumpur')->addDays($days);

        $salesRecords = SalesRecord::where('status', 21)
            ->whereHas('transaction', function ($query) use ($alertDate) {
                $query->whereDate('expired_at', $alertDate);
            })
            ->with('transaction.user')
            ->get();

        if ($salesRecords->isEmpty()) {
            $this->info("No points expiring in the next {$days} day(s).");
            return 0;
        }

        $this->info("Found {$salesRecords->count()} sales records with points expiring in {$days} days:");

        foreach ($salesRecords as $record) {
            $this->line("SalesRecord ID: {$record->id}, Transaction ID: {$record->transaction->id}, Expiring At: {$record->transaction->expired_at}");

            if (!$this->option('dry-run')) {

                // Create user notification
                UserService::createUserNotification(
                    $record->transaction->user_id,
                    __('notification.points_expiry_alert'),
                    __('notification.points_expiry_alert_content', [
                        'amount' => $record->transaction->amount,
                        'days'   => $days
                    ]),
                    'points',
                    'points'
                );

                // Send real-time push notification
                $message['message']['en'] = __('notification.points_expiry_alert');
                $message['message_content']['en'] = __('notification.points_expiry_alert_content', [
                    'amount' => $record->transaction->amount,
                    'days'   => $days
                ]);
                $message['key'] = 'points_expiry_alert';
                $message['id'] = $record->transaction->id;

                Helper::sendNotification($record->transaction->user, $message);

                $this->comment("→ Sent expiry alert for Transaction ID {$record->transaction->id}");
            } else {
                $this->comment("→ [DRY RUN] Would send expiry alert for Transaction ID {$record->transaction->id}");
            }
        }

        $this->info('Expiry alert process completed.');
        return 0;
    }
}
