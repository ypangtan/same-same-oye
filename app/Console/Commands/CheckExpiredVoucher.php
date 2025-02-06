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
    {--dryrun : Whether the calculated result should store}';

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
        
        $vouchers = Voucher::where('status', 10)->get();

        foreach ( $vouchers as $voucher ) {

            DB::beginTransaction();

            if (Carbon::parse( $voucher->created_at)->lessThan(Carbon::now()->subMinutes(10))) {
                $voucher->status = 21;
                $voucher->save();
            }            

            if ( !$isDryRun ) {
                DB::commit();
            }
                   
        }

        return 0;
    }
}
