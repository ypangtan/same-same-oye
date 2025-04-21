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
    Supplier,
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SupplierService
{

    public static function createSupplier( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'supplier.title' ),
            'description' => __( 'supplier.description' ),
            'image' => __( 'supplier.image' ),
            'thumbnail' => __( 'supplier.thumbnail' ),
            'url_slug' => __( 'supplier.url_slug' ),
            'structure' => __( 'supplier.structure' ),
            'size' => __( 'supplier.size' ),
            'phone_number' => __( 'supplier.phone_number' ),
            'sort' => __( 'supplier.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $supplierCreate = Supplier::create([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'supplier/' . $supplierCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $supplierCreate->image = $target;
                   $supplierCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'supplier/' . $supplierCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $supplierCreate->thumbnail = $target;
                   $supplierCreate->save();

                    $thumbnailFile->status = 10;
                    $thumbnailFile->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.suppliers' ) ) ] ),
        ] );
    }
    
    public static function updateSupplier( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'supplier.title' ),
            'description' => __( 'supplier.description' ),
            'image' => __( 'supplier.image' ),
            'thumbnail' => __( 'supplier.thumbnail' ),
            'url_slug' => __( 'supplier.url_slug' ),
            'structure' => __( 'supplier.structure' ),
            'size' => __( 'supplier.size' ),
            'phone_number' => __( 'supplier.phone_number' ),
            'sort' => __( 'supplier.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateSupplier = Supplier::find( $request->id );
    
            $updateSupplier->title = $request->title;
            $updateSupplier->description = $request->description;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'supplier/' . $updateSupplier->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateSupplier->image = $target;
                   $updateSupplier->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateSupplier->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.suppliers' ) ) ] ),
        ] );
    }

     public static function allSuppliers( $request ) {

        $suppliers = Supplier::select( 'suppliers.*');

        $filterObject = self::filter( $request, $suppliers );
        $supplier = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $supplier->orderBy( 'suppliers.created_at', $dir );
                    break;
                case 2:
                    $supplier->orderBy( 'suppliers.title', $dir );
                    break;
                case 3:
                    $supplier->orderBy( 'suppliers.description', $dir );
                    break;
            }
        }

            $supplierCount = $supplier->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $suppliers = $supplier->skip( $offset )->take( $limit )->get();

            if ( $suppliers ) {
                $suppliers->append( [
                    'encrypted_id',
                    'image_path',
                    'thumbnail_path',
                ] );
            }

            $totalRecord = Supplier::count();

            $data = [
                'suppliers' => $suppliers,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $supplierCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'suppliers.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'suppliers.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_supplier)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_supplier . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
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

    public static function oneSupplier( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $supplier = Supplier::find( $request->id );

        $supplier->append( ['encrypted_id','image_path'] );
        
        return response()->json( $supplier );
    }

    public static function deleteSupplier( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'supplier.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Supplier::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.suppliers' ) ) ] ),
        ] );
    }

    public static function updateSupplierStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateSupplier = Supplier::find( $request->id );
            $updateSupplier->status = $updateSupplier->status == 10 ? 20 : 10;

            $updateSupplier->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'supplier' => $updateSupplier,
                    'message_key' => 'update_supplier_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_supplier_failed',
            ], 500 );
        }
    }

    public static function removeSupplierGalleryImage( $request ) {

        $updateFarm = Supplier::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}