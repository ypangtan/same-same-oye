<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    EghlService,
};

use Helper;

class PaymentController extends Controller
{
    public function callback( Request $request ) {

        echo 'Close This';
    }

    public function initEghl( Request $request ) {

        return EghlService::init( $request );
    }

    public function notifyEghl( Request $request ) {

        return EghlService::notify( $request );
    }

    public function queryEghl( Request $request ) {

        return EghlService::query( $request );
    }

    public function callbackEghl( Request $request ) {

        return EghlService::callback( $request );
    }

    public function fallbackEghl( Request $request ) {

        return EghlService::fallback( $request );
    }

    public function success( Request $request ) {

        return response()->json( [
            'message' => '',
            'message_key' => 'order_placed',
            'data' => [
                'status' => true
            ],
        ] );
    }

    public function failed( Request $request ) {

        return response()->json( [
            'message' => '',
            'message_key' => 'order_failed_you_may_retry_payment',
            'data' => [
                'status' => true
            ],
        ] );
    }
}
