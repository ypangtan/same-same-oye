<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CollectionService,
    FileManagerService,
    FileService,
};

class CollectionController extends Controller
{

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.collections' ) ) ] );
        $this->data['content'] = 'admin.collection.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.collections' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.collections' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.collections' ) ) ] ),
        ];
        $this->data['data']['type'] = $request->type ?? null;
        $this->data['data']['parent_route'] = $request->parent_route ?? null;

        return view( 'admin.main' )->with( $this->data );  
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.collections' ) ) ] );
        $this->data['content'] = 'admin.collection.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.collections' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.collections' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.collections' ) ) ] ),
        ];
        $this->data['data']['type'] = $request->type ?? null;
        $this->data['data']['parent_route'] = $request->parent_route ?? null;

        return view( 'admin.main' )->with( $this->data );  
    }

    public function allCollections( Request $request ) {
        return CollectionService::allCollections( $request );
    }

    public function oneCollection( Request $request ) {
        return CollectionService::oneCollection( $request );
    }

    public function createCollection( Request $request ) {
        return CollectionService::createCollection( $request );
    }

    public function updateCollection( Request $request ) {
        return CollectionService::updateCollection( $request );
    }

    public function updateCollectionStatus( Request $request ) {
        return CollectionService::updateCollectionStatus( $request );
    }

    public function ckeUpload( Request $request ) {

        $request->merge( [
            'source' => 'ckeditor/collection'
        ] );

        return FileService::ckeUpload( $request );
    }

    public function imageUpload( Request $request ) {
        $request->merge( [
            'source' => 'image/collection'
        ] );
        return FileService::imageUpload( $request );
    }

    public function updateOrder( Request $request ) {
        return CollectionService::updateOrder( $request );
    }
}
