<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    UserVoucherService,
};

class UserVoucherController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.user_vouchers' );
        $this->data['content'] = 'admin.user_voucher.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.user_vouchers' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'voucher.active' ),
            '20' => __( 'voucher.used' ),
            '21' => __( 'voucher.expired' ),
        ];

        $this->data['data']['voucher_type'] = [
            '1' => __( 'user_voucher.public_voucher' ),
            '2' => __( 'user_voucher.user_specific_voucher' ),
            '3' => __( 'user_voucher.login_reward_voucher' ),
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'voucher.percentage' ),
            '2' => __( 'voucher.fixed_amount' ),
            '3' => __( 'voucher.free_cup' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.user_vouchers' ) ) ] );
        $this->data['content'] = 'admin.user_voucher.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.user_voucher.index' ),
                'text' => __( 'template.user_vouchers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.user_vouchers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.user_vouchers' ) ) ] );
        $this->data['content'] = 'admin.user_voucher.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.user_voucher.index' ),
                'text' => __( 'template.user_vouchers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.user_vouchers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allUserVouchers( Request $request ) {

        return UserVoucherService::allUserVouchers( $request );
    }

    public function oneUserVoucher( Request $request ) {

        return UserVoucherService::oneUserVoucher( $request );
    }

    public function createUserVoucher( Request $request ) {

        return UserVoucherService::createUserVoucher( $request );
    }

    public function updateUserVoucher( Request $request ) {

        return UserVoucherService::updateUserVoucher( $request );
    }

    public function updateUserVoucherStatus( Request $request ) {

        return UserVoucherService::updateUserVoucherStatus( $request );
    }

    public function removeUserVoucherGalleryImage( Request $request ) {

        return UserVoucherService::removeUserVoucherGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return UserVoucherService::ckeUpload( $request );
    }
    
}
