<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    HomeCollectionService,
};

class HomeCollectionController extends Controller
{
    public function index() {

        $this->data['header']['title'] = __( 'template.home' );
        $this->data['content'] = 'admin.home_collection.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.home' ),
            'title' => __( 'template.home_layout' ),
            'mobile_title' => __( 'template.home' ),
        ];

        $this->data['data']['display_types'] = [
            [ 'value' => '1', 'title' => __( 'collection.type_1' ) ],
            [ 'value' => '2', 'title' => __( 'collection.type_2' ) ],
            [ 'value' => '3', 'title' => __( 'collection.type_3' ) ],
            [ 'value' => '4', 'title' => __( 'collection.type_4' ) ],
            [ 'value' => '5', 'title' => __( 'collection.type_5' ) ],
            [ 'value' => '6', 'title' => __( 'collection.type_6' ) ],
            [ 'value' => '7', 'title' => __( 'collection.type_7' ) ],
            [ 'value' => '8', 'title' => __( 'collection.type_8' ) ],
            [ 'value' => '9', 'title' => __( 'collection.type_9' ) ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allHomeCollections( Request $request ) {
        return HomeCollectionService::allHomeCollections( $request );
    }

    public function addHomeCollection( Request $request ) {
        return HomeCollectionService::addHomeCollection( $request );
    }

    public function updateHomeCollection( Request $request ) {
        return HomeCollectionService::updateHomeCollection( $request );
    }

    public function deleteHomeCollection( Request $request ) {
        return HomeCollectionService::deleteHomeCollection( $request );
    }

    public function updateOrder( Request $request ) {
        return HomeCollectionService::updateOrder( $request );
    }
}
