<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    FestiveAnimationService,
};

class FestiveAnimationController extends Controller
{
    /**
     * 1. Get all Festive Animations & Advertisements
     *
     * Only returns active records within their start / end date window, ordered by sequence.
     * file_type: 1 = image, 2 = gif, 3 = video, 4 = other
     *
     * @group Festive Animation API
     *
     * @bodyParam file_type integer Filter by file type. Example: 3
     *
     */
    public function getFestiveAnimations( Request $request ) {

        return FestiveAnimationService::getFestiveAnimations( $request );
    }

    /**
     * 2. Get one Festive Animation & Advertisement
     *
     * @group Festive Animation API
     *
     * @bodyParam id string required The encrypted_id of the festive animation. Example: 52
     *
     */
    public function getFestiveAnimation( Request $request ) {

        return FestiveAnimationService::getFestiveAnimation( $request );
    }
}
