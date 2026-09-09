<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Services\{
    RadioQueueService,
};

/**
 * Public, app-facing radio info — separate from RadioEngineController (which only the
 * Liquidsoap engine talks to, guarded by a shared secret). Nothing here is sensitive, so it
 * needs no auth: just what's currently on air, for the app's player screen to show alongside
 * the audio stream (title + cover image, since the stream itself is just raw audio bytes and
 * can't carry that).
 */
class RadioController extends Controller
{
    public function nowPlaying() {
        return RadioQueueService::nowPlaying();
    }
}
