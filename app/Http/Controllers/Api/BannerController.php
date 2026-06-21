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
    Announcement,
    Banner,
    BannerClick,
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
     * @bodyParam id string The encrypted_id of the banner. Example: 52
     * 
     */
    public function getBanner( Request $request ) {

        return BannerService::oneBannerClient( $request );
    }

    public function recordClick( Request $request ) {

        $banner = Banner::find( $request->id );

        if ( !$banner ) {
            return response()->json( [ 'message' => 'Banner not found.' ], 404 );
        }

        BannerClick::create( [
            'banner_id' => $banner->id,
            'user_id'   => auth()->id() ?? null,
        ] );

        return response()->json( [ 'message' => 'Click recorded.' ] );
    }

}
