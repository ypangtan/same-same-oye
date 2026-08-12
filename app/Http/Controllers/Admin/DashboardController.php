<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index() {
        $this->data['header']['title'] = __( 'template.dashboard' );
        $this->data['content'] = 'admin.module_parent.dashboard.index';
        return view( 'admin.main' )->with( $this->data );
    }

    public function getEngagementStats() {
        return DashboardService::getEngagementStats();
    }

    public function getEngagementDetail( Request $request ) {
        return DashboardService::getEngagementDetail( $request );
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

    public function getStreamDetail( Request $request ) {
        return DashboardService::getContentStreamDetail( $request );
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

    public function getBannerClickDetail( Request $request ) {
        return DashboardService::getBannerClickDetail( $request );
    }

    public function getPopAnnouncementClickDetail( Request $request ) {
        return DashboardService::getPopAnnouncementClickDetail( $request );
    }

    public function getAppAnalytics( Request $request ) {
        return DashboardService::getAppAnalytics( $request );
    }

    public function streamPage( Request $request ) {
        $this->data['header']['title'] = __( 'template.dashboard' );
        $this->data['content']         = 'admin.module_parent.dashboard.stream';
        $this->data['activePage']      = $request->query( 'page', 'radio' );
        return view( 'admin.main' )->with( $this->data );
    }
}
