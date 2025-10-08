<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    BannerService,
    FileManagerService,
    FileService,
};

class BannerController extends Controller
{
    public function index() {

        $this->data['header']['title'] = __( 'template.banners' );
        $this->data['content'] = 'admin.banner.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.banners' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.banners' ),
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );   
    }

    public function add() {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.banners' ) ) ] );
        $this->data['content'] = 'admin.banner.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.banners' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.banners' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.banners' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );  
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.banners' ) ) ] );
        $this->data['content'] = 'admin.banner.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.banners' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.banners' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.banners' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );  
    }

    public function allBanners( Request $request ) {
        return BannerService::allBanners( $request );
    }

    public function oneBanner( Request $request ) {
        return BannerService::oneBanner( $request );
    }

    public function createBanner( Request $request ) {
        return BannerService::createBanner( $request );
    }

    public function updateBanner( Request $request ) {
        return BannerService::updateBanner( $request );
    }

    public function updateBannerStatus( Request $request ) {
        return BannerService::updateBannerStatus( $request );
    }

    public function ckeUpload( Request $request ) {

        $request->merge( [
            'source' => 'ckeditor/banner'
        ] );

        return FileService::ckeUpload( $request );
    }
}
