<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Hash,
    Storage,
    Validator,
};

use Illuminate\Validation\Rules\Password;

use App\Models\{
    FileManager,
    Category,
    User,
    Role as RoleModel
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class CategoryService
{
    public static function allCategories( $request ) {

        $category = Category::select( 'categories.*' );

        $filterObject = self::filter( $request, $category );
        $category = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            switch ( $request->input( 'order.0.column' ) ) {
                default:
                    $category->orderBy( 'created_at', $dir );
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
                'name',
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

        if ( !empty( $request->created_at ) ) {
            if ( str_contains( $request->created_at, 'to' ) ) {
                $dates = explode( ' to ', $request->created_at );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'categories.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_at );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'categories.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( function( $q ) use ( $request ) {
                $q->where( 'multi_lang_name', 'LIKE', '%' . $request->name . '%' );
            } );
            $filter = true;
        }

        if( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
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

        $category = Category::find( $request->id );

        if( $category ) {
            $category->append( [
                'encrypted_id',
                'image_path',
                'title',
                'text',
            ] );
        }

        return response()->json( $category );
    }

    public static function createCategory( $request ) {

        $validator = Validator::make( $request->all(), [
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'image' => [ 'required' ],
            'color' => [ 'required' ],
        ] );

        $attributeName = [
            'en_name' => __( 'category.name' ),
            'zh_name' => __( 'category.name' ),
            'image' => __( 'category.image' ),
            'color' => __( 'category.color' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createcategory = Category::create( [
                'en_title' => $request->en_title,
                'zh_title' => $request->zh_title,
                'image' => $request->image,
                'color' => $request->color,
                'type_id' => $request->type_id,
            ] );
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
            'en_name' => [ 'required' ],
            'zh_name' => [ 'nullable' ],
            'image' => [ 'required' ],
            'color' => [ 'required' ],
        ] );

        $attributeName = [
            'en_name' => __( 'category.name' ),
            'zh_name' => __( 'category.name' ),
            'color' => __( 'category.color' ),
            'image' => __( 'category.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updatecategory = Category::find( $request->id );
            $updatecategory->en_name = $request->en_name;
            $updatecategory->zh_name = $request->zh_name;
            $updatecategory->type_id = $request->type_id;
            if( $updatecategory->image != $request->image ) {
                Storage::disk('public')->delete( $updatecategory->image );
            }
            $updatecategory->image = $request->image;
            $updatecategory->color = $request->color;
            $updatecategory->save();

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

    public static function updateCategoryStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updatecategory = Category::find( $request->id );
        $updatecategory->status = $request->status;
        $updatecategory->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.categories' ) ) ] ),
        ] );
    }

    // api
    public static function getCategories( $request ) {

        $per_page = $request->input( 'per_page', 10 );
        $type_id = \Helper::decode( $request->input( 'type_id' ) );

        $categories = Category::where( 'type_id', $type_id )
            ->where( 'status', '10' )
            ->orderBy( 'created_at', 'DESC' )
            ->paginate( $per_page );

        if ( $categories ) {
            $categories->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $categories );
    }
}