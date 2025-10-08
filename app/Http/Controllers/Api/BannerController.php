<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    AnnouncementService,
    BannerService,
    PopAnnouncementService,
    VoucherService
};

use App\Models\{
    Announcement
};

class BannerController extends Controller
{
    /**
     * 1. Get all Banners 
     * 
     * @group Banner API
     * 
     */
    public function getBanners( Request $request ) {

        return BannerService::getBanners( $request );
    }

    /**
     * 2. Get one Banner 
     * 
     * @group Banner API
     * 
     */
    public function getBanner( Request $request ) {

        return BannerService::getBanner( $request );
    }

}
