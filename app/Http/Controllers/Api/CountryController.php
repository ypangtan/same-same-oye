<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    CountryService,
};

use App\Models\{
    Announcement
};

class CountryController extends Controller
{
    /**
     * 1. Get all Countries 
     * 
     * @group Country API
     * 
     * @bodyParam per_page string The total record per page. Example: 10
     * 
     */
    public function getCountries( Request $request ) {

        return CountryService::getCountries( $request );
    }
}
