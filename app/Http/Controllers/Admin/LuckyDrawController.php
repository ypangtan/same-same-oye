<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    LuckyDrawRewardService,
};

class LuckyDrawController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.lucky_draw_rewards' );
        $this->data['content'] = 'admin.lucky_draw_reward.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.lucky_draw_rewards' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.lucky_draw_rewards' ),
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.lucky_draw_rewards' );
        $this->data['content'] = 'admin.lucky_draw_reward.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.lucky_draw_rewards' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.lucky_draw_rewards' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.lucky_draw_rewards' ) ) ] ),
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.lucky_draw_rewards' );
        $this->data['content'] = 'admin.lucky_draw_reward.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.lucky_draw_rewards' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.lucky_draw_rewards' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.lucky_draw_rewards' ) ) ] ),
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function import( Request $request ) {

        $this->data['header']['title'] = __( 'template.lucky_draw_rewards' );
        $this->data['content'] = 'admin.lucky_draw_reward.import';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.lucky_draw_rewards' ),
            'title' => __( 'template.import_x', [ 'title' => \Str::singular( __( 'template.lucky_draw_rewards' ) ) ] ),
            'mobile_title' => __( 'template.import_x', [ 'title' => \Str::singular( __( 'template.lucky_draw_rewards' ) ) ] ),
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allLuckyDrawRewards( Request $request ) {

        return LuckyDrawRewardService::allLuckyDrawRewards( $request );
    }

    public function oneLuckyDrawReward( Request $request ) {

        return LuckyDrawRewardService::oneLuckyDrawReward( $request );
    }

    public function createLuckyDrawReward( Request $request ) {

        return LuckyDrawRewardService::createLuckyDrawReward( $request );
    }

    public function updateLuckyDrawReward( Request $request ) {

        return LuckyDrawRewardService::updateLuckyDrawReward( $request );
    }

    public function updateLuckyDrawRewardStatus( Request $request ) {

        return LuckyDrawRewardService::updateLuckyDrawRewardStatus( $request );
    }

    public function importLuckyDrawReward( Request $request ) {

        return LuckyDrawRewardService::importLuckyDrawReward( $request );
    }

    public function importLuckyDrawRewardV2( Request $request ) {

        return LuckyDrawRewardService::importLuckyDrawRewardV2( $request );
    }
}
