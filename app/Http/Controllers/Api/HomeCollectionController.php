<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    HomeCollectionService,
};

class HomeCollectionController extends Controller
{
    /**
     * 1. Get Home Collections
     *
     * Returns the collections configured for the home page, in display order.
     * The first collection is shown as "Recommended" by the frontend.
     *
     * @group Home API
     *
     */
    public function getHomeCollections( Request $request ) {

        return HomeCollectionService::getHomeCollections( $request );
    }
}
