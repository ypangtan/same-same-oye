<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    AnnouncementService,
    WebsiteBannerService,
    PopAnnouncementService,
    VoucherService
};

use App\Models\{
    Announcement
};

class WebsiteBannerController extends Controller
{
    /**
     * 1. Get all Website Banners 
     * 
     * @group Website Banner API
     * 
     */
    public function getWebsiteBanners( Request $request ) {

        return WebsiteBannerService::getWebsiteBanners( $request );
    }

    /**
     * 2. Get one Website Banner 
     * 
     * @group Website Banner API
     * 
     * @bodyParam id string The encrypted_id of the website banner. Example: 52
     * 
     */
    public function getWebsiteBanner( Request $request ) {

        return WebsiteBannerService::oneWebsiteBannerClient( $request );
    }

}
