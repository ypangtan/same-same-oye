<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CategoryService,
    FileService,
    SubscriptionPlanService,
};

class SubscriptionPlanController extends Controller {

    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.subscription_plans' );
        $this->data['content'] = 'admin.subscription_plan.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.subscription_plans' ),
                'class' => 'active',
            ],
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.subscription_plans' ) ) ] );
        $this->data['content'] = 'admin.subscription_plan.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.subscription_plans' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.subscription_plans' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.subscription_plans' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.subscription_plans' ) ) ] );
        $this->data['content'] = 'admin.subscription_plan.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.subscription_plans' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.subscription_plans' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.subscription_plans' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allSubscriptionPlans( Request $request ) {
        return SubscriptionPlanService::allSubscriptionPlans( $request );
    }

    public function oneSubscriptionPlan( Request $request ) {

        return SubscriptionPlanService::oneSubscriptionPlan( $request );
    }

    public function createSubscriptionPlan( Request $request ) {

        return SubscriptionPlanService::createSubscriptionPlan( $request );
    }

    public function updateSubscriptionPlan( Request $request ) {

        return SubscriptionPlanService::updateSubscriptionPlan( $request );
    }

    public function updateSubscriptionPlanStatus( Request $request ) {

        return SubscriptionPlanService::updateSubscriptionPlanStatus( $request );
    }
}
