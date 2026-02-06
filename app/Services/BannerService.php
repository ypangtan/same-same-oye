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
    Banner,
    Booking,
    FileManager,
    VendingMachine,
    VendingMachineStock,
    BannerUsage,
    Cart,
    CartMeta,
    Order,
    OrderMeta,
    UserBanner,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BannerService
{

    public static function createBanner( $request ) {

        $validator = Validator::make( $request->all(), [
            'url' => [ 'nullable' ],
            'file' => [ 'required','mimes:jpeg,jpg,png' ],
        ] );

        $attributeName = [
            'url' => __( 'banner.url' ),
            'file' => __( 'banner.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $bannerCreate = Banner::create([
                'url' => '',
                'sequence' => 1,
                'status' => 10,
                'priority' => 0,
            ]);

            // $name = $request->file( 'file' )->getClientOriginalName();
            // $path = $request->file( 'file' )->store( 'file-managers', [ 'disk' => 'public' ] );
            // $type = $request->file( 'file' )->getClientOriginalExtension() == 'pdf' ? 1 : 2;

            $bannerCreate->image = StorageService::upload( 'banner', $request->file( 'file' ) );
            $bannerCreate->save();

            $bannerCreate->append( [ 'image_path' ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.banners' ) ) ] ),
            'data' => [
                'id' => $bannerCreate->id,
                'url' => $bannerCreate->url,
                'banner_url' => $bannerCreate->image_path,
            ],
            'status' => 200
        ] );
    }
    
    public static function updateBanner( $request ) {

        $validator = Validator::make( $request->all(), [
            'url' => [ 'nullable' ],
        ] );

        $attributeName = [
            'url' => __( 'banner.url' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();

        try {
            $updateBanner = Banner::find( $request->id );
    
            $updateBanner->url = $request->url;
            
            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'banner/' . $updateBanner->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateBanner->image = $target;
                   $updateBanner->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateBanner->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.banners' ) ) ] ),
        ] );
    }
    
    public static function updateBannerUrl( $request ) {

        $validator = Validator::make( $request->all(), [
            'url' => [ 'nullable' ],
        ] );

        $attributeName = [
            'url' => __( 'banner.url' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();

        try {
            $updateBanner = Banner::find( $request->id );
            $updateBanner->url = $request->url;
            $updateBanner->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.banners' ) ) ] ),
        ] );
    }

    public static function allBanners( $request ) {

        $banners = Banner::select( 'banners.*');

        $filterObject = self::filter( $request, $banners );
        $banner = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $banner->orderBy( 'banners.created_at', $dir );
                    break;
                case 2:
                    $banner->orderBy( 'banners.title', $dir );
                    break;
                case 3:
                    $banner->orderBy( 'banners.description', $dir );
                    break;
            }
        }

            $bannerCount = $banner->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $banners = $banner->skip( $offset )->take( $limit )->get();

            if ( $banners ) {
                $banners->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }

            $totalRecord = Banner::count();

            $data = [
                'banners' => $banners,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $bannerCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'banners.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'banners.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_banner)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_banner . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->banner_type ) ) {
            $model->where( 'type', $request->banner_type );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->code ) ) {
            $model->where( 'code', 'LIKE', '%' . $request->code . '%' );
            $filter = true;
        }

        if ( !empty( $request->vending_machine_id ) ) {
            $vendingMachineBanners = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'banner_id' );
            $model->whereNotIn( 'id', $vendingMachineBanners );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneBanner( $request ) {

        $banner = Banner::find( $request->id );

        $banner->append( ['encrypted_id','image_path'] );
        
        return response()->json( $banner );
    }

    public static function oneBannerClient( $request ) {

        $banner = Banner::find( \Helper::decode( $request->id ) );

        $banner->append( ['encrypted_id','image_path'] );

        return response()->json( [
            'message' => '',
            'message_key' => 'get_banner_success',
            'data' => $banner,
        ] );
    }

    public static function deleteBanner( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'banner.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $banner = Banner::find($request->id);
            Storage::delete( $banner->image );
            $banner->delete();
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.banners' ) ) ] ),
        ] );
    }

    public static function updateBannerStatus( $request ) {

        DB::beginTransaction();

        try {

            $updateBanner = Banner::find( $request->id );
            $updateBanner->status = $updateBanner->status == 10 ? 20 : 10;

            $updateBanner->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'banner' => $updateBanner,
                    'message_key' => 'update_banner_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_banner_failed',
            ], 500 );
        }
    }

    public static function removeBannerGalleryImage( $request ) {

        $updateBanner = Banner::find( $request->id );

        Storage::delete( 'public/' . $updateBanner->image );
        $updateBanner->image = null;

        $updateBanner->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }

    public static function ckeUpload( $request ) {

        $file = $request->file( 'file' )->store( 'vouhcer/ckeditor', [ 'disk' => 'public' ] );

        $data = [
            'url' => asset( 'storage/' . $file ),
        ];

        return response()->json( $data );
    }

    public static function getBanners( $request ) {
        $banners = Banner::where('status', 10)
        ->orderBy( 'sequence' );

        $banners = $banners->get();

        foreach( $banners as $banner ) {
            $banner->append( [
                'image_path',
                'encrypted_id',
            ] );
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'get_banner_success',
            'data' => $banners,
        ] );

    }

}