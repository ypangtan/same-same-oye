<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    BannerService,
};

use App\Models\{
    Banner,
};

class BannerController extends Controller
{

    public function updateOrder( Request $request ) {
        foreach ( $request->order as $index => $id ) {
            Banner::where( 'id', $id )->update( [ 'sequence' => $index ] );
        }
        return response()->json( [ 'success' => true ] );
    }
    
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.banners' );
        $this->data['content'] = 'admin.banner.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.banners' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
            '21' => __( 'datatables.expired' ),
        ];
        
        $this->data['data']['banners'] = Banner::where( 'status', 10 )->orderBy( 'sequence' )->get();

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.banners' ) ) ] );
        $this->data['content'] = 'admin.banner.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.banner.index' ),
                'text' => __( 'template.banners' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.banners' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'banner.percentage' ),
            '2' => __( 'banner.fixed_amount' ),
            '3' => __( 'banner.free_cup' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.banners' ) ) ] );
        $this->data['content'] = 'admin.banner.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.banner.index' ),
                'text' => __( 'template.banners' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.banners' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'banner.percentage' ),
            '2' => __( 'banner.fixed_amount' ),
            '3' => __( 'banner.free_cup' ),
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

    public function removeBannerGalleryImage( Request $request ) {

        return BannerService::removeBannerGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return BannerService::ckeUpload( $request );
    }

    public function deleteBanner( Request $request ) {

        return BannerService::deleteBanner( $request );
    }

    public function updateBannerUrl( Request $request ) {
        return BannerService::updateBannerUrl( $request );
    } 
    
}
