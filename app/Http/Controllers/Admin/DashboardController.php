<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    DashboardService,
    UserService,
};

class DashboardController extends Controller
{
    public function index( Request $request ) {
        
        $this->data['header']['title'] = __( 'template.dashboard' );
        $this->data['content'] = 'admin.dashboard.index';
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function getDashboardData( Request $request ) {

        return DashboardService::getDashboardData( $request );
    }

    public function totalRevenueStatistics( Request $request ) {
        return DashboardService::totalRevenueStatistics( $request );
    }

    public function totalReloadStatistics( Request $request ) {
        return DashboardService::totalReloadStatistics( $request );
    }

    public function totalCupsStatistics( Request $request ) {
        return DashboardService::totalCupsStatistics( $request );
    }

    public function totalUserStatistics( Request $request ) {
        return DashboardService::totalUserStatistics( $request );
    }
}
