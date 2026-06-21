<?php

namespace App\Services;

use App\Models\{
    User,
    Banner,
    PopAnnouncement,
    UserSubscription,
};

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardService {

    // ── Private helpers ───────────────────────────────────────────────────────

    private static function parseDateRange( ?string $range ): array {
        if ( !$range || !str_contains( $range, ' to ' ) ) {
            return [ null, null ];
        }
        $parts = explode( ' to ', $range );
        return [ trim( $parts[0] ) ?: null, trim( $parts[1] ) ?: null ];
    }

    private static function applyDateRange( $q, string $column, ?string $from, ?string $to ): void {
        if ( $from ) $q->where( $column, '>=', Carbon::parse( $from )->startOfDay() );
        if ( $to )   $q->where( $column, '<=', Carbon::parse( $to )->endOfDay() );
    }

    // ── Engagement summary cards ──────────────────────────────────────────────

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

        $streamCounts = DB::table( 'stream_logs' )
            ->selectRaw( 'content_type, COUNT(*) as total' )
            ->groupBy( 'content_type' )
            ->pluck( 'total', 'content_type' );

        // Per content-category type: types that have items, playlists, or collections
        $contentTypes = DB::table( 'types' )
            ->where( function ( $q ) {
                $q->whereExists( fn( $s ) => $s->select( DB::raw( 1 ) )->from( 'items' )->whereColumn( 'items.type_id', 'types.id' ) )
                  ->orWhereExists( fn( $s ) => $s->select( DB::raw( 1 ) )->from( 'playlists' )->whereColumn( 'playlists.type_id', 'types.id' ) )
                  ->orWhereExists( fn( $s ) => $s->select( DB::raw( 1 ) )->from( 'collections' )->whereColumn( 'collections.type_id', 'types.id' ) );
            } )
            ->select( 'id', 'en_name as name' )
            ->orderBy( 'en_name' )
            ->get();

        $itemsByType = DB::table( 'stream_logs' )
            ->join( 'items', 'items.id', '=', 'stream_logs.item_id' )
            ->where( 'stream_logs.content_type', 2 )
            ->selectRaw( 'items.type_id, COUNT(*) as total' )
            ->groupBy( 'items.type_id' )
            ->pluck( 'total', 'type_id' );

        $playlistsByType = DB::table( 'stream_logs' )
            ->join( 'playlists', 'playlists.id', '=', 'stream_logs.playlist_id' )
            ->where( 'stream_logs.content_type', 3 )
            ->selectRaw( 'playlists.type_id, COUNT(*) as total' )
            ->groupBy( 'playlists.type_id' )
            ->pluck( 'total', 'type_id' );

        $collsByType = DB::table( 'stream_logs' )
            ->join( 'collections', 'collections.id', '=', 'stream_logs.collection_id' )
            ->where( 'stream_logs.content_type', 4 )
            ->selectRaw( 'collections.type_id, COUNT(*) as total' )
            ->groupBy( 'collections.type_id' )
            ->pluck( 'total', 'type_id' );

        $streamTypes = $contentTypes->map( fn( $t ) => [
            'name'        => $t->name,
            'items'       => number_format( $itemsByType->get( $t->id, 0 ) ),
            'playlists'   => number_format( $playlistsByType->get( $t->id, 0 ) ),
            'collections' => number_format( $collsByType->get( $t->id, 0 ) ),
        ] )->values();

        return response()->json( [
            'total_active'       => number_format( $totalActive ),
            'free_users'         => number_format( $freeUsers ),
            'trial_users'        => number_format( $trialUsers ),
            'paid_users'         => number_format( $paidUsers ),
            'new_users_today'    => number_format( $newUsersToday ),
            'new_users_month'    => number_format( $newUsersThisMonth ),
            'subs_today'         => number_format( $subsToday ),
            'subs_month'         => number_format( $subsThisMonth ),
            'stream_radio'       => number_format( $streamCounts->get( 1, 0 ) ),
            'stream_types'       => $streamTypes,
        ] );
    }

    // ── Daily user chart ──────────────────────────────────────────────────────

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

    // ── Radio streams graph ───────────────────────────────────────────────────

    public static function getRadioStreamGraph() {
        $days  = 30;
        $since = Carbon::today()->subDays( $days - 1 )->startOfDay();

        $topRadios = DB::table( 'stream_logs' )
            ->where( 'content_type', 1 )
            ->whereNotNull( 'radio_name' )
            ->selectRaw( 'radio_name, COUNT(*) as total' )
            ->groupBy( 'radio_name' )
            ->orderByDesc( 'total' )
            ->limit( 8 )
            ->pluck( 'radio_name' );

        $labels = [];
        for ( $i = $days - 1; $i >= 0; $i-- ) {
            $labels[] = Carbon::today()->subDays( $i )->format( 'M d' );
        }

        if ( $topRadios->isEmpty() ) {
            return response()->json( [ 'labels' => $labels, 'series' => [] ] );
        }

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

    // ── Subscriptions DataTable ───────────────────────────────────────────────

    public static function getSubscriptionsTable( Request $request ) {
        [ $from, $to ] = self::parseDateRange( $request->input( 'date_range' ) );
        $search    = $request->input( 'search', '' );
        $subType   = $request->input( 'sub_type', '' );
        $subStatus = $request->input( 'sub_status', '' );

        $q = DB::table( 'user_subscriptions' )
            ->join( 'users', 'users.id', '=', 'user_subscriptions.user_id' )
            ->leftJoin( 'subscription_plans', 'subscription_plans.id', '=', 'user_subscriptions.subscription_plan_id' )
            ->select(
                'users.fullname as user_name',
                'users.email',
                'subscription_plans.name as plan_name',
                'user_subscriptions.type',
                'user_subscriptions.status',
                'user_subscriptions.start_date',
                'user_subscriptions.end_date',
            )
            ->orderByDesc( 'user_subscriptions.start_date' );

        self::applyDateRange( $q, 'user_subscriptions.start_date', $from, $to );

        if ( $search ) {
            $q->where( function ( $q2 ) use ( $search ) {
                $q2->where( 'users.fullname', 'like', "%{$search}%" )
                   ->orWhere( 'users.email', 'like', "%{$search}%" )
                   ->orWhere( 'subscription_plans.name', 'like', "%{$search}%" );
            } );
        }

        if ( $subType === 'Trial' ) {
            $q->where( 'user_subscriptions.type', 2 );
        } elseif ( $subType === 'Paid' ) {
            $q->where( 'user_subscriptions.type', 1 );
        }

        if ( $subStatus === 'Active' ) {
            $q->where( 'user_subscriptions.status', 10 );
        } elseif ( $subStatus === 'Inactive' ) {
            $q->where( 'user_subscriptions.status', '!=', 10 );
        }

        $rows = $q->get()->map( function ( $r ) {
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

    // ── Item Streams DataTable ────────────────────────────────────────────────

    public static function getItemStreams( Request $request ) {
        [ $from, $to ] = self::parseDateRange( $request->input( 'date_range' ) );
        $search = $request->input( 'search', '' );
        $typeId = $request->input( 'type_id', '' );

        // Types from items table regardless of stream history
        $types = DB::table( 'types' )
            ->join( 'items', 'items.type_id', '=', 'types.id' )
            ->distinct()
            ->select( 'types.id', 'types.en_name as name' )
            ->orderBy( 'types.en_name' )
            ->get();

        $q = DB::table( 'stream_logs' )
            ->join( 'items', 'items.id', '=', 'stream_logs.item_id' )
            ->join( 'types', 'types.id', '=', 'items.type_id' )
            ->where( 'stream_logs.content_type', 2 );

        self::applyDateRange( $q, 'stream_logs.created_at', $from, $to );

        if ( $search ) {
            $q->where( function ( $q2 ) use ( $search ) {
                $q2->where( 'items.title', 'like', "%{$search}%" )
                   ->orWhere( 'items.author', 'like', "%{$search}%" );
            } );
        }

        if ( $typeId ) {
            $q->where( 'types.id', $typeId );
        }

        $rows = $q->selectRaw(
                'types.id as type_id, types.en_name as type_name,
                 items.title, items.author,
                 COUNT(*) as total,
                 MAX(stream_logs.created_at) as last_played'
            )
            ->groupBy( 'types.id', 'types.en_name', 'items.title', 'items.author' )
            ->orderByDesc( 'total' )
            ->get();

        return response()->json( [ 'items' => $rows, 'types' => $types ] );
    }

    // ── Playlist Streams DataTable ────────────────────────────────────────────

    public static function getPlaylistStreams( Request $request ) {
        [ $from, $to ] = self::parseDateRange( $request->input( 'date_range' ) );
        $search = $request->input( 'search', '' );
        $typeId = $request->input( 'type_id', '' );

        // Types from playlists table regardless of stream history
        $types = DB::table( 'types' )
            ->join( 'playlists', 'playlists.type_id', '=', 'types.id' )
            ->distinct()
            ->select( 'types.id', 'types.en_name as name' )
            ->orderBy( 'types.en_name' )
            ->get();

        $q = DB::table( 'stream_logs' )
            ->join( 'playlists', 'playlists.id', '=', 'stream_logs.playlist_id' )
            ->join( 'types', 'types.id', '=', 'playlists.type_id' )
            ->where( 'stream_logs.content_type', 3 );

        self::applyDateRange( $q, 'stream_logs.created_at', $from, $to );

        if ( $search ) {
            $q->where( 'playlists.en_name', 'like', "%{$search}%" );
        }

        if ( $typeId ) {
            $q->where( 'types.id', $typeId );
        }

        $rows = $q->selectRaw(
                'types.id as type_id, types.en_name as type_name,
                 playlists.en_name as name,
                 COUNT(*) as total,
                 MAX(stream_logs.created_at) as last_played'
            )
            ->groupBy( 'types.id', 'types.en_name', 'playlists.id', 'playlists.en_name' )
            ->orderByDesc( 'total' )
            ->get();

        return response()->json( [ 'playlists' => $rows, 'types' => $types ] );
    }

    // ── Collection Streams DataTable ──────────────────────────────────────────

    public static function getCollectionStreams( Request $request ) {
        [ $from, $to ] = self::parseDateRange( $request->input( 'date_range' ) );
        $search = $request->input( 'search', '' );
        $typeId = $request->input( 'type_id', '' );

        // Types from collections table regardless of stream history
        $types = DB::table( 'types' )
            ->join( 'collections', 'collections.type_id', '=', 'types.id' )
            ->distinct()
            ->select( 'types.id', 'types.en_name as name' )
            ->orderBy( 'types.en_name' )
            ->get();

        $q = DB::table( 'stream_logs' )
            ->join( 'collections', 'collections.id', '=', 'stream_logs.collection_id' )
            ->join( 'types', 'types.id', '=', 'collections.type_id' )
            ->where( 'stream_logs.content_type', 4 );

        self::applyDateRange( $q, 'stream_logs.created_at', $from, $to );

        if ( $search ) {
            $q->where( 'collections.en_name', 'like', "%{$search}%" );
        }

        if ( $typeId ) {
            $q->where( 'types.id', $typeId );
        }

        $rows = $q->selectRaw(
                'types.id as type_id, types.en_name as type_name,
                 collections.en_name as name,
                 COUNT(*) as total,
                 MAX(stream_logs.created_at) as last_played'
            )
            ->groupBy( 'types.id', 'types.en_name', 'collections.id', 'collections.en_name' )
            ->orderByDesc( 'total' )
            ->get();

        return response()->json( [ 'collections' => $rows, 'types' => $types ] );
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
                    'name'       => $banner->en_name ?? '—',
                    'image_path' => $banner->image_path,
                    'status'     => $banner->status == 10 ? 'Active' : 'Inactive',
                    'clicks'     => $banner->clicks_count,
                    'created_at' => $banner->created_at ? $banner->created_at->format( 'Y-m-d' ) : '—',
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
                    'title'      => $popup->en_title ?? '—',
                    'image_path' => $popup->image_path,
                    'status'     => $popup->status == 10 ? 'Active' : 'Inactive',
                    'clicks'     => $popup->clicks_count,
                    'created_at' => $popup->created_at ? $popup->created_at->format( 'Y-m-d' ) : '—',
                ];
            } );

        return response()->json( [ 'popups' => $popups ] );
    }
}
