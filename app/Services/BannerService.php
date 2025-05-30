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
            'file' => [ 'required','mimes:jpeg,jpg,png' ],
        ] );

        $attributeName = [
            'file' => __( 'banner.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $bannerCreate = Banner::create([
                'title' => null,
                'description' => null,
                'sequence' => 1,
                'status' => 10,
            ]);

            $name = $request->file( 'file' )->getClientOriginalName();
            $path = $request->file( 'file' )->store( 'file-managers', [ 'disk' => 'public' ] );
            $type = $request->file( 'file' )->getClientOriginalExtension() == 'pdf' ? 1 : 2;

            $bannerCreate->image = $path;
            $bannerCreate->save();

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
                'url' => $bannerCreate->imagePath
            ],
            'status' => 200
        ] );
    }
    
    public static function updateBanner( $request ) {

        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'banner.title' ),
            'description' => __( 'banner.description' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();

        try {
            $updateBanner = Banner::find( $request->id );
    
            $updateBanner->title = $request->title;
            $updateBanner->description = $request->description;
            
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

    public static function allStocksBanners( $request ) {

        // Query all banners not in vending_machine_stocks
        $banners = Banner::select( 'banners.*' )
            ->whereNotIn('id', function ($query) {
                $query->select('banner_id')
                    ->from('vending_machine_stocks')
                    ->whereNotNull('banner_id');
            });
    
        $filterObject = self::filter( $request, $banners );
        $banner = $filterObject['model'];
        $filter = $filterObject['filter'];
    
        // Handle sorting
        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $banner->orderBy( 'banners.created_at', $dir );
                    break;
                case 3:
                    $banner->orderBy( 'banners.title', $dir );
                    break;
                case 4:
                    $banner->orderBy( 'banners.description', $dir );
                    break;
            }
        }
    
        $bannerCount = $banner->count();
    
        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;
    
        // Paginate results
        $banners = $banner->skip( $offset )->take( $limit )->get();
    
        if ( $banners ) {
            $banners->append( [
                'encrypted_id',
                'image_path',
            ] );
        }
    
        $totalRecord = Banner::whereNotIn('id', function ($query) {
            $query->select('banner_id')
                ->from('vending_machine_stocks')
                ->whereNotNull('banner_id');
        })->count();
    
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

        $banner = Banner::find( $request->id );

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
            Banner::find($request->id)->delete($request->id);
            
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

    public static function allBannersForVendingMachine( $request ) {

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

    public static function ckeUpload( $request ) {

        $file = $request->file( 'file' )->store( 'vouhcer/ckeditor', [ 'disk' => 'public' ] );

        $data = [
            'url' => asset( 'storage/' . $file ),
        ];

        return response()->json( $data );
    }

    public static function getBanners( $request )
    {
        $banners = Banner::where('status', 10)
        ->orderBy( 'sequence' );

        $banners = $banners->get();

        foreach( $banners as $banner ) {
            $banner->append( ['image_path'] );
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'get_banner_success',
            'data' => $banners,
        ] );

    }

    public static function validateBanner( $request )
    {

        $validator = Validator::make( $request->all(), [
            'promo_code' => [ 'required' ],
        ] );

        $attributeName = [
            'promo_code' => __( 'banner.promo_code' ),
        ];
        
        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $validator = Validator::make( $request->all(), [
            'cart' => [ 'required', function( $attribute, $value, $fail ) {
                $cart = Cart::find( $value )->where('status', 10);
                if ( !$cart ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ]
        ] );

        $validator->stopOnFirstFailure( true )->validate();

        $banner = Banner::where('status', 10)
            ->where( 'id', $request->promo_code )
            ->orWhere('promo_code', $request->promo_code)
            ->where(function ( $query) {
                $query->where(function ( $q) {
                    $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', Carbon::now());
                })
                ->where(function ( $q) {
                    $q->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', Carbon::now());
                });
        })->first();

        if ( !$banner ) {
            return response()->json( [
                'message_key' => 'banner_not_available',
                'message' => __('banner.banner_not_available'),
                'errors' => [
                    'banner' => __('banner.banner_not_available')
                ]
            ], 422 );
        }

        // user's usage
        $user = auth()->user();
        $bannerUsages = BannerUsage::where( 'banner_id', $banner->id )->where( 'user_id', $user->id )->get();

        if ( $bannerUsages->count() > $banner->usable_amount ) {
            return response()->json( [
                'message_key' => 'banner_you_have_maximum_used',
                'message' => __('banner.banner_you_have_maximum_used'),
                'errors' => [
                    'banner' => __('banner.banner_you_have_maximum_used')
                ]
            ], 422 );
        }

        // total claimable
        if ( $banner->total_claimable <= 0 ) {
            return response()->json( [
                'message_key' => 'banner_fully_claimed',
                'message' => __('banner.banner_fully_claimed'),
                'errors' => [
                    'banner' => __('banner.banner_fully_claimed')
                ]
            ], 422 );
        }

        // check is user able to claim this
        $userBanner = UserBanner::where( 'banner_id', $banner->id )->where( 'user_id', $user->id )->first();
        if(!$userBanner){
            $userPoints = $user->wallets->where( 'type', 1 )->first();

            if ( $userPoints->balance < $banner->points_required ) {

                return response()->json( [
                    'message_key' => 'minimum_points_required',
                    'message' => 'Mininum of ' . $banner->points_required . ' points is required to claim this banner',
                    'errors' => [
                        'banner' => 'Mininum of ' . $banner->points_required . ' points is required to claim this banner',
                    ]
                ], 422 );
    
            }
        }

        $cart = Cart::find( $request->cart );

        if ( $banner->discount_type == 3 ) {

            $adjustment = json_decode( $banner->buy_x_get_y_adjustment );
            
            $x = $cart->cartMetas->whereIn( 'product_id', $adjustment->buy_products )->count();

            if ( $x < $adjustment->buy_quantity ) {
                return response()->json( [
                   'required_amount' => $adjustment->buy_quantity,
                   'message' => __( 'banner.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] ),
                   'message_key' => 'banner.min_quantity_of_x_' . $adjustment->buy_products[0] . '_' .  Product::find( $adjustment->buy_products[0] )->value( 'title' ) ,
                        'errors' => [
                            'banner' => __( 'banner.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] )

                        ]
                ], 422 );
            }

        } else {

            $adjustment = json_decode( $banner->buy_x_get_y_adjustment );

            if ( $cart->total_price < $adjustment->buy_quantity ) {
                return response()->json( [
                    'required_amount' => $adjustment->buy_quantity,
                    'message' => __( 'banner.min_spend_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id', $adjustment->buy_products[0] )->value( 'title' ) ] ),
                    'message_key' => 'banner.min_spend_of_x',
                    'errors' => [
                        'banner' => __( 'banner.min_spend_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id', $adjustment->buy_products[0] )->value( 'title' ) ] )
                    ]
                ], 422 );
            }

        }
    
        return response()->json( [
            'message' => 'banner.banner_validated',
        ] );
    }

    public static function claimBanner( $request )
    {

        $validator = Validator::make( $request->all(), [
            'banner_id' => [ 'required' ],
        ] );

        $attributeName = [
            'banner_id' => __( 'banner.banner_id' ),
        ];
        
        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $banner = Banner::where( 'id', $request->banner_id )
        ->orWhere( 'promo_code', $request->banner_id )
            ->where(function ( $query) {
                $query->where(function ( $q) {
                    $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', Carbon::now());
                })
                ->where(function ( $q) {
                    $q->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', Carbon::now());
                });
        })
        ->where( 'type', 2 )
        ->where( 'status', 10 )->first();

        if ( !$banner ) {
            return response()->json( [
                'message_key' => 'banner_not_available',
                'message' => __('banner.banner_not_available'),
                'errors' => [
                    'banner' => __('banner.banner_not_available'),
                ]
            ], 422 );
        }
        $user = auth()->user();

        $bannerUsages = BannerUsage::where( 'banner_id', $banner->id )->where( 'user_id', $user->id )->get();

        if ( $bannerUsages->count() > $banner->usable_amount ) {
            return response()->json( [
                'message_key' => 'banner_fully_claimed',
                'message' => __('banner.banner_fully_claimed'),
                'errors' => [
                    'banner' => __('banner.banner_fully_claimed'),
                ]
            ], 422 );
        }

        $bannerUserClaimed = UserBanner::where( 'banner_id', $banner->id )->where( 'user_id', $user->id )->count();

        if ( $bannerUserClaimed >= $banner->claim_per_user ) {
            return response()->json( [
                'message_key' => 'banner_you_have_maximum_claimed',
                'message' => __('banner.banner_you_have_maximum_claimed'),
                'errors' => [
                    'banner' => __('banner.banner_you_have_maximum_claimed'),
                ]
            ], 422 );
        }
        
        $userPoints = $user->wallets->where( 'type', 1 )->first();

        if ( $userPoints->balance < $banner->points_required ) {

            return response()->json( [
                'required_amount' => $banner->points_required,
                'message' => 'Mininum of ' . $banner->points_required . ' points is required to claim this banner',
                'message_key' => 'minimum_points_is_required',
                'errors' => [
                    'banner' => 'Mininum of ' . $banner->points_required . ' points is required to claim this banner',
                ]
            ], 422 );

        }        
        
        if ( $banner->total_claimable <= 0 ) {
            return response()->json( [
                'message_key' => 'banner_fully_claimed',
                'message' => __('banner.banner_fully_claimed'),
                'errors' => [
                    'banner' => __('banner.banner_fully_claimed')
                ]
            ], 422 );
        }

        WalletService::transact( $userPoints, [
            'amount' => -$banner->points_required,
            'remark' => 'Claim Banner',
            'type' => $userPoints->type,
            'transaction_type' => 11,
        ] );

        $userBanner = UserBanner::create([
            'user_id' => $user->id,
            'banner_id' => $banner->id,
            'expired_date' => Carbon::now()->addDays($banner->validity_days),
            'status' => 10,
            'redeem_from' => 1,
            'total_left' => 1,
            'used_at' => null,
            'secret_code' => strtoupper( \Str::random( 8 ) ),
        ]);

        $banner->total_claimable -= 1;
        $banner->save();
    
        // notification
        UserService::createUserNotification(
            $user->id,
            'notification.user_banner_success',
            'notification.user_banner_success_content',
            'banner',
            'banner'
        );

        self::sendNotification( $order->user, 'banner', __( 'notification.user_banner_success_content' )  );

        return response()->json( [
            'message' => __('banner.banner_claimed'),
            'message_key' => 'banner_claimed',
            'data' => $userBanner->load(['banner'])
        ] );
    }

    private static function sendNotification( $user, $key, $message ) {

        $messageContent = array();

        $messageContent['key'] = $key;
        $messageContent['id'] = $user->id;
        $messageContent['message'] = $message;

        Helper::sendNotification( $affiliate->user_id, $messageContent );
        
    }

}