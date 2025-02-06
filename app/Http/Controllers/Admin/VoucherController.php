<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    VoucherService,
};

class VoucherController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.vouchers' );
        $this->data['content'] = 'admin.voucher.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.vouchers' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
            '21' => __( 'datatables.expired' ),
        ];

        $this->data['data']['voucher_type'] = [
            '1' => __( 'voucher.public_voucher' ),
            '2' => __( 'voucher.user_specific_voucher' ),
            '3' => __( 'voucher.login_reward_voucher' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vouchers' ) ) ] );
        $this->data['content'] = 'admin.voucher.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.voucher.index' ),
                'text' => __( 'template.vouchers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vouchers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'voucher.percentage' ),
            '2' => __( 'voucher.fixed_amount' ),
            '3' => __( 'voucher.free_cup' ),
        ];

        $this->data['data']['voucher_type'] = [
            '1' => __( 'voucher.public_voucher' ),
            '2' => __( 'voucher.user_specific_voucher' ),
            '3' => __( 'voucher.login_reward_voucher' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.vouchers' ) ) ] );
        $this->data['content'] = 'admin.voucher.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.voucher.index' ),
                'text' => __( 'template.vouchers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.vouchers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'voucher.percentage' ),
            '2' => __( 'voucher.fixed_amount' ),
            '3' => __( 'voucher.free_cup' ),
        ];

        $this->data['data']['voucher_type'] = [
            '1' => __( 'voucher.public_voucher' ),
            '2' => __( 'voucher.user_specific_voucher' ),
            '3' => __( 'voucher.login_reward_voucher' ),
        ];


        return view( 'admin.main' )->with( $this->data );
    }

    public function allVouchers( Request $request ) {

        return VoucherService::allVouchers( $request );
    }

    public function oneVoucher( Request $request ) {

        return VoucherService::oneVoucher( $request );
    }

    public function createVoucher( Request $request ) {

        return VoucherService::createVoucher( $request );
    }

    public function updateVoucher( Request $request ) {

        return VoucherService::updateVoucher( $request );
    }

    public function updateVoucherStatus( Request $request ) {

        return VoucherService::updateVoucherStatus( $request );
    }

    public function removeVoucherGalleryImage( Request $request ) {

        return VoucherService::removeVoucherGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return VoucherService::ckeUpload( $request );
    }
    
}
