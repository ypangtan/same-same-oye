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
    Brand,
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BrandService
{

    public static function createBrand( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'brand.title' ),
            'description' => __( 'brand.description' ),
            'image' => __( 'brand.image' ),
            'thumbnail' => __( 'brand.thumbnail' ),
            'url_slug' => __( 'brand.url_slug' ),
            'structure' => __( 'brand.structure' ),
            'size' => __( 'brand.size' ),
            'phone_number' => __( 'brand.phone_number' ),
            'sort' => __( 'brand.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $brandCreate = Brand::create([
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

                    $target = 'brand/' . $brandCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $brandCreate->image = $target;
                   $brandCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'brand/' . $brandCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $brandCreate->thumbnail = $target;
                   $brandCreate->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.brands' ) ) ] ),
        ] );
    }
    
    public static function updateBrand( $request ) {

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
            'title' => __( 'brand.title' ),
            'description' => __( 'brand.description' ),
            'image' => __( 'brand.image' ),
            'thumbnail' => __( 'brand.thumbnail' ),
            'url_slug' => __( 'brand.url_slug' ),
            'structure' => __( 'brand.structure' ),
            'size' => __( 'brand.size' ),
            'phone_number' => __( 'brand.phone_number' ),
            'sort' => __( 'brand.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateBrand = Brand::find( $request->id );
    
            $updateBrand->title = $request->title;
            $updateBrand->description = $request->description;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'brand/' . $updateBrand->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateBrand->image = $target;
                   $updateBrand->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateBrand->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.brands' ) ) ] ),
        ] );
    }

     public static function allBrands( $request ) {

        $brands = Brand::select( 'brands.*');

        $filterObject = self::filter( $request, $brands );
        $brand = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $brand->orderBy( 'brands.created_at', $dir );
                    break;
                case 2:
                    $brand->orderBy( 'brands.title', $dir );
                    break;
                case 3:
                    $brand->orderBy( 'brands.description', $dir );
                    break;
            }
        }

            $brandCount = $brand->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $brands = $brand->skip( $offset )->take( $limit )->get();

            if ( $brands ) {
                $brands->append( [
                    'encrypted_id',
                    'image_path',
                    'thumbnail_path',
                ] );
            }

            $totalRecord = Brand::count();

            $data = [
                'brands' => $brands,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $brandCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'brands.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'brands.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_brand)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_brand . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'brand.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneBrand( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $brand = Brand::find( $request->id );

        $brand->append( ['encrypted_id','image_path'] );
        
        return response()->json( $brand );
    }

    public static function deleteBrand( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'brand.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Brand::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.brands' ) ) ] ),
        ] );
    }

    public static function updateBrandStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateBrand = Brand::find( $request->id );
            $updateBrand->status = $updateBrand->status == 10 ? 20 : 10;

            $updateBrand->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'brand' => $updateBrand,
                    'message_key' => 'update_brand_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_brand_failed',
            ], 500 );
        }
    }

    public static function removeBrandGalleryImage( $request ) {

        $updateFarm = Brand::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}