<?php

namespace App\Console\Commands;

use App\Services\RadioListenerService;
use Illuminate\Console\Command;

class SyncRadioListeners extends Command
{
    protected $signature = 'radio:sync-listeners';
    protected $description = 'Store sampled Icecast listener IP sessions and mark disconnected clients';

    public function handle()
    {
        if (!config('services.radio.listener_tracking')) {
            $this->info('Listener tracking is disabled.');
            return 0;
        }
        try {
            $summary = RadioListenerService::sync();
            $this->info("Recorded {$summary['connections']} connections, {$summary['unique_ips']} unique IPs.");
            return 0;
        } catch (\Throwable $e) {
            // HTTP transport exceptions may contain credentials/headers: never print those.
            $this->error($e instanceof \RuntimeException && get_class($e) === \RuntimeException::class
                ? $e->getMessage() : 'Listener sync failed; check Icecast connectivity and database configuration.');
            return 1;
        }
    }
}
