<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CategoryService,
    FileService,
    UserSubscriptionService,
};

class UserSubscriptionController extends Controller {

    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.user_subscriptions' );
        $this->data['content'] = 'admin.user_subscription.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.user_subscriptions' ),
                'class' => 'active',
            ],
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'user_subscription.active' ),
            '20' => __( 'user_subscription.expired' ),
            '30' => __( 'user_subscription.refunded' ),
            '40' => __( 'user_subscription.cancelled' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.user_subscriptions' ) ) ] );
        $this->data['content'] = 'admin.user_subscription.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.user_subscriptions' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.user_subscriptions' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.user_subscriptions' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.user_subscriptions' ) ) ] );
        $this->data['content'] = 'admin.user_subscription.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.user_subscriptions' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.user_subscriptions' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.user_subscriptions' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allUserSubscriptions( Request $request ) {
        return UserSubscriptionService::allUserSubscriptions( $request );
    }

    public function oneUserSubscription( Request $request ) {

        return UserSubscriptionService::oneUserSubscription( $request );
    }

    public function createUserSubscription( Request $request ) {

        return UserSubscriptionService::createUserSubscription( $request );
    }

    public function updateUserSubscription( Request $request ) {

        return UserSubscriptionService::updateUserSubscription( $request );
    }

    public function updateUserSubscriptionStatus( Request $request ) {

        return UserSubscriptionService::updateUserSubscriptionStatus( $request );
    }
}
