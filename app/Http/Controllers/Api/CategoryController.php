<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    AnnouncementService,
    CategoryService,
    PlaylistService,
    PopAnnouncementService,
    VoucherService
};

use App\Models\{
    Announcement
};

class CategoryController extends Controller
{
    /**
     * 1. Get all Categories 
     * 
     * @group Category API
     * 
     * @bodyParam per_page string The total record per page. Example: 10
     * @bodyParam type_id string The encrypted_id of the type. Example: 52
     * 
     */
    public function getCategories( Request $request ) {

        return CategoryService::getCategories( $request );
    }
}
