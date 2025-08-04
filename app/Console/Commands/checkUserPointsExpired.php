<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SalesRecord;
use App\Models\Wallet;
use Carbon\Carbon;
use App\Services\WalletService;
use App\Services\UserService;

use Helper;

class checkUserPointsExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:check-points-expired {--dry-run : Simulate the process without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all Sales Records with status 21 where related WalletTransaction has expired today';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::today('Asia/Kuala_Lumpur');

        // Fetch Sales Records with status 21 and related transaction expiring today
        $salesRecords = SalesRecord::where('status', 21)
            ->whereHas('transaction', function ($query) use ($today) {
                $query->whereDate('expired_at', $today);
            })
            ->with('transaction')
            ->get();

        if ($salesRecords->isEmpty()) {
            $this->info('No sales records found with points expiring today.');
            return 0;
        }

        $this->info("Found {$salesRecords->count()} sales records with expiring points:");

        foreach ($salesRecords as $record) {
            $this->line("SalesRecord ID: {$record->id}, Transaction ID: {$record->transaction->id}, Expired At: {$record->transaction->expired_at}");

            if (!$this->option('dry-run')) {

                $wallet = Wallet::lockForUpdate()->where('user_id', $record->transaction->user_id)->first();
            
                if ($wallet && $wallet->balance > 0) {
                    // Deduct only available balance or full amount if possible
                    $deductionAmount = min($wallet->balance, $record->transaction->amount);
            
                    $transaction = WalletService::transact($wallet, [
                        'amount' => -1 * $deductionAmount,
                        'remark' => 'Points Expired',
                        'type' => $wallet->type,
                        'invoice_id' => $record->id,
                        'transaction_type' => 25,
                    ]);
            
                    UserService::createUserNotification(
                        $record->transaction->user_id,
                        __('notification.points_expired'),
                        __('notification.points_expired_content', ['amount' => $deductionAmount]),
                        'points',
                        'points'
                    );
            
                    $message['message']['en'] = __('notification.points_expired');
                    $message['message_content']['en'] = __('notification.points_expired_content', ['amount' => $deductionAmount]);
                    $message['key'] = 'points';
                    $message['id'] = $transaction->id;
            
                    Helper::sendNotification($record->transaction->user, $message);
            
                    $this->comment("→ Deducted {$deductionAmount} points for Transaction ID {$record->transaction->id}");
                } else {
                    $this->comment("→ Wallet has no balance to deduct for Transaction ID {$record->transaction->id}");
                }
            
                // Mark record as expired regardless of full or partial deduction
                $record->status = 22;
                $record->save();
            }
             else {
                $this->comment("→ [DRY RUN] Would expire points for Transaction ID {$record->transaction->id}");
            }
        }

        $this->info('Process completed.');

        return 0;
    }
}
