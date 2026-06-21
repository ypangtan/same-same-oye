<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.dashboard' );
        $this->data['content'] = 'admin.dashboard.index';

        return view( 'admin.main' )->with( $this->data );
    }

    public function getEngagementStats( Request $request ) {
        return DashboardService::getEngagementStats( $request );
    }

    public function getDailyUserStats( Request $request ) {
        return DashboardService::getDailyUserStats( $request );
    }

    public function getBannerClickStats( Request $request ) {
        return DashboardService::getBannerClickStats( $request );
    }

    public function getPopAnnouncementClickStats( Request $request ) {
        return DashboardService::getPopAnnouncementClickStats( $request );
    }
}
