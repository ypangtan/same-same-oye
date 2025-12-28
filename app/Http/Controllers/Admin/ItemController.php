<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ItemService,
    FileManagerService,
    FileService,
};

class ItemController extends Controller
{

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.items' ) ) ] );
        $this->data['content'] = 'admin.item.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.items' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.items' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.items' ) ) ] ),
        ];

        $this->data['data']['type'] = $request->type ?? null;
        $this->data['data']['parent_route'] = $request->parent_route ?? null;

        return view( 'admin.main' )->with( $this->data );  
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.items' ) ) ] );
        $this->data['content'] = 'admin.item.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.items' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.items' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.items' ) ) ] ),
        ];

        $this->data['data']['type'] = $request->type ?? null;
        $this->data['data']['parent_route'] = $request->parent_route ?? null;

        return view( 'admin.main' )->with( $this->data );  
    }

    public function allItems( Request $request ) {
        return ItemService::allItems( $request );
    }

    public function oneItem( Request $request ) {
        return ItemService::oneItem( $request );
    }

    public function createItem( Request $request ) {
        return ItemService::createItem( $request );
    }

    public function updateItem( Request $request ) {
        return ItemService::updateItem( $request );
    }

    public function updateItemStatus( Request $request ) {
        return ItemService::updateItemStatus( $request );
    }

    public function songUpload( Request $request ) {
        return FileService::songUpload( $request );
    }

    public function imageUpload( Request $request ) {
        $request->merge( [
            'source' => 'image/item'
        ] );
        return FileService::imageUpload( $request );
    }
}
