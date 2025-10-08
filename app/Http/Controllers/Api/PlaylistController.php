<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    AnnouncementService,
    PlaylistService,
    PopAnnouncementService,
    VoucherService
};

use App\Models\{
    Announcement
};

class PlaylistController extends Controller
{
    /**
     * 1. Get all Playlists 
     * 
     * @group Playlist API
     * 
     */
    public function getPlaylists( Request $request ) {

        return PlaylistService::getPlaylists( $request );
    }

    /**
     * 2. Get one Playlist 
     * 
     * @group Playlist API
     * 
     */
    public function getPlaylist( Request $request ) {

        return PlaylistService::getPlaylist( $request );
    }

}
