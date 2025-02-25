<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserCheckin;
use App\Services\UserService;
use Carbon\Carbon;
use DB;

class checkUserCheckin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:user-checkin 
        {--dryrun : Simulate the check-in reset process without storing changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset check-in streaks and reward eligible users';

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
            $this->info( 'Starting user check-in reset process...' );
        }

        DB::beginTransaction();

        try {
            $users = User::all();
            $currentDate = now()->format( 'Y-m-d' );

            foreach ( $users as $user ) {
                $this->processUserCheckin( $user, $currentDate, $isDryRun );
            }

            if ( !$isDryRun ) {
                DB::commit();
                $this->info( 'User check-in reset process completed successfully.' );
            } else {
                DB::rollBack();
                $this->warn( 'Dry run completed. No changes were saved.' );
            }
        } catch ( \Exception $e ) {
            DB::rollBack();
            $this->error( 'Error: ' . $e->getMessage() );
        }
    }

    private function processUserCheckin( $user, $currentDate, $isDryRun )
    {
        $this->info( 'Processing user ID: ' . $user->id );

        $lastCheckin = UserCheckin::where( 'user_id', $user->id )
            ->latest( 'checkin_date' )
            ->first();

        if ( $lastCheckin && Carbon::parse( $lastCheckin->checkin_date )->diffInDays( now() ) > 1 ) {
            $this->warn( 'User ID ' . $user->id . ' streak reset required.' );

            if ( !$isDryRun ) {
                $user->check_in_streak = 0;
                $user->save();

                UserService::createUserNotification(
                    $user->id,
                    'notification.user_checkin_reset',
                    'notification.user_checkin_reset_content',
                    'user_checkin',
                    'user_checkin'
                );

                $this->sendNotification( $user, 'checkin', __( 'notification.user_checkin_reset_content' ) );

                $this->info( 'Check-in streak reset for user ID: ' . $user->id );
            } else {
                $this->warn( '[DRY RUN] Streak reset skipped for user ID: ' . $user->id );
            }
        } else {
            $this->info( 'No streak reset needed for user ID: ' . $user->id );
        }
    }

    private function sendNotification( $user, $type, $message )
    {
        $messageContent = array();

        $messageContent['id'] = $user->id;
        $messageContent['message'] = $message;

        Helper::sendNotification( $user->user_id, $messageContent );
    }
}
