<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CollectionService,
    FileManagerService,
    FileService,
};

class TalkController extends Controller
{
    public function item() {

        $this->data['header']['title'] = __( 'template.items' );
        $this->data['content'] = 'admin.item.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.items' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.items' ),
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];
        
        $this->data['data']['type'] = '3';
        $this->data['data']['parent_route'] = route( 'admin.talk.item' );

        return view( 'admin.main' )->with( $this->data );   
    }
    
    public function playlist() {

        $this->data['header']['title'] = __( 'template.playlists' );
        $this->data['content'] = 'admin.playlist.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.playlists' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.playlists' ),
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['type'] = '3';
        $this->data['data']['parent_route'] = route( 'admin.talk.playlist' );

        return view( 'admin.main' )->with( $this->data );   
    }

    public function collection() {

        $this->data['header']['title'] = __( 'template.collections' );
        $this->data['content'] = 'admin.collection.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.collections' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.collections' ),
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['type'] = '3';
        $this->data['data']['parent_route'] = route( 'admin.talk.collection' );

        return view( 'admin.main' )->with( $this->data );   
    }

    public function category() {

        $this->data['header']['title'] = __( 'template.categories' );
        $this->data['content'] = 'admin.category.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.categories' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.categories' ),
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['type'] = '3';
        $this->data['data']['parent_route'] = route( 'admin.talk.category' );

        return view( 'admin.main' )->with( $this->data );   
    }
}
