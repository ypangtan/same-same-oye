<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    AnnouncementService,
    CollectionService,
    PopAnnouncementService,
    VoucherService
};

use App\Models\{
    Announcement
};

class CollectionController extends Controller
{
    /**
     * 1. Get all Collections 
     * 
     * @group Collection API
     * 
     */
    public function getCollections( Request $request ) {

        return CollectionService::getCollections( $request );
    }

    /**
     * 2. Get one Collection 
     * 
     * @group Collection API
     * 
     */
    public function getCollection( Request $request ) {

        return CollectionService::getCollection( $request );
    }

}
