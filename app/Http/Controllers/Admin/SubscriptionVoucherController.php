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

class SubscriptionVoucherController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.subscription_vouchers' );
        $this->data['content'] = 'admin.subscription_voucher.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.module_parent.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.subscription_vouchers' ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['type'] = self::types();

        return view( 'admin.main' )->with( $this->data );
    }

    public static function types() {
        return [
            SubscriptionVoucher::TYPE_SUBSCRIPTION => __( 'subscription_voucher.type_subscription' ),
            SubscriptionVoucher::TYPE_THIRD_PARTY => __( 'subscription_voucher.type_third_party' ),
        ];
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.subscription_vouchers' ) ) ] );
        $this->data['content'] = 'admin.subscription_voucher.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.module_parent.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.subscription_voucher.index' ),
                'text' => __( 'template.subscription_vouchers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.subscription_vouchers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['type'] = self::types();

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.subscription_vouchers' ) ) ] );
        $this->data['content'] = 'admin.subscription_voucher.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.module_parent.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.subscription_voucher.index' ),
                'text' => __( 'template.subscription_vouchers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.subscription_vouchers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['type'] = self::types();

        return view( 'admin.main' )->with( $this->data );
    }

    public function codes( Request $request ) {

        $this->data['header']['title'] = __( 'subscription_voucher.voucher_codes' );
        $this->data['content'] = 'admin.subscription_voucher.codes';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.module_parent.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.subscription_voucher.index' ),
                'text' => __( 'template.subscription_vouchers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'subscription_voucher.voucher_codes' ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['status'] = [
            '1' => __( 'subscription_voucher.available' ),
            '10' => __( 'subscription_voucher.claimed' ),
            '20' => __( 'subscription_voucher.voided' ),
        ];

        $this->data['data']['voucher'] = $request->voucher ? SubscriptionVoucher::find( \Helper::decode( $request->voucher ) ) : null;

        // Third party voucher has no code
        if ( $this->data['data']['voucher'] && $this->data['data']['voucher']->isThirdParty() ) {
            return redirect()->route( 'admin.subscription_voucher.urls', [ 'voucher' => $request->voucher ] );
        }

        return view( 'admin.main' )->with( $this->data );
    }

    public function urls( Request $request ) {

        $this->data['header']['title'] = __( 'subscription_voucher.voucher_urls' );
        $this->data['content'] = 'admin.subscription_voucher.urls';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.module_parent.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.subscription_voucher.index' ),
                'text' => __( 'template.subscription_vouchers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'subscription_voucher.voucher_urls' ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $voucher = $request->voucher ? SubscriptionVoucher::find( \Helper::decode( $request->voucher ) ) : null;

        if ( !$voucher || !$voucher->isThirdParty() ) {
            abort( 404 );
        }

        $this->data['data']['voucher'] = $voucher;

        return view( 'admin.main' )->with( $this->data );
    }

    public function allSubscriptionVouchers( Request $request ) {

        return SubscriptionVoucherService::allSubscriptionVouchers( $request );
    }

    public function oneSubscriptionVoucher( Request $request ) {

        return SubscriptionVoucherService::oneSubscriptionVoucher( $request );
    }

    public function createSubscriptionVoucher( Request $request ) {

        return SubscriptionVoucherService::createSubscriptionVoucher( $request );
    }

    public function updateSubscriptionVoucher( Request $request ) {

        return SubscriptionVoucherService::updateSubscriptionVoucher( $request );
    }

    public function updateSubscriptionVoucherStatus( Request $request ) {

        return SubscriptionVoucherService::updateSubscriptionVoucherStatus( $request );
    }

    public function allSubscriptionVoucherCodes( Request $request ) {

        return SubscriptionVoucherService::allSubscriptionVoucherCodes( $request );
    }

    public function updateSubscriptionVoucherCodeStatus( Request $request ) {

        return SubscriptionVoucherService::updateSubscriptionVoucherCodeStatus( $request );
    }

    public function allSubscriptionVoucherUrls( Request $request ) {

        return SubscriptionVoucherService::allSubscriptionVoucherUrls( $request );
    }

    public function createSubscriptionVoucherUrls( Request $request ) {

        return SubscriptionVoucherService::createSubscriptionVoucherUrls( $request );
    }

    public function updateSubscriptionVoucherUrls( Request $request ) {

        return SubscriptionVoucherService::updateSubscriptionVoucherUrls( $request );
    }

    public function updateSubscriptionVoucherUrlStatus( Request $request ) {

        return SubscriptionVoucherService::updateSubscriptionVoucherUrlStatus( $request );
    }

    public function deleteSubscriptionVoucherUrls( Request $request ) {

        return SubscriptionVoucherService::deleteSubscriptionVoucherUrls( $request );
    }
}
