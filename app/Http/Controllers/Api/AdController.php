<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    AnnouncementService,
    AdService,
    PopAnnouncementService,
    VoucherService
};

use App\Models\{
    Announcement
};

class AdController extends Controller
{
    /**
     * 1. Get all Ads 
     * 
     * @group Ad API
     * 
     */
    public function getAds( Request $request ) {

        return AdService::getAds( $request );
    }

    /**
     * 2. Get one Ad 
     * 
     * @group Ad API
     * 
     */
    public function getAd( Request $request ) {

        return AdService::getAd( $request );
    }

}
