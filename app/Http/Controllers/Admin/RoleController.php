<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

use Spatie\Permission\Models\{
    Permission,
};

use App\Models\{
    PresetPermission,
    Module,
};

use App\Services\{
    RoleService,
    ModuleService,
};

use Helper;

class RoleController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.roles' );
        $this->data['content'] = 'admin.role.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.roles' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.roles' ) ) ] );
        $this->data['content'] = 'admin.role.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.role.index' ),
                'text' => __( 'template.roles' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.roles' ) ) ] ),
                'class' => 'active',
            ],
        ];

        Helper::initiatePermissions();

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.roles' ) ) ] );
        $this->data['content'] = 'admin.role.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.role.index' ),
                'text' => __( 'template.roles' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.roles' ) ) ] ),
                'class' => 'active',
            ],
        ];

        Helper::initiatePermissions();

        return view( 'admin.main' )->with( $this->data );
    }

    public function allRoles( Request $request ) {

        return RoleService::allRoles( $request );
    }

    public function oneRole( Request $request ) {

        return RoleService::oneRole( $request );
    }

    public function createRole( Request $request ) {

        return RoleService::createRole( $request );
    }

    public function updateRole( Request $request ) {
        
        return RoleService::updateRole( $request );
    }
}
