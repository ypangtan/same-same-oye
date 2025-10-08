<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CollectionService,
    FileManagerService,
    FileService,
    MusicRequestService,
};

class MusicRequestController extends Controller
{
    public function index() {

        $this->data['header']['title'] = __( 'template.music_requests' );
        $this->data['content'] = 'admin.music_request.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.music_requests' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.music_requests' ),
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );   
    }

    public function allMusicRequests( Request $request ) {
        return MusicRequestService::allMusicRequests( $request );
    }
}
