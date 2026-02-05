<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    UserService,
};

class UserController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.users' );
        $this->data['content'] = 'admin.user.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.users' ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['user_social'] = [
            '1' => __( 'user.google' ),
            '2' => __( 'user.facebook' ),
            '3' => __( 'user.apple_id' ),
        ];

        $this->data['data']['membership'] = [
            '0' => __( 'user.member' ),
            '1' => __( 'user.premium' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function myFriend( Request $request ) {

        $this->data['header']['title'] = __( 'template.my_friends' );
        $this->data['content'] = 'admin.user.my_friend';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.user.index' ),
                'text' => __( 'template.users' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.my_friends' ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['user_social'] = [
            '1' => __( 'user.google' ),
            '2' => __( 'user.facebook' ),
            '3' => __( 'user.apple_id' ),
        ];

        $this->data['data']['rank'] = [
            '1' => __( 'rank.member' ),
            '2' => __( 'rank.silver' ),
            '3' => __( 'rank.gold' ),
            '4' => __( 'rank.premium' ),
        ];

        $this->data['data']['membership'] = [
            '0' => __( 'user.member' ),
            '1' => __( 'user.premium' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.users' ) ) ] );
        $this->data['content'] = 'admin.user.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.user.index' ),
                'text' => __( 'template.users' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.users' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['membership'] = [
            '0' => __( 'user.member' ),
            '1' => __( 'user.premium' ),
        ];

        $this->data['data']['age_group'] = UserService::ageGroups();

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.users' ) ) ] );
        $this->data['content'] = 'admin.user.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.user.index' ),
                'text' => __( 'template.users' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.users' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['membership'] = [
            '0' => __( 'user.member' ),
            '1' => __( 'user.premium' ),
        ];

        $this->data['data']['age_group'] = UserService::ageGroups();

        return view( 'admin.main' )->with( $this->data );
    }

    public function allUsers( Request $request ) {

        return UserService::allUsers( $request );
    }

    public function oneUserDownlines( Request $request ) {

        return UserService::oneUserDownlines( $request );
    }

    public function oneUser( Request $request ) {

        return UserService::oneUser( $request );
    }

    public function createUser( Request $request ) {

        return UserService::createUser( $request );
    }

    public function updateUser( Request $request ) {

        return UserService::updateUser( $request );
    }

    public function updateUserStatus( Request $request ) {

        return UserService::updateUserStatus( $request );
    }
}
