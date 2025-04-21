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
    TaxMethod,
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TaxMethodService
{

    public static function createTaxMethod( $request ) {

        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'tax_percentage' => [ 'nullable', 'numeric', 'min:0' ],
        ] );

        $attributeName = [
            'parent_id' => __( 'tax_method.parent_id' ),
            'title' => __( 'tax_method.title' ),
            'description' => __( 'tax_method.description' ),
            'image' => __( 'tax_method.image' ),
            'thumbnail' => __( 'tax_method.thumbnail' ),
            'url_slug' => __( 'tax_method.url_slug' ),
            'structure' => __( 'tax_method.structure' ),
            'size' => __( 'tax_method.size' ),
            'phone_number' => __( 'tax_method.phone_number' ),
            'sort' => __( 'tax_method.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $taxMethodCreate = TaxMethod::create([
                'tax_percentage' => $request->tax_percentage,
                'title' => $request->title,
            ]);

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'tax_method/' . $taxMethodCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $taxMethodCreate->image = $target;
                   $taxMethodCreate->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.tax_methods' ) ) ] ),
        ] );
    }
    
    public static function updateTaxMethod( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'tax_percentage' => [ 'nullable', 'numeric', 'min:0' ],
        ] );

        $attributeName = [
            'title' => __( 'tax_method.title' ),
            'description' => __( 'tax_method.description' ),
            'image' => __( 'tax_method.image' ),
            'thumbnail' => __( 'tax_method.thumbnail' ),
            'url_slug' => __( 'tax_method.url_slug' ),
            'structure' => __( 'tax_method.structure' ),
            'size' => __( 'tax_method.size' ),
            'phone_number' => __( 'tax_method.phone_number' ),
            'sort' => __( 'tax_method.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateTaxMethod = TaxMethod::find( $request->id );
    
            $updateTaxMethod->title = $request->title;
            $updateTaxMethod->tax_percentage = $request->tax_percentage;

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'tax_method/' . $updateTaxMethod->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateTaxMethod->image = $target;
                   $updateTaxMethod->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateTaxMethod->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.tax_methods' ) ) ] ),
        ] );
    }

     public static function allTaxMethods( $request ) {

        $taxMethods = TaxMethod::select( 'tax_methods.*' );

        $filterObject = self::filter( $request, $taxMethods );
        $taxMethod = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $taxMethod->orderBy( 'tax_methods.created_at', $dir );
                    break;
                case 2:
                    $taxMethod->orderBy( 'tax_methods.created_at', $dir );
                    break;
                case 3:
                    $taxMethod->orderBy( 'tax_methods.title', $dir );
                    break;
                case 4:
                    $taxMethod->orderBy( 'tax_methods.description', $dir );
                    break;
            }
        }

            $taxMethodCount = $taxMethod->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $taxMethods = $taxMethod->skip( $offset )->take( $limit )->get();

            if ( $taxMethods ) {
                $taxMethods->append( [
                    'encrypted_id',
                    'image_path',
                    'formatted_tax'
                ] );
            }

            $totalRecord = TaxMethod::count();

            $data = [
                'tax_methods' => $taxMethods,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $taxMethodCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'tax_methods.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'tax_methods.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        if ( !empty( $request->id ) ) {
            $model->where( 'tax_methods.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_tax_method)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_tax_method . '%');
            });
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneTaxMethod( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $taxMethod = TaxMethod::select( 'tax_methods.*' )->find( $request->id );

        $taxMethod->append( ['encrypted_id','image_path'] );
        
        return response()->json( $taxMethod );
    }

    public static function deleteTaxMethod( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'tax_method.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            TaxMethod::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.tax_methods' ) ) ] ),
        ] );
    }

    public static function updateTaxMethodStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateTaxMethod = TaxMethod::find( $request->id );
            $updateTaxMethod->status = $updateTaxMethod->status == 10 ? 20 : 10;

            $updateTaxMethod->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'tax_method' => $updateTaxMethod,
                    'message_key' => 'update_tax_method_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_tax_method_failed',
            ], 500 );
        }
    }

    public static function removeTaxMethodGalleryImage( $request ) {

        $updateFarm = TaxMethod::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}