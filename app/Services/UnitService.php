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
    Unit,
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class UnitService
{

    public static function createUnit( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'parent_id' => [ 'nullable', 'exists:units,id' ],
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'parent_id' => __( 'unit.parent_id' ),
            'title' => __( 'unit.title' ),
            'description' => __( 'unit.description' ),
            'image' => __( 'unit.image' ),
            'thumbnail' => __( 'unit.thumbnail' ),
            'url_slug' => __( 'unit.url_slug' ),
            'structure' => __( 'unit.structure' ),
            'size' => __( 'unit.size' ),
            'phone_number' => __( 'unit.phone_number' ),
            'sort' => __( 'unit.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $unitCreate = Unit::create([
                'parent_id' => $request->parent_id,
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

                    $target = 'unit/' . $unitCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $unitCreate->image = $target;
                   $unitCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'unit/' . $unitCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $unitCreate->thumbnail = $target;
                   $unitCreate->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.units' ) ) ] ),
        ] );
    }
    
    public static function updateUnit( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'parent_id' => [ 'nullable', 'exists:units,id' ],
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'parent_id' => __( 'unit.parent_id' ),
            'title' => __( 'unit.title' ),
            'description' => __( 'unit.description' ),
            'image' => __( 'unit.image' ),
            'thumbnail' => __( 'unit.thumbnail' ),
            'url_slug' => __( 'unit.url_slug' ),
            'structure' => __( 'unit.structure' ),
            'size' => __( 'unit.size' ),
            'phone_number' => __( 'unit.phone_number' ),
            'sort' => __( 'unit.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateUnit = Unit::find( $request->id );
    
            $updateUnit->parent_id = $request->parent_id;
            $updateUnit->title = $request->title;
            $updateUnit->description = $request->description;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'unit/' . $updateUnit->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateUnit->image = $target;
                   $updateUnit->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateUnit->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.units' ) ) ] ),
        ] );
    }

     public static function allUnits( $request ) {

        $units = Unit::select( 'units.*' );

        $filterObject = self::filter( $request, $units );
        $unit = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $unit->orderBy( 'units.created_at', $dir );
                    break;
                case 2:
                    $unit->orderBy( 'units.parent_id', $dir );
                    break;
                case 3:
                    $unit->orderBy( 'units.title', $dir );
                    break;
                case 4:
                    $unit->orderBy( 'units.description', $dir );
                    break;
            }
        }

            $unitCount = $unit->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $units = $unit->skip( $offset )->take( $limit )->get();

            if ( $units ) {
                $units->append( [
                    'encrypted_id',
                    'image_path',
                    'thumbnail_path',
                ] );
            }

            $totalRecord = Unit::count();

            $data = [
                'units' => $units,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $unitCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'units.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'units.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        if ( !empty( $request->id ) ) {
            $model->where( 'units.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_unit)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_unit . '%');
            });
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneUnit( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $unit = Unit::select( 'units.*' )->find( $request->id );

        $unit->append( ['encrypted_id','image_path'] );
        
        return response()->json( $unit );
    }

    public static function deleteUnit( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'unit.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Unit::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.units' ) ) ] ),
        ] );
    }

    public static function updateUnitStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateUnit = Unit::find( $request->id );
            $updateUnit->status = $updateUnit->status == 10 ? 20 : 10;

            $updateUnit->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'unit' => $updateUnit,
                    'message_key' => 'update_unit_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_unit_failed',
            ], 500 );
        }
    }

    public static function removeUnitGalleryImage( $request ) {

        $updateFarm = Unit::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}