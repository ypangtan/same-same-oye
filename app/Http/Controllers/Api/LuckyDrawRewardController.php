<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    BannerService,
    LuckyDrawRewardService
};

class LuckyDrawRewardController extends Controller
{
    /**
     * 1. Search Lucky Draw Reward
     * 
     * <aside class="notice">Search Lucky Draw Reward </aside>
     * 
     * @authenticated
     * 
     * @group Lucky Draw Reward API
     * 
     * @bodyParam customer_member_id string required The customer member id of the lucky draw rewards. Example: abc1234
     * 
     */
    public function searchLuckyDrawRewards( Request $request ) {

        return LuckyDrawRewardService::searchLuckyDrawRewards( $request );
    }

}
