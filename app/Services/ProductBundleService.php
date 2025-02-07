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
    ProductBundle,
    ProductBundleGallery,
    Booking,
    FileManager,
    ProductBundleVariant,
    Froyo,
    Syrup,
    Topping,
    ProductBundleMeta,
    User,
    UserBundle,
    UserBundleTransaction,
    Option,
    Order,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Carbon\Carbon;

class ProductBundleService
{

    public static function createProductBundle( $request ) {

        $validator = Validator::make( $request->all(), [
            'code' => [ 'nullable' ],
            'title' => [ 'nullable' ],
            'description' => [ 'nullable' ],
            'price' => [ 'required' ],
            'discount_price' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'products' => [ 'required' ],
            'quantity' => [ 'required', 'min:1' ],
            'validity_days' => [ 'nullable', 'min:1' ],
            'quantities' => ['nullable', 'array'],
            'quantities.*' => ['required', 'integer', 'min:1'],
            
        ] );

        $attributeName = [
            'title' => __( 'product_bundle.title' ),
            'description' => __( 'product_bundle.description' ),
            'image' => __( 'product_bundle.image' ),
            'code' => __( 'product_bundle.code' ),
            'price' => __( 'product_bundle.price' ),
            'discount_price' => __( 'product_bundle.discount_price' ),
            'image' => __( 'product_bundle.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $productBundleCreate = ProductBundle::create([
                'code' => $request->code,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'discount_price' => $request->discount_price,
                'validity_days' => $request->validity_days,
                'status' => 10,
            ]);

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'froyo/' . $productBundleCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $productBundleCreate->image = $target;
                   $productBundleCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            // bundle metas
            $products = explode( ',', $request->products );

            foreach ($products as $product) {

                ProductBundleMeta::create([
                    'product_id' => $product,
                    'product_bundle_id' => $productBundleCreate->id,
                    'quantity' => $request->quantities[$product],
                    'status' => 10,
                ]);
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.product_bundles' ) ) ] ),
        ] );
    }
    
    public static function updateProductBundle( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
    
        $validator = Validator::make( $request->all(), [
            'code' => [ 'nullable' ],
            'title' => [ 'nullable' ],
            'description' => [ 'nullable' ],
            'price' => [ 'required' ],
            'discount_price' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'products' => [ 'required' ],
            'quantity' => [ 'required', 'min:1' ],
            'validity_days' => [ 'nullable', 'min:1' ],
            'quantities' => ['nullable', 'array'],
            'quantities.*' => ['required', 'integer', 'min:1'],
        ] );

        $attributeName = [
            'title' => __( 'product_bundle.title' ),
            'description' => __( 'product_bundle.description' ),
            'image' => __( 'product_bundle.image' ),
            'code' => __( 'product_bundle.code' ),
            'price' => __( 'product_bundle.price' ),
            'discount_price' => __( 'product_bundle.discount_price' ),
            'default_froyo_quantity' => __( 'product_bundle.default_froyo_quantity' ),
            'default_syrup_quantity' => __( 'product_bundle.default_syrup_quantity' ),
            'default_topping_quantity' => __( 'product_bundle.default_topping_quantity' ),
            'image' => __( 'product_bundle.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateProductBundle = ProductBundle::with(['productBundleMetas'])->find( $request->id );
  
            $updateProductBundle->code = $request->code ?? $updateProductBundle->code;
            $updateProductBundle->title = $request->title ?? $updateProductBundle->title;
            $updateProductBundle->description = $request->description ?? $updateProductBundle->description;
            $updateProductBundle->price = $request->price ?? $updateProductBundle->price;
            $updateProductBundle->validity_days = $request->validity_days ?? $updateProductBundle->validity_days;
            $updateProductBundle->discount_price = $request->discount_price ?? $updateProductBundle->discount_price;

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'product/' . $updateProductBundle->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateProductBundle->image = $target;
                   $updateProductBundle->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateProductBundle->save();

            $updateProductBundle->productBundleMetas()->delete();
            // bundle metas
            $products = explode( ',', $request->products );

            foreach ($products as $product) {
                ProductBundleMeta::create([
                    'product_id' => $product,
                    'product_bundle_id' => $updateProductBundle->id,
                    'quantity' => $request->quantities[$product],
                    'status' => 10,
                ]);
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.product_bundles' ) ) ] ),
        ] );
    }

    public static function allProductBundles( $request ) {

        $productBundles = ProductBundle::with(['productBundleMetas.product'])->select( 'product_bundles.*' );

        $filterObject = self::filter( $request, $productBundles );
        $productBundle = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $productBundle->orderBy( 'product_bundles.created_at', $dir );
                    break;
                case 1:
                    $productBundle->orderBy( 'product_bundles.id', $dir );
                    break;
                case 3:
                    $productBundle->orderBy( 'product_bundles.title', $dir );
                    break;
                case 4:
                    $productBundle->orderBy( 'product_bundles.description', $dir );
                    break;
            }
        }

            $productBundleCount = $productBundle->count();

            $limit = $request->length;
            $offset = $request->start;

            $productBundles = $productBundle->skip( $offset )->take( $limit )->get();

            if ( $productBundles ) {

                $productBundles->append( [
                    'encrypted_id',
                    'image_path',
                ] );

                foreach( $productBundles as $productBundle ){
                    if( $productBundle->productBundleMetas ){
                        $pbms = $productBundle->productBundleMetas;
                        foreach( $pbms as $pbm ){
                            $pbm->product->append( [
                                'image_path',
                            ] );
                        }
                    }
                }

            }

            $totalRecord = ProductBundle::count();

            $data = [
                'product_bundles' => $productBundles,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $productBundleCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->name ) ) {
            $model->where('title', 'LIKE', '%' . $request->name . '%')
            ->orWhereHas('variants', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->name . '%');
            })->orWhereHas('bundles', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->name . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->title ) ) {
            $model->where( 'product_bundles.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'product_bundles.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->code ) ) {
            $model->where( 'product_bundles.code', 'LIKE', '%' . $request->code . '%' );
            $filter = true;
        }
        
        if ( !empty( $request->id ) ) {
            $model->where( 'product_bundles.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->brand)) {
            $model->whereHas('brand', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->brand . '%');
            });
            $filter = true;
        }

        if (!empty($request->category)) {
            $model->whereHas('categories', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->category . '%');
            });
            $filter = true;
        }

        if (!empty($request->unit)) {
            $model->whereHas('unit', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->unit . '%');
            });
            $filter = true;
        }
        
        if (!empty($request->warehouse)) {
            $model->whereHas('warehouses', function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse);
            });
            $filter = true;
        }

        if (!empty($request->product)) {
            $model->whereHas('productBundleMetas', function ($query) use ($request) {
                $model->whereHas('product', function ($query) use ($request) {
                    $query->where('product.title', 'LIKE' . '%' . $request->product. '%');
                });
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'product_bundles.status', $request->status );
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

    public static function oneProductBundle( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $productBundle = ProductBundle::with( ['productBundleMetas.product'] )->select( 'product_bundles.*' )->find( $request->id );

        if ( $productBundle ) {
            $productBundle->append( [
                'encrypted_id',
                'image_path',
            ] );

            if( $productBundle->productBundleMetas ){
                if( $productBundle->productBundleMetas ){
                    $pbms = $productBundle->productBundleMetas;
                    foreach( $pbms as $pbm ){
                        $pbm->product->append( [
                            'image_path',
                        ] );
                    }
                }
            }
        }
        
        return response()->json( $productBundle );
    }

    public static function deleteProductBundle( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'product_bundle.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            ProductBundle::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.product_bundles' ) ) ] ),
        ] );
    }

    public static function updateProductBundleStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateProductBundle = ProductBundle::find( $request->id );
            $updateProductBundle->status = $updateProductBundle->status == 10 ? 20 : 10;

            $updateProductBundle->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'product' => $updateProductBundle,
                    'message_key' => 'update_product_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_product_failed',
            ], 500 );
        }
    }

    public static function getBundles( $request )
    {
        if( !$request->user_bundle ){

            $productbundles = ProductBundle::where('status', 10)
            ->orderBy( 'created_at', 'DESC' );
    
            if ( $request && $request->title) {
                $productbundles->where( 'title', 'LIKE', '%' . $request->title . '%' );
            }

            if ( $request && $request->bundle_id) {
                $productbundles->where( 'id', 'LIKE', '%' . $request->bundle_id . '%' );
            }

            $productbundles = $productbundles->get();
            $claimedBundleIds = UserBundle::where('user_id', auth()->user()->id)
            ->pluck('product_bundle_id')
            ->toArray();

            $productbundles = $productbundles->map(function ($productbundle) use ($claimedBundleIds) {
                $productbundle->claimed = in_array($productbundle->id, $claimedBundleIds) ? 'purchased' : 'not purchased';
                $productbundle->append( ['image_path','bundle_rules'] );
                return $productbundle;
            });

        }else {
            $productbundles = UserBundle::with([
                'productBundle',
                'activeCarts.cartMetas' // Load cartMetas for activeCarts
            ])
            ->where('user_id', auth()->user()->id)
            ->where(function ($query) {
                $query->where('cups_left', '>', 0)
                      ->orWhereHas('activeCarts');
            })
            ->orderBy('created_at', 'DESC');
        

            if ( $request && $request->title) {
                $productbundles->where( 'title', 'LIKE', '%' . $request->title . '%' );
            }

            if ( $request && $request->bundle_id) {
                $productbundles->where( 'id', 'LIKE', '%' . $request->bundle_id . '%' );
            }

            $productbundles = $productbundles->get();

            $productbundles = $productbundles->map(function ($productbundle){
                $productbundle->append( ['bundle_status_label'] );
                $productbundle->productBundle->append( ['image_path','bundle_rules'] );
                $productbundle->bundle_rules = $productbundle->productBundle->bundle_rules;
                $productbundle->cups_in_cart = $productbundle->activeCarts->sum(function ($cart) {
                    return $cart->cartMetas->count();
                });

                if( $productbundle->activeCarts ){
                    foreach( $productbundle->activeCarts as $cart ){

                        if($cart->vendingMachine){
                            $cart->vendingMachine->makeHidden(['created_at', 'updated_at', 'status'])
                            ->setAttribute('operational_hour', $cart->vendingMachine->operational_hour)
                            ->setAttribute('image_path', $cart->vendingMachine->image_path);
                        }

                        $cartMetas = $cart->cartMetas->map(function ($meta) {
                            return [
                                'id' => $meta->id,
                                'subtotal' => $meta->total_price,
                                'product' => $meta->product?->makeHidden(['created_at', 'updated_at', 'status'])
                                    ->setAttribute('image_path', $meta->product->image_path),
                                'froyo' => $meta->froyos_metas,
                                'syrup' => $meta->syrups_metas,
                                'topping' => $meta->toppings_metas,
                            ];
                        });
                
                        // Attach the cart metas to the cart object
                        $cart->cartMetas = $cartMetas;
                    }

                    foreach( $productbundle->activeCarts as $userCart ) {
                        $userCart->cart_metas = $userCart->cartMetas;
                        // $userCart->cartMetas = null;
                        unset($userCart->cartMetas);
                        $userCart->cartMetas = $userCart->cart_metas;

                    }
                }

                return $productbundle;
            });
        }

        foreach ($productbundles as $productbundle) {
            $productbundle->makeHidden('productBundleMetas');
            if($productbundle->productBundle){
                $productbundle->productBundle->makeHidden('productBundleMetas');
            }
        }

        return response()->json( [
            'message' => '',
            'message_key' => $request->user_bundle ? 'get_user_bundle_success' : 'get_product_bundle_success',
            'data' => $productbundles,
        ] );

    }

    public static function buyBundle( $request ) {

        $validator = Validator::make($request->all(), [
            'bundle_id' => ['required', 'exists:product_bundles,id'],
            'payment_method' => ['nullable', 'in:1,2'],
        ]);

        $user = auth()->user();
        
        $validator->validate();
        
        // check wallet balance 
        $userWallet = $user->wallets->where('type',1)->first();
        $bundle = ProductBundle::find( $request->bundle_id );

        if( $request->payment_method == 1 ){

            if (!$userWallet) {
                return response()->json([
                    'message' => 'Wallet Not Found',
                    'message_key' => 'wallet_not_found',
                    'errors' => [
                        'wallet' => 'Wallet not found',
                    ]
                ], 422);
            }else{
                if( $userWallet->balance < $bundle->price ){
                    return response()->json([
                        'message' => 'Balance is not enough, please top up to continue',
                        'message_key' => 'insufficient_balance',
                        'errors' => [
                            'wallet' => 'Balance is not enough, please top up to continue',
                        ]
                    ], 422);
                }
            }
        }

        DB::beginTransaction();
        try {
        
            $orderPrice = 0;
            $user = auth()->user();
            $userWallet = $user->wallets->where( 'type', 1 )->first();
            $bundle = ProductBundle::find( $request->bundle_id );

            $bundleCupLeft = [];
            foreach($bundleMetas as $key => $bundleMeta){
                $bundleCupLeft[$bundleMeta->product_id] = $bundleMeta->quantity;
            }

            $userBundle = UserBundle::create([
                'user_id' => $user->id,
                'product_bundle_id' => $bundle->id,
                'status' => $request->payment_method == 1 ? 10 : 20,
                'total_cups' => $bundle->productBundleMetas->sum('quantity'),
                'cups_left' => $bundle->productBundleMetas->sum('quantity'),
                'cups_left_metas' => json_encode( $bundleCupLeft ),
                'last_used' => null,
                'payment_attempt' => 1,
                'payment_url' => 'null',
            ]);

            $bundleTransaction = UserBundleTransaction::create( [
                'user_id' => $user->id,
                'product_bundle_id' => $bundle->id,
                'user_bundle_id' => $userBundle->id,
                'reference' => Helper::generateBundleReference(),
                'price' => $bundle->price,
                'status' => 10,
                'payment_attempt' => 1,
                'payment_url' => 'null',
            ] );

            $order = Order::create( [
                'user_id' => $user->id,
                'product_id' => null,
                'product_bundle_id' => $bundle->id,
                'outlet_id' => null,
                'vending_machine_id' => null,
                'user_bundle_id' => $userBundle->id,
                'total_price' => $bundle->price,
                'discount' => 0,
                'status' => 1,
                'reference' => Helper::generateOrderReference(),
                'tax' => 0,
            ] );

            if( $request->payment_method == 1 ){
                
                WalletService::transact( $userWallet, [
                    'amount' => -$bundle->price,
                    'remark' => 'Bundle Purchased: ' . $bundle->title,
                    'type' => $userWallet->type,
                    'transaction_type' => 10,
                ] );

                // assign purchasing bonus
                $spendingBonus = Option::getSpendingSettings();
                if( $spendingBonus ){

                    $userBonusWallet = $user->wallets->where( 'type', 2 )->first();

                    WalletService::transact( $userBonusWallet, [
                        'amount' => $bundle->price * $spendingBonus->option_value,
                        'remark' => 'Purchase Bonus',
                        'type' => 2,
                        'transaction_type' => 22,
                    ] );
                }

                // assign referral's purchasing bonus
                $referralSpendingBonus = Option::getReferralSpendingSettings();
                if( $user->referral && $referralSpendingBonus){

                    $referralWallet = $user->referral->wallets->where('type',2)->first();

                    if($referralWallet){
                        WalletService::transact( $referralWallet, [
                            'amount' => $bundle->price * $referralSpendingBonus->option_value,
                            'remark' => 'Referral Purchase Bonus',
                            'type' => $referralWallet->type,
                            'transaction_type' => 22,
                        ] );
                    }
                    
                }

                $order->status = 10;
                $order->save();

            }else {
                
                $data = [
                    'TransactionType' => 'SALE',
                    'PymtMethod' => 'ANY',
                    'ServiceID' => config('services.eghl.merchant_id'),
                    'PaymentID' => $bundleTransaction->reference . '-' . $bundleTransaction->payment_attempt,
                    'OrderNumber' => $bundleTransaction->reference,
                    'PaymentDesc' => $bundleTransaction->reference,
                    'MerchantName' => 'Yobe Froyo',
                    'MerchantReturnURL' => config('services.eghl.staging_callabck_url'),
                    'MerchantApprovalURL' => config('services.eghl.staging_success_url'),
                    'MerchantUnApprovalURL' => config('services.eghl.staging_failed_url'),
                    'Amount' => $bundleTransaction->price,
                    'CurrencyCode' => 'MYR',
                    'CustIP' => request()->ip(),
                    'CustName' => $bundleTransaction->user->username ?? 'Yobe Guest',
                    'HashValue' => '',
                    'CustEmail' => $bundleTransaction->user->email ?? 'yobeguest@gmail.com',
                    'CustPhone' => $bundleTransaction->user->phone_number,
                    'MerchantTermsURL' => null,
                    'LanguageCode' => 'en',
                    'PageTimeout' => '780',
                ];

                $data['HashValue'] = Helper::generatePaymentHash($data);
                $url2 = config('services.eghl.test_url') . '?' . http_build_query($data);
                $bundleTransaction->payment_url = $url2;
                $userBundle->payment_url = $url2;

            }

            $bundleTransaction->save();
            $userBundle->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }
        
        return response()->json( [
            'message' => '',
            'message_key' => $request->payment_method == 1  ? 'purchase_bundle_success' :'please proceed to payment',
            'payment_url' => $bundleTransaction->payment_url,
            'bundle' => $userBundle,
        ] );
    }

    public static function retryPayment( $request ) {

        $validator = Validator::make($request->all(), [
            'user_bundle_id' => ['required', 'exists:user_bundles,id'],
        ]);

        $user = auth()->user();
        
        $bundle = UserBundle::where('id', $request->user_bundle_id)
        ->where('status', 20)
        ->where('user_id', auth()->user()->id )
        ->first();

        if (!$bundle) {
            return response()->json([
                'message' => '',
                'message_key' => 'user_bundle_not_available',
                'errors' => [
                    'order' => 'user bundle not available'
                ]
            ], 422);
        }

        $validator->validate();

        DB::beginTransaction();
        try {
        
            $orderPrice = 0;
            $user = auth()->user();

            $userBundle = UserBundle::where('id', $request->user_bundle_id)
            ->where('status', 20)
            ->where('user_id', auth()->user()->id )
            ->first();

            $bundleTransaction = UserBundleTransaction::create( [
                'user_id' => $user->id,
                'product_bundle_id' => $userBundle->product_bundle_id,
                'user_bundle_id' => $userBundle->id,
                'reference' => Helper::generateBundleReference(),
                'price' => $userBundle->productBundle->price,
                'status' => 10,
                'payment_attempt' => 1,
                'payment_url' => 'null',
            ] );
                
            $data = [
                'TransactionType' => 'SALE',
                'PymtMethod' => 'ANY',
                'ServiceID' => config('services.eghl.merchant_id'),
                'PaymentID' => $bundleTransaction->reference . '-' . $bundleTransaction->payment_attempt,
                'OrderNumber' => $bundleTransaction->reference,
                'PaymentDesc' => $bundleTransaction->reference,
                'MerchantName' => 'Yobe Froyo',
                'MerchantReturnURL' => config('services.eghl.staging_callabck_url'),
                'MerchantApprovalURL' => config('services.eghl.staging_success_url'),
                'MerchantUnApprovalURL' => config('services.eghl.staging_failed_url'),
                'Amount' => $bundleTransaction->price,
                'CurrencyCode' => 'MYR',
                'CustIP' => request()->ip(),
                'CustName' => $bundleTransaction->user->username ?? 'Yobe Guest',
                'HashValue' => '',
                'CustEmail' => $bundleTransaction->user->email ?? 'yobeguest@gmail.com',
                'CustPhone' => $bundleTransaction->user->phone_number,
                'MerchantTermsURL' => null,
                'LanguageCode' => 'en',
                'PageTimeout' => '780',
            ];

            $data['HashValue'] = Helper::generatePaymentHash($data);
            $url2 = config('services.eghl.test_url') . '?' . http_build_query($data);
            $bundleTransaction->payment_url = $url2;
            $userBundle->payment_url = $url2;
            $bundleTransaction->save();
            $userBundle->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }
        
        return response()->json( [
            'message' => '',
            'message_key' => 'please proceed to payment',
            'payment_url' => $bundleTransaction->payment_url,
            'bundle' => $userBundle,
        ] );
    }

}