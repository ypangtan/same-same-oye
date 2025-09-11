<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    OtpLogService,
};

class OtpLogController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.otp_logs' );
        $this->data['content'] = 'admin.otp_log.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.otp_logs' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.otp_logs' ),
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.success' ),
            '20' => __( 'datatables.failed' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allOtpLogs( Request $request ) {

        return OtpLogService::allOtpLogs( $request );
    }

    public function oneOtpLog( Request $request ) {

        return OtpLogService::oneOtpLog( $request );
    }
}
