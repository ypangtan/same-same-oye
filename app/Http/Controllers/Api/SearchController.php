<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Crypt,
    Hash,
    Http,
    Storage
};

use App\Services\SearchService;

class SearchController extends Controller {

    public function __construct() {}

    /**
     * 1. Search
     * 
     * @sort 1
     * 
     * @group Search API
     * 
     * @bodyParam page integer  required The page. Example: 1
     * @bodyParam per_page integer  required The per_page. Example: 10
     * @bodyParam text string required The text for filter. Example: abc
     * @bodyParam category_id string required The category_id of item for filter. Example: 1
     * @bodyParam type_id string required The type_id of item for filter (song, talk, postcast ). Example: 1
     * 
     */
    public function search( Request $request ) {

        return SearchService::search( $request );
    }

}