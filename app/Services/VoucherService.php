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
    Voucher,
    Booking,
    FileManager,
    VendingMachine,
    VendingMachineStock,
    VoucherUsage,
    Cart,
    CartMeta,
    Order,
    OrderMeta,
    UserVoucher,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class VoucherService
{

    public static function createVoucher( $request ) {

        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'discount_type' => [ 'required' ],
            'voucher_type' => [ 'nullable' ],
            'promo_code' => ['nullable', 'unique:vouchers,promo_code'],
            'image' => [ 'nullable' ],
            'start_date' => [ 'nullable' ],
            'expired_date' => [ 'nullable' ],
            'total_claimable' => [ 'nullable' ],
            'points_required' => [ 'nullable' ],
            'usable_amount' => [ 'nullable' ],
            'validity_days' => [ 'nullable' ],
            'adjustment_data' => ['required'],
            'claim_per_user' => ['nullable'],
        ] );

        $attributeName = [
            'title' => __( 'voucher.title' ),
            'description' => __( 'voucher.description' ),
            'image' => __( 'voucher.image' ),
            'code' => __( 'voucher.code' ),
            'ingredients' => __( 'voucher.ingredients' ),
            'nutritional_values' => __( 'voucher.nutritional_values' ),
            'price' => __( 'voucher.price' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $adjustmentData = json_decode($request->adjustment_data, true);

        if ($request->discount_type == 3) {
            if (!$adjustmentData) {
                return response()->json(['error' => __('Invalid adjustment data')], 422);
            }
        
            $validator = Validator::make($adjustmentData, [
                'buy_products' => ['required', 'array'],
                'buy_quantity' => ['required', 'numeric', 'min:0'], // Added numeric and min validation
                'get_quantity' => ['required', 'numeric', 'min:1'], // Added numeric and min validation
                'get_product' => ['required', 'exists:products,id'],
            ]);
        
            $attributeName = [
                'buy_products' => __('voucher.buy_products'),
                'buy_quantity' => __('voucher.buy_quantity'),
                'get_quantity' => __('voucher.get_quantity'),
                'get_product' => __('voucher.get_product'),
            ];
        
            $validator->setAttributeNames($attributeName)->validate();
        } elseif ($request->discount_type == 2) {
            $validator = Validator::make($adjustmentData, [
                'buy_quantity' => ['required', 'numeric', 'min:1'], // Added numeric and min validation
                'discount_quantity' => ['required', 'numeric', 'min:0'],
            ]);
        
            $attributeName = [
                'buy_quantity' => __('voucher.buy_quantity'),
                'discount_quantity' => __('voucher.discount_quantity'),
                'discount_type' => __('voucher.discount_type'),
            ];
        
            $validator->setAttributeNames($attributeName)->validate();
        }

        DB::beginTransaction();
        
        try {
            $voucherCreate = Voucher::create([
                'title' => $request->title,
                'discount_type' => $request->discount_type,
                'type' => $request->voucher_type,
                'description' => $request->description,
                'promo_code' => $request->promo_code,
                'total_claimable' => $request->total_claimable,
                'points_required' => $request->points_required,
                'start_date' => $request->start_date,
                'expired_date' => $request->expired_date,
                'buy_x_get_y_adjustment' => $request->adjustment_data,
                'usable_amount' => $request->usable_amount,
                'validity_days' => $request->validity_days,
                'claim_per_user' => $request->claim_per_user,
            ]);

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'voucher/' . $voucherCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $voucherCreate->image = $target;
                   $voucherCreate->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.vouchers' ) ) ] ),
        ] );
    }
    
    public static function updateVoucher( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'discount_type' => [ 'required' ],
            'voucher_type' => [ 'nullable' ],
            'promo_code' => [ 'nullable', 'unique:vouchers,promo_code,' . $request->id, ],
            'image' => [ 'nullable' ],
            'start_date' => [ 'nullable' ],
            'expired_date' => [ 'nullable' ],
            'total_claimable' => [ 'nullable' ],
            'points_required' => [ 'nullable' ],
            'usable_amount' => [ 'nullable' ],
            'validity_days' => [ 'nullable' ],
            'adjustment_data' => ['required'],
            'claim_per_user' => ['required'],
            
        ] );

        $attributeName = [
            'title' => __( 'voucher.title' ),
            'description' => __( 'voucher.description' ),
            'image' => __( 'voucher.image' ),
            'code' => __( 'voucher.code' ),
            'ingredients' => __( 'voucher.ingredients' ),
            'nutritional_values' => __( 'voucher.nutritional_values' ),
            'price' => __( 'voucher.price' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();


        $validator->setAttributeNames( $attributeName )->validate();

        $adjustmentData = json_decode($request->adjustment_data, true);

        if ($request->discount_type == 3) {
            if (!$adjustmentData) {
                return response()->json(['error' => __('Invalid adjustment data')], 422);
            }
        
            $validator = Validator::make($adjustmentData, [
                'buy_products' => ['required', 'array'],
                'buy_quantity' => ['required', 'numeric', 'min:1'], // Added numeric and min validation
                'get_quantity' => ['required', 'numeric', 'min:1'], // Added numeric and min validation
                'get_product' => ['required', 'exists:products,id'],
            ]);
        
            $attributeName = [
                'buy_products' => __('voucher.buy_products'),
                'buy_quantity' => __('voucher.buy_quantity'),
                'get_quantity' => __('voucher.get_quantity'),
                'get_product' => __('voucher.get_product'),
            ];
        
            $validator->setAttributeNames($attributeName)->validate();
        } elseif ($request->discount_type == 2) {
            $validator = Validator::make($adjustmentData, [
                'buy_quantity' => ['required', 'numeric', 'min:1'], // Added numeric and min validation
                'discount_quantity' => ['required', 'numeric', 'min:0'],
            ]);
        
            $attributeName = [
                'buy_quantity' => __('voucher.buy_quantity'),
                'discount_quantity' => __('voucher.discount_quantity'),
                'discount_type' => __('voucher.discount_type'),
            ];
        
            $validator->setAttributeNames($attributeName)->validate();
        }
        
        DB::beginTransaction();

        try {
            $updateVoucher = Voucher::find( $request->id );
    
            $updateVoucher->title = $request->title;
            $updateVoucher->discount_type = $request->discount_type;
            $updateVoucher->type = $request->voucher_type;
            $updateVoucher->description = $request->description;
            $updateVoucher->promo_code = $request->promo_code;
            $updateVoucher->total_claimable = $request->total_claimable;
            $updateVoucher->points_required = $request->points_required;
            $updateVoucher->start_date = $request->start_date;
            $updateVoucher->expired_date = $request->expired_date;
            $updateVoucher->usable_amount = $request->usable_amount;
            $updateVoucher->validity_days = $request->validity_days;
            $updateVoucher->claim_per_user = $request->claim_per_user;
            $updateVoucher->buy_x_get_y_adjustment = $request->adjustment_data;
            
            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'voucher/' . $updateVoucher->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateVoucher->image = $target;
                   $updateVoucher->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateVoucher->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.vouchers' ) ) ] ),
        ] );
    }

    public static function allVouchers( $request ) {

        $vouchers = Voucher::select( 'vouchers.*');

        $filterObject = self::filter( $request, $vouchers );
        $voucher = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $voucher->orderBy( 'vouchers.created_at', $dir );
                    break;
                case 2:
                    $voucher->orderBy( 'vouchers.title', $dir );
                    break;
                case 3:
                    $voucher->orderBy( 'vouchers.description', $dir );
                    break;
            }
        }

            $voucherCount = $voucher->count();

            $limit = $request->length;
            $offset = $request->start;

            $vouchers = $voucher->skip( $offset )->take( $limit )->get();

            if ( $vouchers ) {
                $vouchers->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }

            $totalRecord = Voucher::count();

            $data = [
                'vouchers' => $vouchers,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $voucherCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

    }

    public static function allStocksVouchers( $request ) {

        // Query all vouchers not in vending_machine_stocks
        $vouchers = Voucher::select( 'vouchers.*' )
            ->whereNotIn('id', function ($query) {
                $query->select('voucher_id')
                    ->from('vending_machine_stocks')
                    ->whereNotNull('voucher_id');
            });
    
        $filterObject = self::filter( $request, $vouchers );
        $voucher = $filterObject['model'];
        $filter = $filterObject['filter'];
    
        // Handle sorting
        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $voucher->orderBy( 'vouchers.created_at', $dir );
                    break;
                case 3:
                    $voucher->orderBy( 'vouchers.title', $dir );
                    break;
                case 4:
                    $voucher->orderBy( 'vouchers.description', $dir );
                    break;
            }
        }
    
        $voucherCount = $voucher->count();
    
        $limit = $request->length;
        $offset = $request->start;
    
        // Paginate results
        $vouchers = $voucher->skip( $offset )->take( $limit )->get();
    
        if ( $vouchers ) {
            $vouchers->append( [
                'encrypted_id',
                'image_path',
            ] );
        }
    
        $totalRecord = Voucher::whereNotIn('id', function ($query) {
            $query->select('voucher_id')
                ->from('vending_machine_stocks')
                ->whereNotNull('voucher_id');
        })->count();
    
        $data = [
            'vouchers' => $vouchers,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $voucherCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];
    
        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'vouchers.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'vouchers.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_voucher)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_voucher . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->voucher_type ) ) {
            $model->where( 'type', $request->voucher_type );
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
            $vendingMachineVouchers = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'voucher_id' );
            $model->whereNotIn( 'id', $vendingMachineVouchers );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneVoucher( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $voucher = Voucher::find( $request->id );

        $voucher->append( ['encrypted_id','image_path', 'decoded_adjustment'] );
        
        return response()->json( $voucher );
    }

    public static function deleteVoucher( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'voucher.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Voucher::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.vouchers' ) ) ] ),
        ] );
    }

    public static function updateVoucherStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateVoucher = Voucher::find( $request->id );
            $updateVoucher->status = $updateVoucher->status == 10 ? 20 : 10;

            $updateVoucher->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'voucher' => $updateVoucher,
                    'message_key' => 'update_voucher_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_voucher_failed',
            ], 500 );
        }
    }

    public static function removeVoucherGalleryImage( $request ) {

        $updateFarm = Voucher::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }

    public static function allVouchersForVendingMachine( $request ) {

        $vouchers = Voucher::select( 'vouchers.*');

        $filterObject = self::filter( $request, $vouchers );
        $voucher = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $voucher->orderBy( 'vouchers.created_at', $dir );
                    break;
                case 2:
                    $voucher->orderBy( 'vouchers.title', $dir );
                    break;
                case 3:
                    $voucher->orderBy( 'vouchers.description', $dir );
                    break;
            }
        }

        $voucherCount = $voucher->count();

        $limit = $request->length;
        $offset = $request->start;

        $vouchers = $voucher->skip( $offset )->take( $limit )->get();

        if ( $vouchers ) {

            $vouchers->append( [
                'encrypted_id',
                'image_path',
            ] );
        }

        $totalRecord = Voucher::count();

        $data = [
            'vouchers' => $vouchers,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $voucherCount : $totalRecord,
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

    public static function getVouchers( $request )
    {
        if( !$request->user_voucher ){

            $vouchers = Voucher::where('status', 10)
            ->where(function ( $query) {
                $query->where(function ( $query) {
                    $query->whereNull('start_date');
                    $query->whereNull('expired_date');
                });
                $query->orWhere(function ( $query) {
                    $query->where('start_date', '<=', date('Y-m-d 23:59:59'));
                    $query->where('expired_date', '>=', date('Y-m-d 00:00:00'));
                });
            })
            ->whereIn( 'type', [1,2] )
            ->orderBy( 'created_at', 'DESC' );
    
            if ( $request && $request->promo_code) {
                $vouchers->where( 'promo_code', 'LIKE', '%' . $request->promo_code . '%' );
            }

            if ( $request && $request->voucher_id) {
                $vouchers->where( 'id', 'LIKE', '%' . $request->voucher_id . '%' );
            }

            if ( $request && $request->voucher_type) {
                $vouchers->where( 'type', $request->voucher_type );
            }

            if ( $request && $request->discount_type) {
                $vouchers->where( 'discount_type', $request->discount_type );
            }

            $vouchers = $vouchers->get();
            $claimedVoucherIds = UserVoucher::where('user_id', auth()->user()->id)
            ->pluck('voucher_id')
            ->toArray();

            $vouchers = $vouchers->map(function ($voucher) use ($claimedVoucherIds) {
                $voucher->claimed = in_array($voucher->id, $claimedVoucherIds) ? 'claimed' : 'unclaim';
                $voucher->makeHidden( [ 'created_at', 'updated_at', 'type', 'status', 'min_spend', 'min_order', 'buy_x_get_y_adjustment', 'discount_amount' ] );
                $voucher->append(['decoded_adjustment', 'image_path','voucher_type','voucher_type_label']);
                return $voucher;
            });

        }else {
            $vouchers = UserVoucher::with( ['voucher'] )
            ->whereHas( 'voucher', function($query){
                $query->where(function ( $query) {
                    $query->where('discount_type' , '!=', 1 );
                });
            } )
            ->where( 'user_id', auth()->user()->id )
            ->where(function ( $query) {
                $query->where(function ( $query) {
                    $query->whereNull('expired_date');
                });
                $query->orWhere(function ( $query) {
                    $query->where('expired_date', '>=', date('Y-m-d 00:00:00'));
                });
            })
            ->orderBy( 'total_left', 'DESC' );

            if (!empty($request->promo_code)) {
                $voucher->whereHas('voucher', function ($query) use ($request) {
                    $query->where('promo_code', 'LIKE', '%' . $request->promo_code . '%');
                });
            }

            if (!empty($request->voucher_id)) {
                $vouchers->where( 'id', 'LIKE', '%' . $request->voucher_id . '%' );
            }

            $vouchers = $vouchers->get();

            $vouchers = $vouchers->map(function ($voucher){
                $voucher->append( ['voucher_status_label'] );
                $voucher->voucher->append(['decoded_adjustment', 'image_path','voucher_type','voucher_type_label']);
                $voucher->voucher->makeHidden( [ 'created_at', 'updated_at', 'type', 'status', 'min_spend', 'min_order', 'buy_x_get_y_adjustment', 'discount_amount' ] );
                return $voucher;
            });
        }

        return response()->json( [
            'message' => '',
            'message_key' => $request->user_voucher ? 'get_user_voucher_success' : 'get_voucher_success',
            'data' => $vouchers,
        ] );

    }

    public static function validateVoucher( $request )
    {

        $validator = Validator::make( $request->all(), [
            'promo_code' => [ 'required' ],
        ] );

        $attributeName = [
            'promo_code' => __( 'voucher.promo_code' ),
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

        $voucher = Voucher::where('status', 10)
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

        if ( !$voucher ) {
            return response()->json( [
                'message_key' => 'voucher_not_available',
                'message' => __('voucher.voucher_not_available'),
                'errors' => [
                    'voucher' => __('voucher.voucher_not_available')
                ]
            ], 422 );
        }

        // user's usage
        $user = auth()->user();
        $voucherUsages = VoucherUsage::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->get();

        if ( $voucherUsages->count() > $voucher->usable_amount ) {
            return response()->json( [
                'message_key' => 'voucher_you_have_maximum_used',
                'message' => __('voucher.voucher_you_have_maximum_used'),
                'errors' => [
                    'voucher' => __('voucher.voucher_you_have_maximum_used')
                ]
            ], 422 );
        }

        // total claimable
        if ( $voucher->total_claimable <= 0 ) {
            return response()->json( [
                'message_key' => 'voucher_fully_claimed',
                'message' => __('voucher.voucher_fully_claimed'),
                'errors' => [
                    'voucher' => __('voucher.voucher_fully_claimed')
                ]
            ], 422 );
        }

        // check is user able to claim this
        $userVoucher = UserVoucher::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->first();
        if(!$userVoucher){
            $userPoints = $user->wallets->where( 'type', 2 )->first();

            if ( $userPoints->balance < $voucher->points_required ) {

                return response()->json( [
                    'message_key' => 'minimum_points_required',
                    'message' => 'Mininum of ' . $voucher->points_required . ' points is required to claim this voucher',
                    'errors' => [
                        'voucher' => 'Mininum of ' . $voucher->points_required . ' points is required to claim this voucher',
                    ]
                ], 422 );
    
            }
        }

        $cart = Cart::find( $request->cart );

        if ( $voucher->discount_type == 3 ) {

            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
            
            $x = $cart->cartMetas->whereIn( 'product_id', $adjustment->buy_products )->count();

            if ( $x < $adjustment->buy_quantity ) {
                return response()->json( [
                   'required_amount' => $adjustment->buy_quantity,
                   'message' => __( 'voucher.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] ),
                   'message_key' => 'voucher.min_quantity_of_x_' . $adjustment->buy_products[0] . '_' .  Product::find( $adjustment->buy_products[0] )->value( 'title' ) ,
                        'errors' => [
                            'voucher' => __( 'voucher.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] )

                        ]
                ], 422 );
            }

        } else {

            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );

            if ( $cart->total_price < $adjustment->buy_quantity ) {
                return response()->json( [
                    'required_amount' => $adjustment->buy_quantity,
                    'message' => __( 'voucher.min_spend_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id', $adjustment->buy_products[0] )->value( 'title' ) ] ),
                    'message_key' => 'voucher.min_spend_of_x',
                    'errors' => [
                        'voucher' => __( 'voucher.min_spend_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id', $adjustment->buy_products[0] )->value( 'title' ) ] )
                    ]
                ], 422 );
            }

        }
    
        return response()->json( [
            'message' => 'voucher.voucher_validated',
        ] );
    }

    public static function claimVoucher( $request )
    {

        $validator = Validator::make( $request->all(), [
            'voucher_id' => [ 'required' ],
        ] );

        $attributeName = [
            'voucher_id' => __( 'voucher.voucher_id' ),
        ];
        
        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $voucher = Voucher::where( 'id', $request->voucher_id )
        ->orWhere( 'promo_code', $request->voucher_id )
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

        if ( !$voucher ) {
            return response()->json( [
                'message_key' => 'voucher_not_available',
                'message' => __('voucher.voucher_not_available'),
                'errors' => [
                    'voucher' => __('voucher.voucher_not_available'),
                ]
            ], 422 );
        }
        $user = auth()->user();

        $voucherUsages = VoucherUsage::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->get();

        if ( $voucherUsages->count() > $voucher->usable_amount ) {
            return response()->json( [
                'message_key' => 'voucher_fully_claimed',
                'message' => __('voucher.voucher_fully_claimed'),
                'errors' => [
                    'voucher' => __('voucher.voucher_fully_claimed'),
                ]
            ], 422 );
        }

        $voucherUserClaimed = UserVoucher::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->count();

        if ( $voucherUserClaimed >= $voucher->claim_per_user ) {
            return response()->json( [
                'message_key' => 'voucher_you_have_maximum_claimed',
                'message' => __('voucher.voucher_you_have_maximum_claimed'),
                'errors' => [
                    'voucher' => __('voucher.voucher_you_have_maximum_claimed'),
                ]
            ], 422 );
        }
        
        $userPoints = $user->wallets->where( 'type', 2 )->first();

        if ( $userPoints->balance < $voucher->points_required ) {

            return response()->json( [
                'required_amount' => $voucher->points_required,
                'message' => 'Mininum of ' . $voucher->points_required . ' points is required to claim this voucher',
                'message_key' => 'minimum_points_is_required',
                'errors' => [
                    'voucher' => 'Mininum of ' . $voucher->points_required . ' points is required to claim this voucher',
                ]
            ], 422 );

        }        
        
        if ( $voucher->total_claimable <= 0 ) {
            return response()->json( [
                'message_key' => 'voucher_fully_claimed',
                'message' => __('voucher.voucher_fully_claimed'),
                'errors' => [
                    'voucher' => __('voucher.voucher_fully_claimed')
                ]
            ], 422 );
        }

        WalletService::transact( $userPoints, [
            'amount' => -$voucher->points_required,
            'remark' => 'Claim Voucher',
            'type' => $userPoints->type,
            'transaction_type' => 11,
        ] );

        $userVoucher = UserVoucher::create([
            'user_id' => $user->id,
            'voucher_id' => $voucher->id,
            'expired_date' => Carbon::now()->addDays($voucher->validity_days),
            'status' => 10,
            'redeem_from' => 1,
            'total_left' => 1,
            'used_at' => null,
            'secret_code' => strtoupper( \Str::random( 8 ) ),
        ]);

        $voucher->total_claimable -= 1;
        $voucher->save();
    
        // notification
        UserService::createUserNotification(
            $user->id,
            'notification.user_voucher_success',
            'notification.user_voucher_success_content',
            'voucher',
            'voucher'
        );

        self::sendNotification( $order->user, 'voucher', __( 'notification.user_voucher_success_content' )  );

        return response()->json( [
            'message' => __('voucher.voucher_claimed'),
            'message_key' => 'voucher_claimed',
            'data' => $userVoucher->load(['voucher'])
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