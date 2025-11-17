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
     * @bodyParam per_page string The total record per page. Example: 10
     * @bodyParam category_id string The encrypted_id of the category. Example: 1
     * @bodyParam collection_id string The encrypted_id of the collection. Example: 1
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
