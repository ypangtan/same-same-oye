<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command( 'check:expired-voucher' )->timezone('Asia/Kuala_Lumpur')
        ->dailyAt('00:00');
        $schedule->command( 'check:user-checkin' )->timezone('Asia/Kuala_Lumpur')
        ->dailyAt('00:03');
        $schedule->command( 'user:check-points-expired' )->timezone('Asia/Kuala_Lumpur')
        ->dailyAt('00:05');
        $schedule->command( 'user:check-points-expiry-alert' )->timezone('Asia/Kuala_Lumpur')
        ->dailyAt('00:10');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
