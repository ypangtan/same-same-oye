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

    // Used by the admin page so "live" counts/IPs don't wait for the once-a-minute
    // scheduled sync. Failures (Icecast down, lock held by the scheduled sync, etc.)
    // are swallowed here; summary()/onlineListeners() already fall back to the last
    // known-good sample and report it as stale rather than wiping the display.
    private static function trySync(): void
    {
        if (!config('services.radio.listener_tracking')) return;
        try {
            self::sync();
        } catch (\Throwable $e) {
            // Intentionally ignored; see comment above.
        }
    }

    public static function freshSummary(): array
    {
        self::trySync();
        return self::summary();
    }

    public static function freshOnlineListeners(): array
    {
        self::trySync();
        return self::onlineListeners();
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

    public static function onlineListeners(): array
    {
        $summary = self::summary();
        $listeners = [];
        if (!empty($summary['fresh'])) {
            $listeners = DB::table('radio_listener_sessions')
                ->where('source_key', self::sourceKey())->whereNull('disconnected_at')
                ->orderBy('connected_at')->get(['client_id', 'ip', 'connected_at'])
                ->map(fn ($row) => [
                    'client_id' => $row->client_id,
                    'ip' => $row->ip,
                    'connected_at' => Carbon::parse($row->connected_at)->toIso8601String(),
                ])->all();
        }
        return ['summary' => $summary, 'listeners' => $listeners];
    }

    /**
     * All recorded listener sessions (online and disconnected) for the backoffice
     * "Listener IPs" datatable on the history page. Server-side paginated: this table
     * only grows over time (no retention policy), unlike the small live-listeners list.
     */
    public static function sessionsTable($request): array
    {
        $key = self::sourceKey();
        $base = fn () => DB::table('radio_listener_sessions')->where('source_key', $key);

        $totalRecord = $base()->count();

        $query = $base();
        $filter = false;
        if (!empty($request->ip)) {
            $query->where('ip', 'LIKE', '%'.$request->ip.'%');
            $filter = true;
        }
        if (!empty($request->connected_date)) {
            self::applyDateRangeFilter($query, 'connected_at', $request->connected_date);
            $filter = true;
        }
        if (!empty($request->disconnected_date)) {
            self::applyDateRangeFilter($query, 'disconnected_at', $request->disconnected_date);
            $filter = true;
        }

        $columns = ['connected_at', 'ip', 'connected_at', 'disconnected_at'];
        $column = $columns[$request->input('order.0.column')] ?? 'connected_at';
        $dir = $request->input('order.0.dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($column, $dir);

        $filteredCount = $filter ? (clone $query)->count() : $totalRecord;

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $rows = $query->skip($request->start)->take($limit)
            ->get(['ip', 'connected_at', 'disconnected_at'])
            ->map(fn ($row) => [
                'ip' => $row->ip,
                'connected_at' => Carbon::parse($row->connected_at)->toIso8601String(),
                'disconnected_at' => $row->disconnected_at ? Carbon::parse($row->disconnected_at)->toIso8601String() : null,
            ]);

        return [
            'draw' => (int) $request->draw,
            'recordsFiltered' => $filteredCount,
            'recordsTotal' => $totalRecord,
            'data' => $rows,
        ];
    }

    // Same convention as RadioQueueService::applyDateRangeFilter (date-only search
    // input, interpreted in Asia/Kuala_Lumpur and compared against the stored UTC column).
    private static function applyDateRangeFilter($query, string $column, string $value): void
    {
        if (str_contains($value, 'to')) {
            $dates = explode(' to ', $value);
            $startDate = explode('-', $dates[0]);
            $start = Carbon::create($startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur');
            $endDate = explode('-', $dates[1]);
            $end = Carbon::create($endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur');
        } else {
            $dates = explode('-', $value);
            $start = Carbon::create($dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur');
            $end = Carbon::create($dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur');
        }

        $query->whereBetween($column, [date('Y-m-d H:i:s', $start->timestamp), date('Y-m-d H:i:s', $end->timestamp)]);
    }
}
