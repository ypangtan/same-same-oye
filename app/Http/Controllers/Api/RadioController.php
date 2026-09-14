<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Services\{
    RadioQueueService,
};

class RadioController extends Controller
{
    
    /**
     * 1. Get Now Playing 
     * 
     * @group Radio API
     * 
     */
    public function nowPlaying() {
        return RadioQueueService::publicNowPlaying();
    }
}
