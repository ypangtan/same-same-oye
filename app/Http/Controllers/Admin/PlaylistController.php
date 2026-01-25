<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    PlaylistService,
    FileManagerService,
    FileService,
};

class PlaylistController extends Controller
{

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.playlists' ) ) ] );
        $this->data['content'] = 'admin.playlist.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.playlists' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.playlists' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.playlists' ) ) ] ),
        ];

        $this->data['data']['type'] = $request->type ?? null;
        $this->data['data']['parent_route'] = $request->parent_route ?? null;

        return view( 'admin.main' )->with( $this->data );  
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.playlists' ) ) ] );
        $this->data['content'] = 'admin.playlist.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.playlists' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.playlists' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.playlists' ) ) ] ),
        ];

        $this->data['data']['type'] = $request->type ?? null;
        $this->data['data']['parent_route'] = $request->parent_route ?? null;

        return view( 'admin.main' )->with( $this->data );  
    }

    public function allPlaylists( Request $request ) {
        return PlaylistService::allPlaylists( $request );
    }

    public function onePlaylist( Request $request ) {
        return PlaylistService::onePlaylist( $request );
    }

    public function createPlaylist( Request $request ) {
        return PlaylistService::createPlaylist( $request );
    }

    public function updatePlaylist( Request $request ) {
        return PlaylistService::updatePlaylist( $request );
    }

    public function updatePlaylistStatus( Request $request ) {
        return PlaylistService::updatePlaylistStatus( $request );
    }

    public function deletePlaylist( Request $request ) {
        return PlaylistService::deletePlaylist( $request );
    }

    public function ckeUpload( Request $request ) {

        $request->merge( [
            'source' => 'ckeditor/playlist'
        ] );

        return FileService::ckeUpload( $request );
    }

    public function imageUpload( Request $request ) {
        $request->merge( [
            'source' => 'image/playlist'
        ] );
        return FileService::imageUpload( $request );
    }
}
