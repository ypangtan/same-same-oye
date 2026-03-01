<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

use App\Services\{
    CategoryService,
    FileService,
    SubscriptionGroupMemberService,
};

class SubscriptionGroupMemberController extends Controller {

    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.subscription_group_members' );
        $this->data['content'] = 'admin.subscription_group_member.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.subscription_group_members' ),
                'class' => 'active',
            ],
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $userSubscription = $request->id ? UserSubscription::find( $request->id ) : '';

        $this->data['data']['leader'] = $userSubscription ? $userSubscription->user_id : '';

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.subscription_group_members' ) ) ] );
        $this->data['content'] = 'admin.subscription_group_member.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.subscription_group_members' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.subscription_group_members' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.subscription_group_members' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.subscription_group_members' ) ) ] );
        $this->data['content'] = 'admin.subscription_group_member.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.subscription_group_members' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.subscription_group_members' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.subscription_group_members' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allSubscriptionGroupMembers( Request $request ) {
        return SubscriptionGroupMemberService::allSubscriptionGroupMembers( $request );
    }

    public function oneSubscriptionGroupMember( Request $request ) {

        return SubscriptionGroupMemberService::oneSubscriptionGroupMember( $request );
    }

    public function createSubscriptionGroupMember( Request $request ) {

        return SubscriptionGroupMemberService::createSubscriptionGroupMember( $request );
    }

    public function updateSubscriptionGroupMember( Request $request ) {

        return SubscriptionGroupMemberService::updateSubscriptionGroupMember( $request );
    }

    public function deleteSubscriptionGroupMember( Request $request ) {

        return SubscriptionGroupMemberService::deleteSubscriptionGroupMember( $request );
    }
}
