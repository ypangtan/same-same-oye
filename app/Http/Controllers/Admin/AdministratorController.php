<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    AdministratorService,
};

use Illuminate\Support\Facades\{
    DB,
};

class AdministratorController extends Controller
{
    public function login( Request $request ) {

        $data['basic'] = true;
        $data['content'] = 'admin.auth.login';

        return view( 'admin.main_pre_auth' )->with( $data );
    }

    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.administrators' );
        $this->data['content'] = 'admin.administrator.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.administrators' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.administrators' ) ) ] );
        $this->data['content'] = 'admin.administrator.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.administrator.index' ),
                'text' => __( 'template.administrators' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.administrators' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $roles = [];
        foreach( DB::table( 'roles' )->select( 'id', 'name' )->orderBy( 'id', 'ASC' )->get() as $role ) {
            $roles[] = [ 'key' => $role->name, 'value' => $role->id, 'title' => __( 'role.' . $role->name ) ];
        }
        $this->data['data']['roles'] = $roles;

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.administrators' ) ) ] );
        $this->data['content'] = 'admin.administrator.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.administrator.index' ),
                'text' => __( 'template.administrators' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.administrators' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function logout( Request $request ) {

        return AdministratorService::logout( $request );
    }

    public function allAdministrators( Request $request ) {
        return AdministratorService::allAdministrators( $request );
    }

    public function allSalesmen( Request $request ) {

        $request->merge( [
            'role_key' => 'salesman' 
        ] );

        return AdministratorService::allAdministrators( $request );
    }

    public function oneAdministrator( Request $request ) {
        return AdministratorService::oneAdministrator( $request );
    }

    public function createAdministrator( Request $request ) {
        return AdministratorService::createAdministrator( $request );
    }

    public function updateAdministrator( Request $request ) {
        return AdministratorService::updateAdministrator( $request );
    }

    public function oneSalesman( Request $request ) {
        return AdministratorService::updateAdministrator( $request );
    }

    public function createSalesman( Request $request ) {
        return AdministratorService::updateAdministrator( $request );
    }

    public function updateSalesman( Request $request ) {
        return AdministratorService::updateAdministrator( $request );
    }

    public function verify( Request $request ) {

        $value = $request->session()->get( 'mfa-ed' );

        if ( $value ) {
            return redirect()->route( 'admin.dashboard' );
        }
        
        $this->data['header']['title'] = __( 'template.verify_account' );
        
        $this->data['content'] = 'admin.administrator.verify';

        return view( 'admin.main_pre_auth' )->with( $this->data );
    }

    public function verifyCode( Request $request ) {

        return AdministratorService::verifyCode( $request );
    }

    public function indexSalesman( Request $request ) {

        $this->data['header']['title'] = __( 'template.salesmen' );
        $this->data['content'] = 'admin.salesman.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.salesmen' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function addSalesman( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.salesmen' ) ) ] );
        $this->data['content'] = 'admin.salesman.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.administrator.indexSalesman' ),
                'text' => __( 'template.salesmen' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.salesmen' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $roles = [];
        foreach( DB::table( 'roles' )->select( 'id', 'name' )->orderBy( 'id', 'ASC' )->get() as $role ) {
            $roles[] = [ 'key' => $role->name, 'value' => $role->id, 'title' => __( 'role.' . $role->name ) ];
        }
        $this->data['data']['roles'] = $roles;

        return view( 'admin.main' )->with( $this->data );
    }

    public function editSalesman( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.salesmen' ) ) ] );
        $this->data['content'] = 'admin.salesman.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.administrator.indexSalesmen' ),
                'text' => __( 'template.salesmen' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.salesmen' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

}
