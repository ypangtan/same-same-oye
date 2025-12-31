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
     * @bodyParam type_id string The encrypted_id of the type. Example: 52
     * @bodyParam collection_id string The encrypted_id of the collection. Example: 1
     * @bodyParam category_id string The encrypted_id of the category. Example: 52
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
     * @bodyParam id string The encrypted_id of the playlist. Example: 52
     * 
     */
    public function getPlaylist( Request $request ) {

        return PlaylistService::getPlaylist( $request );
    }

}
