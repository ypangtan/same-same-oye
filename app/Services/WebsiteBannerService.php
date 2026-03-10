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
    WebsiteBanner,
    Booking,
    FileManager,
    VendingMachine,
    VendingMachineStock,
    WebsiteBannerUsage,
    Cart,
    CartMeta,
    Order,
    OrderMeta,
    UserWebsiteBanner,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class WebsiteBannerService
{

    public static function createWebsiteBanner( $request ) {

        $validator = Validator::make( $request->all(), [
            'url' => [ 'nullable' ],
            'file' => [ 'required','mimes:jpeg,jpg,png' ],
        ] );

        $attributeName = [
            'url' => __( 'website_banner.url' ),
            'file' => __( 'website_banner.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $website_bannerCreate = WebsiteBanner::create([
                'url' => '',
                'sequence' => 1,
                'status' => 10,
                'priority' => 0,
            ]);

            // $name = $request->file( 'file' )->getClientOriginalName();
            // $path = $request->file( 'file' )->store( 'file-managers', [ 'disk' => 'public' ] );
            // $type = $request->file( 'file' )->getClientOriginalExtension() == 'pdf' ? 1 : 2;

            $website_bannerCreate->image = StorageService::upload( 'website_banner', $request->file( 'file' ) );
            $website_bannerCreate->save();

            $website_bannerCreate->append( [ 'image_path' ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.website_banners' ) ) ] ),
            'data' => [
                'id' => $website_bannerCreate->id,
                'url' => $website_bannerCreate->url,
                'website_banner_url' => $website_bannerCreate->image_path,
            ],
            'status' => 200
        ] );
    }
    
    public static function updateWebsiteBanner( $request ) {

        $validator = Validator::make( $request->all(), [
            'url' => [ 'nullable' ],
        ] );

        $attributeName = [
            'url' => __( 'website_banner.url' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();

        try {
            $updateWebsiteBanner = WebsiteBanner::find( $request->id );
    
            $updateWebsiteBanner->url = $request->url;
            
            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'website_banner/' . $updateWebsiteBanner->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateWebsiteBanner->image = $target;
                   $updateWebsiteBanner->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateWebsiteBanner->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.website_banners' ) ) ] ),
        ] );
    }
    
    public static function updateWebsiteBannerUrl( $request ) {

        $validator = Validator::make( $request->all(), [
            'url' => [ 'nullable' ],
        ] );

        $attributeName = [
            'url' => __( 'website_banner.url' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();

        try {
            $updateWebsiteBanner = WebsiteBanner::find( $request->id );
            $updateWebsiteBanner->url = $request->url;
            $updateWebsiteBanner->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.website_banners' ) ) ] ),
        ] );
    }

    public static function allWebsiteBanners( $request ) {

        $website_banners = WebsiteBanner::select( 'website_banners.*');

        $filterObject = self::filter( $request, $website_banners );
        $website_banner = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $website_banner->orderBy( 'website_banners.created_at', $dir );
                    break;
                case 2:
                    $website_banner->orderBy( 'website_banners.title', $dir );
                    break;
                case 3:
                    $website_banner->orderBy( 'website_banners.description', $dir );
                    break;
            }
        }

            $website_bannerCount = $website_banner->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $website_banners = $website_banner->skip( $offset )->take( $limit )->get();

            if ( $website_banners ) {
                $website_banners->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }

            $totalRecord = WebsiteBanner::count();

            $data = [
                'website_banners' => $website_banners,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $website_bannerCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'website_banners.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'website_banners.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_website_banner)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_website_banner . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->website_banner_type ) ) {
            $model->where( 'type', $request->website_banner_type );
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
            $vendingMachineWebsiteBanners = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'website_banner_id' );
            $model->whereNotIn( 'id', $vendingMachineWebsiteBanners );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneWebsiteBanner( $request ) {

        $website_banner = WebsiteBanner::find( $request->id );

        $website_banner->append( ['encrypted_id','image_path'] );
        
        return response()->json( $website_banner );
    }

    public static function oneWebsiteBannerClient( $request ) {

        $website_banner = WebsiteBanner::find( \Helper::decode( $request->id ) );

        $website_banner->append( ['encrypted_id','image_path'] );

        return response()->json( [
            'message' => '',
            'message_key' => 'get_website_banner_success',
            'data' => $website_banner,
        ] );
    }

    public static function deleteWebsiteBanner( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'website_banner.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $website_banner = WebsiteBanner::find($request->id);
            Storage::delete( $website_banner->image );
            $website_banner->delete();
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.website_banners' ) ) ] ),
        ] );
    }

    public static function updateWebsiteBannerStatus( $request ) {

        DB::beginTransaction();

        try {

            $updateWebsiteBanner = WebsiteBanner::find( $request->id );
            $updateWebsiteBanner->status = $updateWebsiteBanner->status == 10 ? 20 : 10;

            $updateWebsiteBanner->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'website_banner' => $updateWebsiteBanner,
                    'message_key' => 'update_website_banner_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_website_banner_failed',
            ], 500 );
        }
    }

    public static function removeWebsiteBannerGalleryImage( $request ) {

        $updateWebsiteBanner = WebsiteBanner::find( $request->id );

        Storage::delete( 'public/' . $updateWebsiteBanner->image );
        $updateWebsiteBanner->image = null;

        $updateWebsiteBanner->save();

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

    public static function getWebsiteBanners( $request ) {
        $website_banners = WebsiteBanner::where('status', 10)
        ->orderBy( 'sequence' );

        $website_banners = $website_banners->get();

        foreach( $website_banners as $website_banner ) {
            $website_banner->append( [
                'image_path',
                'encrypted_id',
            ] );
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'get_website_banner_success',
            'data' => $website_banners,
        ] );

    }

}