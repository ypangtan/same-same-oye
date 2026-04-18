<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    AnnouncementService,
    TrendingContentService,
    PopAnnouncementService,
    VoucherService
};

use App\Models\{
    Announcement
};

class TrendingContentController extends Controller
{
    /**
     * 1. Get all TrendingContents 
     * 
     * @group TrendingContent API
     * 
     * @bodyParam per_page string The total record per page. Example: 10
     * 
     */
    public function getTrendingContents( Request $request ) {

        return TrendingContentService::getTrendingContents( $request );
    }

    /**
     * 2. Get one TrendingContent 
     * 
     * @group TrendingContent API
     * 
     * @bodyParam id string The encrypted_id of the trending_content. Example: 52
     * 
     */
    public function getTrendingContent( Request $request ) {

        return TrendingContentService::getTrendingContent( $request );
    }

}
