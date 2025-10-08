<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    AnnouncementService,
    ItemService,
    PopAnnouncementService,
    VoucherService
};

use App\Models\{
    Announcement
};

class ItemController extends Controller
{
    /**
     * 1. Get all Items 
     * 
     * @group Item API
     * 
     */
    public function getItems( Request $request ) {

        return ItemService::getItems( $request );
    }

    /**
     * 2. Get one Item 
     * 
     * @group Item API
     * 
     */
    public function getItem( Request $request ) {

        return ItemService::getItem( $request );
    }

}
