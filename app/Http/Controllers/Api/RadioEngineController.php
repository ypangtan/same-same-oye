<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    RadioQueueService,
};

/**
 * Called by the Icecast/Liquidsoap streaming engine (running on the same server), never by the
 * mobile/web app. Protected by the 'radio.engine' middleware (shared-secret header), not user
 * auth — see App\Http\Middleware\VerifyRadioEngineKey.
 */
class RadioEngineController extends Controller
{
    public function next() {
        return RadioQueueService::next();
    }

    public function played( Request $request ) {
        return RadioQueueService::markPlayed( $request );
    }
}
