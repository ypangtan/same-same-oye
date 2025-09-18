<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    AppVersionService,
    BannerService,
    LuckyDrawRewardService
};

class AppVersionController extends Controller
{
    /**
     * 1. Lastest App Version
     * 
     * <aside class="notice">Get Lastest App Version </aside>
     * 
     * <strong>platform</strong><br>
     * 1: App Store<br>
     * 2: Play Store<br>
     * 3: App Gallery<br>
     * 
     * @group App Version API
     * 
     * @bodyParam platform string required The number of the platform. Example: 1
     * 
     */
    public function lastestAppVersion( Request $request ) {

        return AppVersionService::lastestAppVersion( $request );
    }

}
