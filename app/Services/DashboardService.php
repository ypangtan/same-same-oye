<?php

namespace App\Services;

use App\Models\{
    User,
    Banner,
    PopAnnouncement,
    UserSubscription,
    StreamLog,
};

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService {

    // ── Called by the view: sections 1–5 ─────────────────────────────────────

    public static function getEngagementStats( $request ) {
        $today        = Carbon::today()->timezone( 'Asia/Kuala_Lumpur' );
        $startOfMonth = Carbon::now()->timezone( 'Asia/Kuala_Lumpur' )->startOfMonth();

        // User type counts directly from users.membership
        $totalActive = User::where( 'status', 10 )->count();
        $freeUsers   = User::where( 'status', 10 )->where( 'membership', 0 )->count();
        $trialUsers  = User::where( 'status', 10 )->where( 'membership', 2 )->count();
        $paidUsers   = User::where( 'status', 10 )->whereIn( 'membership', [ 1, 3, 4 ] )->count();

        // New users
        $newUsersToday     = User::whereDate( 'created_at', $today )->count();
        $newUsersThisMonth = User::where( 'created_at', '>=', $startOfMonth )->count();

        // Subscriptions
        $subsToday     = UserSubscription::whereDate( 'created_at', $today )->count();
        $subsThisMonth = UserSubscription::where( 'created_at', '>=', $startOfMonth )->count();

        // Stream counts
        $streamRadio = DB::table( 'stream_logs' )
            ->join( 'types', 'types.id', '=', 'stream_logs.type_id' )
            ->whereRaw( 'LOWER(types.en_name) LIKE ?', [ '%radio%' ] )
            ->count();

        $streamItems = DB::table( 'stream_logs' )->whereNotNull( 'item_id' )->count();

        $streamPlaylists = DB::table( 'stream_logs' )->whereNotNull( 'playlist_id' )->count();

        return response()->json( [
            'total_active'       => number_format( $totalActive ),
            'free_users'         => number_format( $freeUsers ),
            'trial_users'        => number_format( $trialUsers ),
            'paid_users'         => number_format( $paidUsers ),
            'new_users_today'    => number_format( $newUsersToday ),
            'new_users_month'    => number_format( $newUsersThisMonth ),
            'subs_today'         => number_format( $subsToday ),
            'subs_month'         => number_format( $subsThisMonth ),
            'stream_radio'       => number_format( $streamRadio ),
            'stream_items'       => number_format( $streamItems ),
            'stream_playlists'   => number_format( $streamPlaylists ),
            'stream_collections' => '0',
        ] );
    }

    // ── Called by the view: daily user breakdown chart (last 30 days) ─────────

    public static function getDailyUserStats( $request ) {
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

    // ── Called by the view: banner click table ────────────────────────────────

    public static function getBannerClickStats( $request ) {
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

    // ── Called by the view: pop-announcement click table ─────────────────────

    public static function getPopAnnouncementClickStats( $request ) {
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
