<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index() {
        $this->data['header']['title'] = __( 'template.dashboard' );
        $this->data['content'] = 'admin.dashboard.index';
        return view( 'admin.main' )->with( $this->data );
    }

    public function getEngagementStats() {
        return DashboardService::getEngagementStats();
    }

    public function getDailyUserStats() {
        return DashboardService::getDailyUserStats();
    }

    public function getRadioStreamGraph() {
        return DashboardService::getRadioStreamGraph();
    }

    public function getSubscriptionsTable( Request $request ) {
        return DashboardService::getSubscriptionsTable( $request );
    }

    public function getItemStreams( Request $request ) {
        return DashboardService::getItemStreams( $request );
    }

    public function getPlaylistStreams( Request $request ) {
        return DashboardService::getPlaylistStreams( $request );
    }

    public function getCollectionStreams( Request $request ) {
        return DashboardService::getCollectionStreams( $request );
    }

    public function getBannerClickStats() {
        return DashboardService::getBannerClickStats();
    }

    public function getPopAnnouncementClickStats() {
        return DashboardService::getPopAnnouncementClickStats();
    }
}
