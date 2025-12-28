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
    TypeService,
    VoucherService
};

use App\Models\{
    Announcement
};

class TypeController extends Controller
{
    /**
     * 1. Get all Types 
     * 
     * @group Type API
     * 
     * @bodyParam per_page string The total record per page. Example: 10
     * 
     */
    public function getTypes( Request $request ) {

        return TypeService::getTypes( $request );
    }

}
