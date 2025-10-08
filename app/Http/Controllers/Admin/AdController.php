<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    AdService,
    FileManagerService,
    FileService,
};

class AdController extends Controller
{
    public function index() {

        $this->data['header']['title'] = __( 'template.ads' );
        $this->data['content'] = 'admin.ad.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.ads' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.ads' ),
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );   
    }

    public function add() {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.ads' ) ) ] );
        $this->data['content'] = 'admin.ad.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.ads' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.ads' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.ads' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );  
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.ads' ) ) ] );
        $this->data['content'] = 'admin.ad.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.ads' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.ads' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.ads' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );  
    }

    public function updateOrder( Request $request ) {
        return AdService::updateOrder( $request );
    }

    public function allAds( Request $request ) {
        return AdService::allAds( $request );
    }

    public function oneAd( Request $request ) {
        return AdService::oneAd( $request );
    }

    public function createAd( Request $request ) {
        return AdService::createAd( $request );
    }

    public function updateAd( Request $request ) {
        return AdService::updateAd( $request );
    }

    public function updateAdStatus( Request $request ) {
        return AdService::updateAdStatus( $request );
    }

    public function ckeUpload( Request $request ) {

        $request->merge( [
            'source' => 'ckeditor/ad'
        ] );

        return FileService::ckeUpload( $request );
    }
}
