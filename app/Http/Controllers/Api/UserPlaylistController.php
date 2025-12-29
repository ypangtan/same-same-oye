<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Crypt,
    Hash,
    Http,
    Storage
};

use App\Services\{
    UserPlaylistService,
};

use Helper;

class UserPlaylistController extends Controller {

    /**
     * 1. Get User Playlists
     * 
     * @sort 1
     * 
     * @authenticated
     * 
     * @group UserPlaylist API
     * 
     * @bodyParam per_page string The total record per page. Example: 10
     * @bodyParam type string The id of type for user playlist. Example: 1
     */
    public function getUserPlaylists( Request $request ) {

        return UserPlaylistService::getUserPlaylists( $request );
    }

    /**
     * 2. Get User Playlist Detail
     * 
     * @sort 2
     * 
     * @authenticated
     * 
     * @group UserPlaylist API
     * 
     * @bodyParam id string The encrypted_id of user playlist. Example: 10
     * 
     */
    public function getUserPlaylist( Request $request ) {

        return UserPlaylistService::getUserPlaylist( $request );
    }

    /**
     * 3. Create User PlayList
     * 
     * @sort 3
     * 
     * @authenticated
     * 
     * @group UserPlaylist API
     * 
     * @bodyParam name string The name of user playlist. Example: abc
     * @bodyParam type string The id of type for user playlist. Example: 1
     * 
     */
    public function createUserPlayList( Request $request ) {

        return UserPlaylistService::createUserPlayList( $request );
    }

    /**
     * 4. Update User PlayList
     * 
     * @sort 4
     * 
     * @authenticated
     * 
     * @group UserPlaylist API
     * 
     * @bodyParam id string The encrypted_id of user playlist. Example: 10
     * @bodyParam name string The name of user playlist. Example: abc
     * @bodyParam type string The id of type for user playlist. Example: 1
     * 
     */
    public function updateUserPlayList( Request $request ) {

        return UserPlaylistService::updateUserPlayList( $request );
    }

    /**
     * 5. Delete User PlayList
     * 
     * @sort 5
     * 
     * @authenticated
     * 
     * @group UserPlaylist API
     * 
     * @bodyParam id string The encrypted_id of user playlist. Example: 10
     * 
     */
    public function deleteUserPlayList( Request $request ) {

        return UserPlaylistService::deleteUserPlayList( $request );
    }

    /**
     * 6. Add Song To User PlayList
     * 
     * @sort 6
     * 
     * @authenticated
     * 
     * @group UserPlaylist API
     * 
     * @bodyParam user_playlist_id string The encrypted_id of user playlist. Example: 10
     * @bodyParam song_id string The encrypted_id of song. Example: 10
     * 
     */
    public function addSongToUserPlayList( Request $request ) {

        return UserPlaylistService::addSongToUserPlayList( $request );
    }

    /**
     * 7. Remove Song To User PlayList
     * 
     * @sort 7
     * 
     * @authenticated
     * 
     * @group UserPlaylist API
     * 
     * @bodyParam user_playlist_id string The encrypted_id of user playlist. Example: 10
     * @bodyParam song_id string The encrypted_id of song. Example: 10
     * 
     */
    public function removeSongToUserPlayList( Request $request ) {

        return UserPlaylistService::removeSongToUserPlayList( $request );
    }

    /**
     * 8. Copy Playlist To User PlayList
     * 
     * @sort 8
     * 
     * @authenticated
     * 
     * @group UserPlaylist API
     * 
     * @bodyParam playlist_id string The encrypted_id of playlist. Example: 10
     * 
     */
    public function addPlaylistToUserPlayList( Request $request ) {

        return UserPlaylistService::addPlaylistToUserPlayList( $request );
    }

}