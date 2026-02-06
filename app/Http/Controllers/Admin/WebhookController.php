<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AndroidCallbackService;
use App\Services\IosCallbackService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

class WebhookController extends Controller
{
    public function ios( Request $request ) {
        return IosCallbackService::callbackIos( $request );
    }

    public function android( Request $request ) {
        return AndroidCallbackService::callbackAndroid( $request );
    }
}
