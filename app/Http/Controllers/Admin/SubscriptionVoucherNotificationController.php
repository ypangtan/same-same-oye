<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    SubscriptionVoucherService,
};

use App\Models\{
    SubscriptionVoucher,
};

class SubscriptionVoucherNotificationController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.subscription_voucher_notifications' );
        $this->data['content'] = 'admin.subscription_voucher_notification.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.module_parent.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.subscription_voucher_notifications' ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['status'] = [
            '1' => __( 'datatables.scheduled' ),
            '10' => __( 'subscription_voucher.published' ),
            '20' => __( 'subscription_voucher.unpublished' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.subscription_voucher_notifications' ) ) ] );
        $this->data['content'] = 'admin.subscription_voucher_notification.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.module_parent.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.subscription_voucher_notification.index' ),
                'text' => __( 'template.subscription_voucher_notifications' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.subscription_voucher_notifications' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['vouchers'] = SubscriptionVoucher::withCount( [ 'codes' => function( $query ) {
            $query->used();
        } ] )
            ->where( 'status', 10 )
            ->orderBy( 'created_at', 'DESC' )
            ->get();

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.subscription_voucher_notifications' ) ) ] );
        $this->data['content'] = 'admin.subscription_voucher_notification.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.module_parent.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.subscription_voucher_notification.index' ),
                'text' => __( 'template.subscription_voucher_notifications' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.subscription_voucher_notifications' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['code_status'] = [
            '1' => __( 'subscription_voucher.available' ),
            '10' => __( 'subscription_voucher.claimed' ),
            '20' => __( 'subscription_voucher.voided' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allVoucherNotifications( Request $request ) {

        return SubscriptionVoucherService::allVoucherNotifications( $request );
    }

    public function oneVoucherNotification( Request $request ) {

        return SubscriptionVoucherService::oneVoucherNotification( $request );
    }

    public function createVoucherNotification( Request $request ) {

        return SubscriptionVoucherService::createVoucherNotification( $request );
    }

    public function updateVoucherNotification( Request $request ) {

        return SubscriptionVoucherService::updateVoucherNotification( $request );
    }

    public function updateVoucherNotificationStatus( Request $request ) {

        return SubscriptionVoucherService::updateVoucherNotificationStatus( $request );
    }
}
