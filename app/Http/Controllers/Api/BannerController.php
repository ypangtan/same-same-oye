<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    BannerService
};

class BannerController extends Controller
{
    /**
     * 1. Get banners 
     * 
     * <aside class="notice">Get all banners ( sorted )</aside>
     * 
     * @authenticated
     * 
     * @group Banner API
     * 
     */
    public function getBanners( Request $request ) {

        return BannerService::getBanners( $request );
    }

    /**
     * 2. Get one banner detail
     * 
     * @group Banner API
     * 
     * @bodyParam id string required The id the banner. Example: 1
     * 
     */ 
    public function oneBanner( Request $request ) {
        
        return BannerService::oneBannerClient( $request );
    }
}
