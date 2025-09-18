<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    AppVersionService,
};

class AppVersionController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.app_versions' );
        $this->data['content'] = 'admin.app_version.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.app_versions' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.app_versions' ),
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['platform'] = [
            '1' => __( 'app_version.app_store' ),
            '2' => __( 'app_version.play_store' ),
            '3' => __( 'app_version.app_gallery' ),
        ];

        $this->data['data']['force_logout'] = [
            '10' => __( 'app_version.true' ),
            '20' => __( 'app_version.false' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.app_versions' );
        $this->data['content'] = 'admin.app_version.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.app_versions' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.app_versions' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.app_versions' ) ) ] ),
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['platform'] = [
            '1' => __( 'app_version.app_store' ),
            '2' => __( 'app_version.play_store' ),
            '3' => __( 'app_version.app_gallery' ),
        ];

        $this->data['data']['force_logout'] = [
            '10' => __( 'app_version.true' ),
            '20' => __( 'app_version.false' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.app_versions' );
        $this->data['content'] = 'admin.app_version.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.app_versions' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.app_versions' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.app_versions' ) ) ] ),
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['platform'] = [
            '1' => __( 'app_version.app_store' ),
            '2' => __( 'app_version.play_store' ),
            '3' => __( 'app_version.app_gallery' ),
        ];

        $this->data['data']['force_logout'] = [
            '10' => __( 'app_version.true' ),
            '20' => __( 'app_version.false' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allAppVersions( Request $request ) {

        return AppVersionService::allAppVersions( $request );
    }

    public function oneAppVersion( Request $request ) {

        return AppVersionService::oneAppVersion( $request );
    }

    public function createAppVersion( Request $request ) {

        return AppVersionService::createAppVersion( $request );
    }

    public function updateAppVersion( Request $request ) {

        return AppVersionService::updateAppVersion( $request );
    }

    public function updateAppVersionStatus( Request $request ) {

        return AppVersionService::updateAppVersionStatus( $request );
    }
}
