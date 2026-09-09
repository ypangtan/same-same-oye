<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    RadioQueueService,
    FileService,
};

class RadioController extends Controller
{
    public function index() {

        $this->data['header']['title'] = __( 'template.radio_queue' );
        $this->data['content'] = 'admin.radio.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.radios' ),
            'title' => __( 'template.radio_queue' ),
            'mobile_title' => __( 'template.radio_queue' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add() {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.radio_queue' ) ) ] );
        $this->data['content'] = 'admin.radio.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.radios' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.radio_queue' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.radio_queue' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function history() {

        $this->data['header']['title'] = __( 'template.radio_history' );
        $this->data['content'] = 'admin.radio.history';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.radios' ),
            'title' => __( 'template.radio_history' ),
            'mobile_title' => __( 'template.radio_history' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allItems( Request $request ) {
        return RadioQueueService::allItems( $request );
    }

    public function allHistory( Request $request ) {
        return RadioQueueService::allHistory( $request );
    }

    public function createItem( Request $request ) {
        return RadioQueueService::createItem( $request );
    }

    public function deleteItem( Request $request ) {
        return RadioQueueService::deleteItem( $request );
    }

    public function reorder( Request $request ) {
        return RadioQueueService::updateOrder( $request );
    }

    public function songUpload( Request $request ) {
        return FileService::radioSongUpload( $request );
    }

    public function nowPlaying() {
        return RadioQueueService::nowPlaying();
    }

    public function listenerGraph( Request $request ) {
        return RadioQueueService::listenerGraph( $request );
    }
}
