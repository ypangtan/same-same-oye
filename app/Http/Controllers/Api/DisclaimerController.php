<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    DisclaimerService,
};

class DisclaimerController extends Controller
{

    /**
     * 1. Get Disclaimer 
     * 
     * @group Disclaimer API
     * 
     */
    public function getDisclaimer() {

        return DisclaimerService::getDisclaimer();
    }

}
