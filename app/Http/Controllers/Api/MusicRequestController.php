<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    AppVersionService,
    BannerService,
    LuckyDrawRewardService,
    MusicRequestService
};

class MusicRequestController extends Controller
{
    /**
     * 1. Create Music Request
     * 
     * <aside class="notice">Create Music Request </aside>
     * 
     * @group Music Request API
     * 
     * @bodyParam name string required The name of the song. Example: abc
     * 
     */
    public function createMusicRequest( Request $request ) {

        return MusicRequestService::createMusicRequest( $request );
    }

}
