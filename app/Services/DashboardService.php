<?php

namespace App\Services;

use App\Models\{
    User,
    Banner,
    PopAnnouncement,
    UserSubscription,
};

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService {

    // ── Summary stat cards (sections 1–5) ────────────────────────────────────

    public static function getEngagementStats() {
        $today        = Carbon::today()->timezone( 'Asia/Kuala_Lumpur' );
        $startOfMonth = Carbon::now()->timezone( 'Asia/Kuala_Lumpur' )->startOfMonth();

        $totalActive = User::where( 'status', 10 )->count();
        $freeUsers   = User::where( 'status', 10 )->where( 'membership', 0 )->count();
        $trialUsers  = User::where( 'status', 10 )->where( 'membership', 2 )->count();
        $paidUsers   = User::where( 'status', 10 )->whereIn( 'membership', [ 1, 3, 4 ] )->count();

        $newUsersToday     = User::whereDate( 'created_at', $today )->count();
        $newUsersThisMonth = User::where( 'created_at', '>=', $startOfMonth )->count();

        $subsToday     = UserSubscription::whereDate( 'created_at', $today )->count();
        $subsThisMonth = UserSubscription::where( 'created_at', '>=', $startOfMonth )->count();

        // Stream totals by content_type
        $streamCounts = DB::table( 'stream_logs' )
            ->selectRaw( 'content_type, COUNT(*) as total' )
            ->groupBy( 'content_type' )
            ->pluck( 'total', 'content_type' );

        return response()->json( [
            'total_active'       => number_format( $totalActive ),
            'free_users'         => number_format( $freeUsers ),
            'trial_users'        => number_format( $trialUsers ),
            'paid_users'         => number_format( $paidUsers ),
            'new_users_today'    => number_format( $newUsersToday ),
            'new_users_month'    => number_format( $newUsersThisMonth ),
            'subs_today'         => number_format( $subsToday ),
            'subs_month'         => number_format( $subsThisMonth ),
            'stream_radio'       => number_format( $streamCounts->get( 1,      0 ) ),
            'stream_items'       => number_format( $streamCounts->get( 2,       0 ) ),
            'stream_playlists'   => number_format( $streamCounts->get( 3,   0 ) ),
            'stream_collections' => number_format( $streamCounts->get( 4, 0 ) ),
        ] );
    }

    // ── Chart: daily user counts by membership (last 30 days) ────────────────

    public static function getDailyUserStats() {
        $days       = 30;
        $xAxis      = [];
        $free_data  = [];
        $trial_data = [];
        $paid_data  = [];

        for ( $x = $days - 1; $x >= 0; $x-- ) {
            $date = Carbon::today()->subDays( $x )->format( 'Y-m-d' );

            $free_data[]  = User::where( 'status', 10 )->where( 'membership', 0 )
                ->whereDate( 'created_at', '<=', $date )->count();
            $trial_data[] = User::where( 'status', 10 )->where( 'membership', 2 )
                ->whereDate( 'created_at', '<=', $date )->count();
            $paid_data[]  = User::where( 'status', 10 )->whereIn( 'membership', [ 1, 3, 4 ] )
                ->whereDate( 'created_at', '<=', $date )->count();
            $xAxis[]      = Carbon::parse( $date )->format( 'M d' );
        }

        return response()->json( [
            'xAxis'      => $xAxis,
            'free_data'  => $free_data,
            'trial_data' => $trial_data,
            'paid_data'  => $paid_data,
        ] );
    }

    // ── Chart: radio streams per day, broken down by radio_name ─────────────

    public static function getRadioStreamGraph() {
        $days  = 30;
        $since = Carbon::today()->subDays( $days - 1 )->startOfDay();

        // Top 8 radio stations by all-time plays
        $topRadios = DB::table( 'stream_logs' )
            ->where( 'content_type', 1 )
            ->whereNotNull( 'radio_name' )
            ->selectRaw( 'radio_name, COUNT(*) as total' )
            ->groupBy( 'radio_name' )
            ->orderByDesc( 'total' )
            ->limit( 8 )
            ->pluck( 'radio_name' );

        // Build date labels
        $labels = [];
        for ( $i = $days - 1; $i >= 0; $i-- ) {
            $labels[] = Carbon::today()->subDays( $i )->format( 'M d' );
        }

        if ( $topRadios->isEmpty() ) {
            return response()->json( [ 'labels' => $labels, 'series' => [] ] );
        }

        // Single query: count per radio per day
        $raw = DB::table( 'stream_logs' )
            ->where( 'content_type', 1 )
            ->whereIn( 'radio_name', $topRadios )
            ->where( 'created_at', '>=', $since )
            ->selectRaw( 'radio_name, DATE(created_at) as date, COUNT(*) as cnt' )
            ->groupBy( 'radio_name', DB::raw( 'DATE(created_at)' ) )
            ->get()
            ->groupBy( 'radio_name' )
            ->map( fn( $rows ) => $rows->keyBy( 'date' ) );

        $series = $topRadios->map( function ( $name ) use ( $raw, $days ) {
            $byDate = $raw->get( $name, collect() );
            $data   = [];
            for ( $i = $days - 1; $i >= 0; $i-- ) {
                $dateStr = Carbon::today()->subDays( $i )->format( 'Y-m-d' );
                $data[]  = (int) ( $byDate->get( $dateStr )->cnt ?? 0 );
            }
            return [ 'name' => $name, 'data' => $data ];
        } )->values();

        return response()->json( [ 'labels' => $labels, 'series' => $series ] );
    }

    // ── Helper: resolve date range from period string ─────────────────────────

    private static function periodRange( string $period ): array {
        $tz = 'Asia/Kuala_Lumpur';
        return match ( $period ) {
            'today' => [ Carbon::today( $tz )->startOfDay(), Carbon::today( $tz )->endOfDay() ],
            'year'  => [ Carbon::now( $tz )->startOfYear(), Carbon::now( $tz )->endOfYear() ],
            'all'   => [ null, null ],
            default => [ Carbon::now( $tz )->startOfMonth(), Carbon::now( $tz )->endOfMonth() ],
        };
    }

    // ── DataTable: subscriptions list ─────────────────────────────────────────

    public static function getSubscriptionsTable( string $period = 'month' ) {
        [ $from, $to ] = self::periodRange( $period );

        $query = DB::table( 'user_subscriptions' )
            ->join( 'users', 'users.id', '=', 'user_subscriptions.user_id' )
            ->leftJoin( 'subscription_plans', 'subscription_plans.id', '=', 'user_subscriptions.subscription_plan_id' )
            ->select(
                'users.name as user_name',
                'users.email',
                'subscription_plans.name as plan_name',
                'user_subscriptions.type',
                'user_subscriptions.status',
                'user_subscriptions.start_date',
                'user_subscriptions.end_date',
                'user_subscriptions.created_at',
            )
            ->orderByDesc( 'user_subscriptions.created_at' );

        if ( $from ) $query->where( 'user_subscriptions.created_at', '>=', $from );
        if ( $to )   $query->where( 'user_subscriptions.created_at', '<=', $to );

        $rows = $query->get()->map( function ( $r ) {
            return [
                'user'       => $r->user_name ?? '—',
                'email'      => $r->email ?? '—',
                'plan'       => $r->plan_name ?? '—',
                'type'       => $r->type == 2 ? 'Trial' : 'Paid',
                'status'     => $r->status == 10 ? 'Active' : 'Inactive',
                'start_date' => $r->start_date ? Carbon::parse( $r->start_date )->format( 'Y-m-d' ) : '—',
                'end_date'   => $r->end_date   ? Carbon::parse( $r->end_date )->format( 'Y-m-d' )   : '—',
            ];
        } );

        return response()->json( [ 'subscriptions' => $rows ] );
    }

    // ── Tables: streams grouped by content type ───────────────────────────────

    public static function getStreamsByType( string $period = 'month' ) {
        [ $from, $to ] = self::periodRange( $period );

        $applyPeriod = function ( $q ) use ( $from, $to ) {
            if ( $from ) $q->where( 'stream_logs.created_at', '>=', $from );
            if ( $to )   $q->where( 'stream_logs.created_at', '<=', $to );
        };

        // Item streams grouped by their item.type_id
        $iq = DB::table( 'stream_logs' )
            ->join( 'items', 'items.id', '=', 'stream_logs.item_id' )
            ->join( 'types', 'types.id', '=', 'items.type_id' )
            ->where( 'stream_logs.content_type', 2 );
        $applyPeriod( $iq );
        $itemStreams = $iq
            ->selectRaw( 'types.id as type_id, types.en_name as type_name, items.id as item_id, items.title, items.author, COUNT(*) as total, MAX(stream_logs.created_at) as last_played' )
            ->groupBy( 'types.id', 'types.en_name', 'items.id', 'items.title', 'items.author' )
            ->orderBy( 'types.en_name' )
            ->orderByDesc( 'total' )
            ->get()
            ->groupBy( 'type_id' );

        // Playlist streams grouped by their playlist.type_id
        $pq = DB::table( 'stream_logs' )
            ->join( 'playlists', 'playlists.id', '=', 'stream_logs.playlist_id' )
            ->join( 'types', 'types.id', '=', 'playlists.type_id' )
            ->where( 'stream_logs.content_type', 3 );
        $applyPeriod( $pq );
        $playlistStreams = $pq
            ->selectRaw( 'types.id as type_id, types.en_name as type_name, playlists.id as playlist_id, playlists.en_name as name, COUNT(*) as total, MAX(stream_logs.created_at) as last_played' )
            ->groupBy( 'types.id', 'types.en_name', 'playlists.id', 'playlists.en_name' )
            ->orderBy( 'types.en_name' )
            ->orderByDesc( 'total' )
            ->get()
            ->groupBy( 'type_id' );

        // Collection streams grouped by their collection.type_id
        $cq = DB::table( 'stream_logs' )
            ->join( 'collections', 'collections.id', '=', 'stream_logs.collection_id' )
            ->join( 'types', 'types.id', '=', 'collections.type_id' )
            ->where( 'stream_logs.content_type', 4 );
        $applyPeriod( $cq );
        $collectionStreams = $cq
            ->selectRaw( 'types.id as type_id, types.en_name as type_name, collections.id as collection_id, collections.en_name as name, COUNT(*) as total, MAX(stream_logs.created_at) as last_played' )
            ->groupBy( 'types.id', 'types.en_name', 'collections.id', 'collections.en_name' )
            ->orderBy( 'types.en_name' )
            ->orderByDesc( 'total' )
            ->get()
            ->groupBy( 'type_id' );

        // Merge all type IDs encountered
        $allTypeIds = $itemStreams->keys()
            ->merge( $playlistStreams->keys() )
            ->merge( $collectionStreams->keys() )
            ->unique();

        // Build type name map
        $typeNames = DB::table( 'types' )
            ->whereIn( 'id', $allTypeIds )
            ->pluck( 'en_name', 'id' );

        $types = $allTypeIds->sortBy( fn( $id ) => $typeNames->get( $id, '' ) )->values()->map( function ( $typeId ) use ( $itemStreams, $playlistStreams, $collectionStreams, $typeNames ) {
            return [
                'id'          => $typeId,
                'name'        => $typeNames->get( $typeId, 'Unknown' ),
                'items'       => $itemStreams->get( $typeId, collect() )->values(),
                'playlists'   => $playlistStreams->get( $typeId, collect() )->values(),
                'collections' => $collectionStreams->get( $typeId, collect() )->values(),
            ];
        } );

        return response()->json( [ 'types' => $types ] );
    }

    // ── Banner click table ────────────────────────────────────────────────────

    public static function getBannerClickStats() {
        $banners = Banner::withCount( 'clicks' )
            ->where( 'status', '!=', 20 )
            ->orderBy( 'sequence' )
            ->get()
            ->map( function ( $banner ) {
                return [
                    'id'         => $banner->id,
                    'name'       => $banner->en_name ?? '-',
                    'image_path' => $banner->image_path,
                    'status'     => $banner->status,
                    'clicks'     => $banner->clicks_count,
                    'created_at' => $banner->created_at ? $banner->created_at->format( 'Y-m-d' ) : '-',
                ];
            } );

        return response()->json( [ 'banners' => $banners ] );
    }

    // ── Pop-announcement click table ──────────────────────────────────────────

    public static function getPopAnnouncementClickStats() {
        $popups = PopAnnouncement::withCount( 'clicks' )
            ->where( 'status', '!=', 20 )
            ->latest()
            ->get()
            ->map( function ( $popup ) {
                return [
                    'id'         => $popup->id,
                    'title'      => $popup->en_title ?? '-',
                    'image_path' => $popup->image_path,
                    'status'     => $popup->status,
                    'clicks'     => $popup->clicks_count,
                    'created_at' => $popup->created_at ? $popup->created_at->format( 'Y-m-d' ) : '-',
                ];
            } );

        return response()->json( [ 'popups' => $popups ] );
    }
}
