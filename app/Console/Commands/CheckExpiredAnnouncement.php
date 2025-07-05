<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\{
    DB,
};

use App\Services\{
    AnnouncementService,
};

use App\Models\{
    Announcement,
};

use Carbon\Carbon;

class CheckExpiredAnnouncement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:expired-announcement {--dry-run : Whether the calculated result should store}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check announcement expired';

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
        $now = Carbon::now();
        $isDryRun = $this->option('dry-run');

        $expiredAnnouncements = Announcement::where('status', 10)
            ->whereNotNull('expired_date')
            ->where('expired_date', '<=', $now)
            ->get();

        if ( $expiredAnnouncements->isEmpty() ) {
            $this->info('No anouncements to expire.');
            return;
        }

        $this->info($isDryRun ? 'Dry Run: The following announcements would be expired:' : 'Expiring the following announcements:');

        foreach ( $expiredAnnouncements as $announcement ) {
            $this->line("• Anouncement ID: {$announcement->id}, End At: {$announcement->expired_date}");

            if ( ! $isDryRun ) {
                $announcement->status = 21;
                $announcement->save();
            }
        }

        $this->info($isDryRun 
            ? "Dry run complete — {$expiredAnnouncements->count()} anouncement(s) found."
            : "Expired {$expiredAnnouncements->count()} anouncement(s)."
        );
    }
}
