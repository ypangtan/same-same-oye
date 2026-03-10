<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    WebsiteBannerService,
};

use App\Models\{
    WebsiteBanner,
};

class WebsiteBannerController extends Controller
{

    public function updateOrder( Request $request ) {
        foreach ( $request->order as $index => $id ) {
            WebsiteBanner::where( 'id', $id )->update( [ 'sequence' => $index ] );
        }
        return response()->json( [ 'success' => true ] );
    }
    
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.website_banners' );
        $this->data['content'] = 'admin.website_banner.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.website_banners' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
            '21' => __( 'datatables.expired' ),
        ];
        
        $this->data['data']['website_banners'] = WebsiteBanner::where( 'status', 10 )->orderBy( 'sequence' )->get();

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.website_banners' ) ) ] );
        $this->data['content'] = 'admin.website_banner.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.website_banner.index' ),
                'text' => __( 'template.website_banners' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.website_banners' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'website_banner.percentage' ),
            '2' => __( 'website_banner.fixed_amount' ),
            '3' => __( 'website_banner.free_cup' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.website_banners' ) ) ] );
        $this->data['content'] = 'admin.website_banner.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.website_banner.index' ),
                'text' => __( 'template.website_banners' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.website_banners' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'website_banner.percentage' ),
            '2' => __( 'website_banner.fixed_amount' ),
            '3' => __( 'website_banner.free_cup' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allWebsiteBanners( Request $request ) {

        return WebsiteBannerService::allWebsiteBanners( $request );
    }

    public function oneWebsiteBanner( Request $request ) {

        return WebsiteBannerService::oneWebsiteBanner( $request );
    }

    public function createWebsiteBanner( Request $request ) {

        return WebsiteBannerService::createWebsiteBanner( $request );
    }

    public function updateWebsiteBanner( Request $request ) {

        return WebsiteBannerService::updateWebsiteBanner( $request );
    }

    public function updateWebsiteBannerStatus( Request $request ) {

        return WebsiteBannerService::updateWebsiteBannerStatus( $request );
    }

    public function removeWebsiteBannerGalleryImage( Request $request ) {

        return WebsiteBannerService::removeWebsiteBannerGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return WebsiteBannerService::ckeUpload( $request );
    }

    public function deleteWebsiteBanner( Request $request ) {

        return WebsiteBannerService::deleteWebsiteBanner( $request );
    }

    public function updateWebsiteBannerUrl( Request $request ) {
        return WebsiteBannerService::updateWebsiteBannerUrl( $request );
    } 
    
}
