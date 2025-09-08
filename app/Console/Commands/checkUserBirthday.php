<?php

namespace App\Console\Commands;

use App\Models\BirthdayGiftSetting;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserCheckin;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Services\UserService;
use App\Services\WalletService;
use Carbon\Carbon;
use DB;

use Helper;

class checkUserBirthday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:user-birthday
        {--dryrun : Simulate the check user birthday voucher without storing changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'reward users who are bithday month';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option( 'dryrun' );

        if ( $isDryRun ) {
            $this->warn( 'Running in DRY RUN mode. No changes will be saved.' );
        } else {
            $this->info( 'Starting user birthday month checking process...' );
        }

        DB::beginTransaction();

        try {
            $startMonth = now()->timezone( 'Asia/Kuala_Lumpur' )->startOfMonth()->format('m-d');
            $endMonth = now()->timezone( 'Asia/Kuala_Lumpur' )->endOfMonth()->format('m-d');
            $users = User::where( 'status', 10 )
                ->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') BETWEEN ? AND ?", [
                    $startMonth,
                    $endMonth,
                ])->get();

            foreach ( $users as $user ) {
                $this->processUserChecking( $user->id, $startMonth, $endMonth, $isDryRun );
            }

            if ( !$isDryRun ) {
                DB::commit();
                $this->info( 'User birthday month checking process completed successfully.' );
            } else {
                DB::rollBack();
                $this->warn( 'Dry run completed. No changes were saved.' );
            }
        } catch ( \Exception $e ) {
            DB::rollBack();
            $this->error( 'Error: ' . $e->getMessage() );
        }
    }

    private function processUserChecking( $user_id, $startMonth, $endMonth, $isDryRun ) {
        $this->info( 'Processing user ID: ' . $user_id );

        $user = User::find( $user_id );

        if ( $user && ( Carbon::parse( $user->last_give_birthday_gift )->diffInYears( now()->timezone( 'Asia/Kuala_Lumpur' ) ) > 1 ) || $user->last_give_birthday_gift == null ) {
            $this->warn( 'User ID ' . $user->id . ' required.' );

            if ( !$isDryRun ) {
                // give voucher
                $gift = BirthdayGiftSetting::where( 'status', 10 )->first();
                if( $gift ) {
                    if( $gift->reward_type == 2 ) {
                        $voucher = Voucher::find( $gift->voucher_id );
                        if( $voucher ) {
                            $createUserVoucher = UserVoucher::create( [
                                'user_id' => $user->id, 
                                'voucher_id' => $voucher->id,
                                'expired_date' => $endMonth,
                                'total_left' => 1,
                            ] );
                        }
                    } else {
                        // give point
                        WalletService::transact( $user->wallets->where('type', 1)->first(), [
                            'amount' => $gift->reward_value,
                            'remark' => 'Birthdays Rewards',
                            'type' => 2,
                            'transaction_type' => 26,
                        ] );
                    }

                    $user->last_give_birthday_gift = $startMonth;
                    $user->save();

                    UserService::createUserNotification(
                        $user->id,
                        'notification.user_birthday',
                        'notification.user_birthday_content',
                        'user_birthday',
                        'user_birthday'
                    );

                    $this->sendNotification( $user, 'user_birthday', __( 'notification.user_birthday_content' ) );

                    $this->info( 'give birthday voucher for user ID: ' . $user->id );
                } else {
                    $this->info( 'not gift for user ID: ' . $user->id );
                }
            } else {
                $this->warn( '[DRY RUN] give birthday voucher skipped for user ID: ' . $user->id );
            }
        } else {
            $this->info( 'Skiping for user ID: ' . $user->id );
        }
    }

    private function sendNotification( $user, $type, $message ) {
        $messageContent = array();

        $messageContent['id'] = $user->id;
        $messageContent['message'] = $message;

        Helper::sendNotification( $user->user_id, $messageContent );
    }
}
