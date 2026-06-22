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

    public function getRadioStreamTable( Request $request ) {
        return DashboardService::getRadioStreamTable( $request );
    }

    public function getRadioStreamGraph() {
        return DashboardService::getRadioStreamGraph();
    }

    public function getSubscriptionsTable( Request $request ) {
        return DashboardService::getSubscriptionsTable( $request );
    }

    public function getItemStreams() {
        return DashboardService::getItemStreams();
    }

    public function getPlaylistStreams() {
        return DashboardService::getPlaylistStreams();
    }

    public function getCollectionStreams() {
        return DashboardService::getCollectionStreams();
    }

    public function getBannerClickStats() {
        return DashboardService::getBannerClickStats();
    }

    public function getPopAnnouncementClickStats() {
        return DashboardService::getPopAnnouncementClickStats();
    }
}
