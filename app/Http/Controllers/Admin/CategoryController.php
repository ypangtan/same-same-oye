<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CategoryService,
    FileService,
};

class CategoryController extends Controller
{

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.categories' ) ) ] );
        $this->data['content'] = 'admin.category.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.categories' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.categories' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.categories' ) ) ] ),
        ];

        $this->data['data']['type'] = $request->type ?? null;
        $this->data['data']['parent_route'] = $request->parent_route ?? null;

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.categories' ) ) ] );
        $this->data['content'] = 'admin.category.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.categories' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.categories' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.categories' ) ) ] ),
        ];
        
        $this->data['data']['type'] = $request->type ?? null;
        $this->data['data']['parent_route'] = $request->parent_route ?? null;

        return view( 'admin.main' )->with( $this->data );
    }

    public function allCategories( Request $request ) {
        return CategoryService::allCategories( $request );
    }

    public function oneCategory( Request $request ) {

        return CategoryService::oneCategory( $request );
    }

    public function createCategory( Request $request ) {

        return CategoryService::createCategory( $request );
    }

    public function updateCategory( Request $request ) {

        return CategoryService::updateCategory( $request );
    }

    public function updateCategoryStatus( Request $request ) {

        return CategoryService::updateCategoryStatus( $request );
    }

    public function imageUpload( Request $request ) {
        $request->merge( [
            'source' => 'image/category'
        ] );
        return FileService::imageUpload( $request );
    }
}
