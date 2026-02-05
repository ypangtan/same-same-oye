<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CountryService,
};

class CountryController extends Controller
{

    public function allCountries( Request $request ) {

        return CountryService::allCountries( $request );
    }
}
