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
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.ranks' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.ranks' ),
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.ranks' );
        $this->data['content'] = 'admin.rank.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.ranks' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.ranks' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.ranks' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.ranks' );
        $this->data['content'] = 'admin.rank.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.ranks' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.ranks' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.ranks' ) ) ] ),
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
}
