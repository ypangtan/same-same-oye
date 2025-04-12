<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ProductService,
    WarehouseService,
};

use App\Models\{
    Product,
};

class ProductController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.products' );
        $this->data['content'] = 'admin.product.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.products' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.products' ) ) ] );
        $this->data['content'] = 'admin.product.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.product.index' ),
                'text' => __( 'template.products' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.products' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.products' ) ) ] );
        $this->data['content'] = 'admin.product.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.product.index' ),
                'text' => __( 'template.products' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.products' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allProducts( Request $request ) {

        return ProductService::allProducts( $request );
    }

    public function allProductsBundles( Request $request ) {

        return ProductService::allProductsBundles( $request );
    }

    public function oneProduct( Request $request ) {

        return ProductService::oneProduct( $request );
    }

    public function createProduct( Request $request ) {

        return ProductService::createProduct( $request );
    }

    public function updateProduct( Request $request ) {
        return ProductService::updateProduct( $request );
    }

    public function updateProductStatus( Request $request ) {

        return ProductService::updateProductStatus( $request );
    }

    public function removeProductGalleryImage( Request $request ) {

        return ProductService::removeProductGalleryImage( $request );
    }

}
