<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    RankService,
};

class RankController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.ranks' );
        $this->data['content'] = 'admin.rank.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.ranks' ),
                'class' => 'active',
            ],
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
            '21' => __( 'datatables.expired' ),
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'voucher.percentage' ),
            '2' => __( 'voucher.fixed_amount' ),
            '3' => __( 'voucher.free_cup' ),
        ];

        $this->data['data']['voucher_type'] = [
            '2' => __( 'rank.user_specific_voucher' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.ranks' ) ) ] );
        $this->data['content'] = 'admin.rank.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.rank.index' ),
                'text' => __( 'template.ranks' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.ranks' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'voucher.percentage' ),
            '2' => __( 'voucher.fixed_amount' ),
            '3' => __( 'voucher.free_cup' ),
        ];

        $this->data['data']['voucher_type'] = [
            '2' => __( 'rank.user_specific_voucher' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.ranks' ) ) ] );
        $this->data['content'] = 'admin.rank.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.rank.index' ),
                'text' => __( 'template.ranks' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.ranks' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'voucher.percentage' ),
            '2' => __( 'voucher.fixed_amount' ),
            '3' => __( 'voucher.free_cup' ),
        ];

        $this->data['data']['voucher_type'] = [
            '2' => __( 'rank.user_specific_voucher' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allRanks( Request $request ) {

        return RankService::allRanks( $request );
    }

    public function oneRank( Request $request ) {

        return RankService::oneRank( $request );
    }

    public function createRank( Request $request ) {

        return RankService::createRank( $request );
    }

    public function updateRank( Request $request ) {

        return RankService::updateRank( $request );
    }

    public function updateRankStatus( Request $request ) {

        return RankService::updateRankStatus( $request );
    }

    public function removeRankGalleryImage( Request $request ) {

        return RankService::removeRankGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return RankService::ckeUpload( $request );
    }
    
}
