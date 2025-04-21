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
    Category,
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CategoryService
{

    public static function createCategory( $request ) {
        
        $validator = Validator::make( $request->all(), [
           'parent_id' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\Category::find($value)) {
                    $fail(__('The selected category is invalid.'));
                }
            }],
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'parent_id' => __( 'category.parent_id' ),
            'title' => __( 'category.title' ),
            'description' => __( 'category.description' ),
            'image' => __( 'category.image' ),
            'thumbnail' => __( 'category.thumbnail' ),
            'url_slug' => __( 'category.url_slug' ),
            'structure' => __( 'category.structure' ),
            'size' => __( 'category.size' ),
            'phone_number' => __( 'category.phone_number' ),
            'sort' => __( 'category.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            if( $request->parent_id == 'null' ){
                $request->merge( ['parent_id' => null] );
            }
            
            $categoryCreate = Category::create([
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

                    $target = 'category/' . $categoryCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $categoryCreate->image = $target;
                   $categoryCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'category/' . $categoryCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $categoryCreate->thumbnail = $target;
                   $categoryCreate->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.categories' ) ) ] ),
        ] );
    }
    
    public static function updateCategory( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'parent_id' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\Category::find($value)) {
                    $fail(__('The selected category is invalid.'));
                }
            }],
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'parent_id' => __( 'category.parent_id' ),
            'title' => __( 'category.title' ),
            'description' => __( 'category.description' ),
            'image' => __( 'category.image' ),
            'thumbnail' => __( 'category.thumbnail' ),
            'url_slug' => __( 'category.url_slug' ),
            'structure' => __( 'category.structure' ),
            'size' => __( 'category.size' ),
            'phone_number' => __( 'category.phone_number' ),
            'sort' => __( 'category.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            if( $request->parent_id == 'null' ){
                $request->merge( ['parent_id' => null] );
            }

            $updateCategory = Category::find( $request->id );
    
            $updateCategory->parent_id = $request->parent_id;
            $updateCategory->title = $request->title;
            $updateCategory->description = $request->description;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'category/' . $updateCategory->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateCategory->image = $target;
                   $updateCategory->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateCategory->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.categories' ) ) ] ),
        ] );
    }

     public static function allCategories( $request ) {

        $categories = Category::with(['children', 'parent']);

        $filterObject = self::filter( $request, $categories );
        $category = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $category->orderBy( 'categories.created_at', $dir );
                    break;
                case 2:
                    $category->orderBy( 'categories.parent_id', $dir );
                    break;
                case 3:
                    $category->orderBy( 'categories.title', $dir );
                    break;
                case 4:
                    $category->orderBy( 'categories.description', $dir );
                    break;
            }
        }

            $categoryCount = $category->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $categories = $category->skip( $offset )->take( $limit )->get();

            if ( $categories ) {
                $categories->append( [
                    'encrypted_id',
                    'image_path',
                    'thumbnail_path',
                ] );
            }

            $totalRecord = Category::count();

            $data = [
                'categories' => $categories,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $categoryCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'categories.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'categories.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'categories.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_category)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_category . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'categories.status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneCategory( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $category = Category::with( [
            'children', 'parent',
        ] )->find( $request->id );

        $category->append( ['encrypted_id','image_path'] );
        
        return response()->json( $category );
    }

    public static function deleteCategory( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'category.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Category::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.categories' ) ) ] ),
        ] );
    }

    public static function updateCategoryStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateCategory = Category::find( $request->id );
            $updateCategory->status = $updateCategory->status == 10 ? 20 : 10;

            $updateCategory->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'category' => $updateCategory,
                    'message_key' => 'update_category_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_category_failed',
            ], 500 );
        }
    }

    public static function removeCategoryGalleryImage( $request ) {

        $updateFarm = Category::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}