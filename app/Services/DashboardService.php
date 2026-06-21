<?php

namespace App\Services;

use App\Models\{
    User,
    Banner,
    PopAnnouncement,
    UserSubscription,
};

use Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService {

    public static function getBannerClickStats( $request ) {
        // Include all banners (active and suspended) unless explicitly deleted
        $banners = Banner::withCount('clicks')->where( 'status', '!=', 20 )->orderBy('sequence')->get()->map(function ($banner) {
            return [
                'id' => $banner->id,
                'name' => $banner->en_name ?? '-',
                'image_path' => $banner->image_path,
                'status' => $banner->status,
                'clicks' => $banner->clicks_count,
                'created_at' => $banner->created_at ? $banner->created_at->format('Y-m-d') : '-',
            ];
        });

        return response()->json(['banners' => $banners]);
    }

    public static function getPopAnnouncementClickStats( $request ) {
        $popups = PopAnnouncement::withCount('clicks')->where( 'status', '!=', 20 )->latest()->get()->map(function ($popup) {
            return [
                'id' => $popup->id,
                'title' => $popup->en_title ?? '-',
                'image_path' => $popup->image_path,
                'status' => $popup->status,
                'clicks' => $popup->clicks_count,
                'created_at' => $popup->created_at ? $popup->created_at->format('Y-m-d') : '-',
            ];
        });

        return response()->json(['popups' => $popups]);
    }

    public static function getDailyUserStats( $request ) {
        $days = 30;
        $xAxis = [];
        $free_data  = [];
        $trial_data = [];
        $paid_data  = [];

        for ($x = $days - 1; $x >= 0; $x--) {
            $date = Carbon::today()->subDays($x)->format('Y-m-d');

            $free_data[]  = User::where('status', 10)->where('membership', 0)
                ->whereDate('created_at', '<=', $date)->count();
            $trial_data[] = User::where('status', 10)->where('membership', 2)
                ->whereDate('created_at', '<=', $date)->count();
            $paid_data[]  = User::where('status', 10)->whereIn('membership', [1, 3, 4])
                ->whereDate('created_at', '<=', $date)->count();
            $xAxis[]      = Carbon::parse($date)->format('M d');
        }

        return response()->json([
            'xAxis'      => $xAxis,
            'free_data'  => $free_data,
            'trial_data' => $trial_data,
            'paid_data'  => $paid_data,
        ]);
    }

    public static function getSummaryDetail ( $request ) {
        $total_subscription_today = UserSubscription::whereDate( 'created_at', Carbon::today() )->count();
        $total_subscription_this_month = UserSubscription::whereDate( 'created_at', Carbon::today()->format('Y-m') )->count();

        return response()->json([
            'total_subscription_today' => $total_subscription_today,
            'total_subscription_this_month' => $total_subscription_this_month,
        ]);
    }

}