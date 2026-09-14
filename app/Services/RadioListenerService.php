<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RadioListenerService
{
    public static function sourceKey(): string
    {
        return hash('sha256', config('services.radio.icecast_admin_url').'|'.config('services.radio.icecast_mount'));
    }

    public static function sync(): array
    {
        if (!config('services.radio.listener_tracking')) {
            throw new RuntimeException('Listener tracking is disabled.');
        }
        $key = self::sourceKey();
        return Cache::lock('radio-listeners:'.$key, 60)->block(1, function () use ($key) {
            try {
                if (!config('services.radio.icecast_admin_password')) {
                    throw new RuntimeException('Icecast admin password is not configured.');
                }
                $response = Http::withBasicAuth(config('services.radio.icecast_admin_user'), config('services.radio.icecast_admin_password'))
                    ->withOptions(['allow_redirects' => false])->timeout(5)
                    ->get(config('services.radio.icecast_admin_url'), ['mount' => config('services.radio.icecast_mount')]);
                if (!$response->successful()) {
                    throw new RuntimeException('Icecast listener request failed (HTTP '.$response->status().').');
                }
                $listeners = self::parseListeners($response->body(), config('services.radio.icecast_mount'));
                $now = Carbon::now();
                DB::transaction(function () use ($key, $listeners, $now) {
                    $active = DB::table('radio_listener_sessions')->where('source_key', $key)
                        ->whereNull('disconnected_at')->lockForUpdate()->get()->keyBy('client_id');
                    foreach ($listeners as $listener) {
                        $previous = $active->pull($listener['id']);
                        $connectedAt = $now->copy()->subSeconds($listener['connected']);
                        // Client IDs can be reused after Icecast restarts. Allow polling/network jitter.
                        if ($previous && ($previous->ip !== $listener['ip']
                            || abs(Carbon::parse($previous->connected_at)->diffInSeconds($connectedAt, false)) > 10)) {
                            DB::table('radio_listener_sessions')->where('id', $previous->id)->update(['disconnected_at' => $now]);
                            $previous = null;
                        }
                        if ($previous) {
                            DB::table('radio_listener_sessions')->where('id', $previous->id)->update(['last_seen_at' => $now]);
                        } else {
                            DB::table('radio_listener_sessions')->insert([
                                'source_key' => $key, 'client_id' => $listener['id'], 'ip' => $listener['ip'],
                                'connected_at' => $connectedAt, 'first_seen_at' => $now, 'last_seen_at' => $now,
                            ]);
                        }
                    }
                    foreach ($active as $previous) {
                        DB::table('radio_listener_sessions')->where('id', $previous->id)->update(['disconnected_at' => $now]);
                    }
                    DB::table('radio_listener_syncs')->updateOrInsert(['source_key' => $key], ['synced_at' => $now, 'failed_at' => null]);
                });
                return self::summary();
            } catch (\Throwable $e) {
                DB::table('radio_listener_syncs')->updateOrInsert(['source_key' => $key], ['failed_at' => Carbon::now()]);
                // Never mark everyone offline on an authentication, XML or network failure.
                throw $e;
            }
        });
    }

    public static function parseListeners(string $body, string $mount): array
    {
        if (stripos($body, '<!DOCTYPE') !== false || stripos($body, '<!ENTITY') !== false) {
            throw new RuntimeException('Unexpected Icecast XML declarations.');
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        if ($xml === false || $xml->getName() !== 'icestats') {
            throw new RuntimeException('Invalid Icecast listener response.');
        }
        $source = null;
        foreach ($xml->source as $candidate) {
            if ((string) $candidate['mount'] === $mount) $source = $candidate;
        }
        if ($source === null) {
            throw new RuntimeException('Requested Icecast mount is missing.');
        }
        $result = [];
        foreach ($source->listener as $listener) {
            // 2.5 uses lowercase; legacy mode uses ID/IP/Connected.
            $id = (string) ($listener->id ?? $listener->ID ?? $listener['id']);
            $ip = trim((string) ($listener->ip ?? $listener->IP));
            $connected = (string) ($listener->connected ?? $listener->Connected);
            if (!ctype_digit($id) || strlen($id) > 32 || !filter_var($ip, FILTER_VALIDATE_IP)
                || !ctype_digit($connected) || (float) $connected > 2147483647 || isset($result[$id])) {
                throw new RuntimeException('Invalid Icecast listener entry.');
            }
            $ip = inet_ntop(inet_pton($ip));
            if (str_starts_with($ip, '::ffff:')) $ip = substr($ip, 7);
            if ($ip === '::1' || str_starts_with($ip, '127.')) {
                throw new RuntimeException('Icecast reports loopback IPs. Configure trusted proxy forwarding first.');
            }
            $result[$id] = ['id' => $id, 'ip' => $ip, 'connected' => (int) $connected];
        }
        return array_values($result);
    }

    public static function summary(): array
    {
        if (!config('services.radio.listener_tracking')) return ['enabled' => false];
        $key = self::sourceKey();
        $sync = DB::table('radio_listener_syncs')->where('source_key', $key)->first();
        $fresh = $sync && $sync->synced_at && !$sync->failed_at
            && Carbon::parse($sync->synced_at)->greaterThan(Carbon::now()->subSeconds(120));
        $rows = DB::table('radio_listener_sessions')->where('source_key', $key);
        return [
            'enabled' => true, 'fresh' => (bool) $fresh,
            'sampled_at' => $sync && $sync->synced_at ? Carbon::parse($sync->synced_at)->toIso8601String() : null,
            'connections' => $fresh ? (clone $rows)->whereNull('disconnected_at')->count() : null,
            'unique_ips' => $fresh ? (clone $rows)->whereNull('disconnected_at')->distinct()->count('ip') : null,
            'today_unique_ips' => (clone $rows)->where('last_seen_at', '>=', Carbon::now('Asia/Kuala_Lumpur')->startOfDay()->utc())
                ->distinct()->count('ip'),
        ];
    }
}
