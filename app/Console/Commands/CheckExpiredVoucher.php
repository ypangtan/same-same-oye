<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\{
    DB,
};

use App\Services\{
    VoucherService,
};

use App\Models\{
    Voucher,
    UserVoucher,
};

use Carbon\Carbon;

class CheckExpiredVoucher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:expired-voucher
    {--dry-run : Whether the calculated result should store}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check voucher expired';

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
        $isDryRun = $this->option('dryrun');
        
        $vouchers = Voucher::where('status', 10)->where( 'validity_days', '>', 0 )->get();

        foreach ( $vouchers as $voucher ) {

            DB::beginTransaction();

            if (Carbon::parse( $voucher->expired_date)->lessThan(Carbon::now()->subMinutes(10))) {
                $voucher->status = 21;
                $voucher->save();
            }            

            if ( !$isDryRun ) {
                DB::commit();
            }
                   
        }

        $userVouchers = UserVoucher::where('status', 10)->get();

        foreach ( $userVouchers as $userVoucher ) {

            DB::beginTransaction();

            if (Carbon::parse( $userVoucher->expired_date)->lessThan(Carbon::now()->subMinutes(10)) && $userVoucher->voucher->validity_days > 0 ) {
                $userVoucher->status = 21;
                $userVoucher->save();
            }            

            if ( !$isDryRun ) {
                DB::commit();
            }
                   
        }

        return 0;
    }
}
