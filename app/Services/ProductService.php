<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
};

use Helper;

use App\Models\{
    Company,
    Customer,
    Product,
    ProductGallery,
    Booking,
    FileManager,
    ProductVariant,
    Froyo,
    Syrup,
    Topping,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Carbon\Carbon;

class ProductService
{

    public static function createProduct( $request ) {

        $validator = Validator::make( $request->all(), [
            'code' => [ 'nullable' ],
            'title' => [ 'nullable' ],
            'description' => [ 'nullable' ],
            'price' => [ 'required' ],
            'discount_price' => [ 'nullable' ],
            'image' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'product.title' ),
            'description' => __( 'product.description' ),
            'image' => __( 'product.image' ),
            'code' => __( 'product.code' ),
            'price' => __( 'product.price' ),
            'discount_price' => __( 'product.discount_price' ),
            'image' => __( 'product.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $productCreate = Product::create([
                'code' => $request->code,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'discount_price' => $request->discount_price,
            ]);

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'product/' . $productCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $productCreate->image = $target;
                   $productCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.products' ) ) ] ),
        ] );
    }
    
    public static function updateProduct( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
    
        $validator = Validator::make( $request->all(), [
            'code' => [ 'nullable' ],
            'title' => [ 'nullable' ],
            'description' => [ 'nullable' ],
            'price' => [ 'nullable' ],
            'discount_price' => [ 'nullable' ],
            'image' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'product.title' ),
            'description' => __( 'product.description' ),
            'image' => __( 'product.image' ),
            'code' => __( 'product.code' ),
            'price' => __( 'product.price' ),
            'discount_price' => __( 'product.discount_price' ),
            'image' => __( 'product.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            
            $updateProduct = Product::find( $request->id );
  
            $updateProduct->code = $request->code ?? $updateProduct->code;
            $updateProduct->title = $request->title ?? $updateProduct->title;
            $updateProduct->description = $request->description ?? $updateProduct->description;
            $updateProduct->price = $request->price ?? $updateProduct->price;
            $updateProduct->discount_price = $request->discount_price ?? $updateProduct->discount_price;

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'product/' . $updateProduct->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateProduct->image = $target;
                   $updateProduct->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateProduct->save();
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.products' ) ) ] ),
        ] );
    }

    public static function allProducts( $request ) {

        $products = Product::select( 'products.*' );

        $filterObject = self::filter( $request, $products );
        $product = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $product->orderBy( 'products.created_at', $dir );
                    break;
                case 1:
                    $product->orderBy( 'products.id', $dir );
                    break;
                case 3:
                    $product->orderBy( 'products.title', $dir );
                    break;
                case 4:
                    $product->orderBy( 'products.description', $dir );
                    break;
            }
        }

            $productCount = $product->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $products = $product->skip( $offset )->take( $limit )->get();

            if ( $products ) {
                $products->append( [
                    'encrypted_id',
                    'image_path'
                ] );
            }

            $totalRecord = Product::count();

            $data = [
                'products' => $products,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $productCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    public static function allProductsBundles( $request ) {

        $products = Product::select( 'products.*' )->with(['variants','bundles', 'categories', 'warehouses', 'galleries','brand','supplier', 'unit']);

        $filterObject = self::filter( $request, $products );
        $product = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $product->orderBy( 'products.created_at', $dir );
                    break;
                case 1:
                    $product->orderBy( 'products.id', $dir );
                    break;
                case 3:
                    $product->orderBy( 'products.title', $dir );
                    break;
                case 4:
                    $product->orderBy( 'products.description', $dir );
                    break;
            }
        }

            $productCount = $product->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $products = $product->skip( $offset )->take( $limit )->get();

            if ( $products ) {
                $products->append( [
                    'encrypted_id',
                    'stock_worth'
                ] );

                foreach ($products as $product) {
                    if( $product->galleries ){
                        $product->galleries->append( [
                            'image_path',
                        ] );
                    }
                }
            }

            $totalRecord = Product::count();

            $data = [
                'products' => $products,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $productCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->name ) ) {
            $model->where('title', 'LIKE', '%' . $request->name . '%')
            ->orWhereHas('variants', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->name . '%');
            })->orWhereHas('bundles', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->name . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->title ) ) {
            $model->where( 'products.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'products.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->code ) ) {
            $model->where( 'products.code', 'LIKE', '%' . $request->code . '%' );
            $filter = true;
        }
        
        if ( !empty( $request->id ) ) {
            $model->where( 'products.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->brand)) {
            $model->whereHas('brand', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->brand . '%');
            });
            $filter = true;
        }

        if (!empty($request->category)) {
            $model->whereHas('categories', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->category . '%');
            });
            $filter = true;
        }

        if (!empty($request->unit)) {
            $model->whereHas('unit', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->unit . '%');
            });
            $filter = true;
        }
        
        if (!empty($request->warehouse)) {
            $model->whereHas('warehouses', function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse);
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'products.status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneProduct( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $product = Product::select( 'products.*' )->find( $request->id );

        if ( $product ) {
            $product->append( [
                'encrypted_id',
                'image_path',
            ] );
        }
        
        return response()->json( $product );
    }

    public static function deleteProduct( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'product.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Product::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.products' ) ) ] ),
        ] );
    }

    public static function updateProductStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateProduct = Product::find( $request->id );
            $updateProduct->status = $updateProduct->status == 10 ? 20 : 10;

            $updateProduct->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'product' => $updateProduct,
                    'message_key' => 'update_product_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_product_failed',
            ], 500 );
        }
    }

    public static function removeProductGalleryImage( $request ) {

        $updateProduct = ProductGallery::find( $request->id );
        $updateProduct->delete();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'product.galleries' ) ) ] ),
        ] );
    }

    public static function getproducts( $request ) {

        $products = Product::select( 'products.*' )->where('status', 10)->where('product_type', 1);

        $filterObject = self::filter( $request, $products );
        $product = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $product->orderBy( 'products.created_at', $dir );
                    break;
                case 1:
                    $product->orderBy( 'products.id', $dir );
                    break;
                case 3:
                    $product->orderBy( 'products.title', $dir );
                    break;
                case 4:
                    $product->orderBy( 'products.description', $dir );
                    break;
            }
        }

            $productCount = $product->count();

            $limit = 10;
            $offset = 0;

            $products = $product->skip( $offset )->take( $limit )->get();

            if ( $products ) {

                $products->makeHidden([
                    'status',
                    'created_at',
                    'updated_at',
                    'image',
                ]);

                $products->append( [
                    'encrypted_id',
                    'image_path'
                ] );
            }

            return response()->json([
                'message' => '',
                'message_key' => 'get_menu_success',
                'data' => $products,
            ]);

    }

    public static function getFroyos( $request ) {

        $froyos = Froyo::select( 'froyos.*' )->where('status', 10);

        $filterObject = self::filter( $request, $froyos );
        $froyo = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $froyo->orderBy( 'froyos.created_at', $dir );
                    break;
                case 1:
                    $froyo->orderBy( 'froyos.id', $dir );
                    break;
                case 3:
                    $froyo->orderBy( 'froyos.title', $dir );
                    break;
                case 4:
                    $froyo->orderBy( 'froyos.description', $dir );
                    break;
            }
        }

            $froyoCount = $froyo->count();

            $limit = 10;
            $offset = 0;

            $froyos = $froyo->skip( $offset )->take( $limit )->get();

            if ( $froyos ) {

                $froyos->makeHidden([
                    'status',
                    'created_at',
                    'updated_at',
                    'image',
                ]);

                $froyos->append( [
                    'encrypted_id',
                    'image_path'
                ] );
            }

            return response()->json([
                'message' => '',
                'message_key' => 'get_froyos_success',
                'data' => $froyos,
            ]);

              
    }

    public static function getSyrups( $request ) {

        $syrups = Syrup::select( 'syrups.*' )->where('status', 10);

        $filterObject = self::filter( $request, $syrups );
        $syrup = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $syrup->orderBy( 'syrups.created_at', $dir );
                    break;
                case 1:
                    $syrup->orderBy( 'syrups.id', $dir );
                    break;
                case 3:
                    $syrup->orderBy( 'syrups.title', $dir );
                    break;
                case 4:
                    $syrup->orderBy( 'syrups.description', $dir );
                    break;
            }
        }

            $syrupCount = $syrup->count();

            $limit = 10;
            $offset = 0;

            $syrups = $syrup->skip( $offset )->take( $limit )->get();

            if ( $syrups ) {

                $syrups->makeHidden([
                    'status',
                    'created_at',
                    'updated_at',
                    'image',
                ]);

                $syrups->append( [
                    'encrypted_id',
                    'image_path'
                ] );
            }

            return response()->json([
                'message' => '',
                'message_key' => 'get_syrups_success',
                'data' => $syrups,
            ]);

              
    }

    public static function getToppings( $request ) {

        $toppings = Topping::select( 'toppings.*' )->where('status', 10);

        $filterObject = self::filter( $request, $toppings );
        $topping = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $topping->orderBy( 'toppings.created_at', $dir );
                    break;
                case 1:
                    $topping->orderBy( 'toppings.id', $dir );
                    break;
                case 3:
                    $topping->orderBy( 'toppings.title', $dir );
                    break;
                case 4:
                    $topping->orderBy( 'toppings.description', $dir );
                    break;
            }
        }

            $toppingCount = $topping->count();

            $limit = 10;
            $offset = 0;

            $toppings = $topping->skip( $offset )->take( $limit )->get();

            if ( $toppings ) {

                $toppings->makeHidden([
                    'status',
                    'created_at',
                    'updated_at',
                    'image',
                ]);

                $toppings->append( [
                    'encrypted_id',
                    'image_path'
                ] );
            }

            return response()->json([
                'message' => '',
                'message_key' => 'get_toppings_success',
                'data' => $toppings,
            ]);

              
    }

    public static function getSelections( $request ) {

        $froyos = Froyo::select( 'froyos.*' )->where('status', 10)->get();

        $syrups = Syrup::select( 'syrups.*' )->where('status', 10)->get();

        $toppings = Topping::select( 'toppings.*' )->where('status', 10)->get();

        $froyos->makeHidden([
            'created_at',
            'updated_at',
            'quantity_per_serving',
            'status',
            'measurement_unit',
        ]);

        $syrups->makeHidden([
            'created_at',
            'updated_at',
            'quantity_per_serving',
            'status',
            'measurement_unit',
        ]);

        $toppings->makeHidden([
            'created_at',
            'updated_at',
            'quantity_per_serving',
            'status',
            'measurement_unit',
        ]);

        foreach($froyos as $froyo){
            $froyo->append(['image_path']);
        }

        foreach($syrups as $syrup){
            $syrup->append(['image_path']);
        }
        
        foreach($toppings as $topping){
            $topping->append(['image_path']);
        }

        $data['froyos'] = $froyos;
        $data['syrups'] = $syrups;
        $data['toppings'] = $toppings;

        return response()->json([
            'message' => '',
            'message_key' => 'get_selection_success',
            'data' => $data,
        ]);

    }
}