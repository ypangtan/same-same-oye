<?php

namespace App\Services;

use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    Cart,
    CartMeta,
    Froyo,
    Syrup,
    Topping,
    Product,
    Voucher,
    VoucherUsage,
    UserVoucher,
    ProductBundle,
    ProductBundleMeta,
    UserBundle,
    UserBundleHistory,
    UserBundleHistoryMeta,
    Option,
    UserNotification,
};

use App\Services\{
    ProductService,
};

use Helper;

use Carbon\Carbon;

class CartService {

    public static function getCart($request)
    {
        // Validate the incoming request parameters (id and session_key)
        $validator = Validator::make($request->all(), [
            'id' => ['nullable', 'exists:carts,id'],
            'session_key' => ['nullable', 'exists:carts,session_key'],
            'per_page' => ['nullable', 'integer', 'min:1'], // Validate per_page input
        ]);
        
        // If validation fails, it will automatically throw an error
        $validator->validate();
    
        // Get the current authenticated user
        $user = auth()->user();
    
        // Start by querying carts for the authenticated user
        $query = Cart::where('user_id', $user->id)
            ->with(['cartMetas', 'vendingMachine', 'voucher', 'productBundle'])
            ->where('status', 10)
            ->orderBy('created_at', 'DESC');
    
        // Apply filters if 'id' or 'session_key' is provided
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        if ($request->has('session_key')) {
            $query->where('session_key', $request->session_key);
        }
    
        // Use paginate instead of get
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $userCarts = $query->paginate($perPage);
    
        // Modify each cart and its related data
        $userCarts->getCollection()->transform(function ($cart) {
            // Make vending machine attributes hidden and add additional attributes
            if($cart->voucher){
                $cart->voucher->makeHidden( [ 'created_at', 'updated_at', 'type', 'status', 'min_spend', 'min_order', 'buy_x_get_y_adjustment', 'discount_amount' ] )
                ->append(['decoded_adjustment', 'image_path','voucher_type','voucher_type_label']);
            }

            if($cart->vendingMachine){
                $cart->vendingMachine->makeHidden(['created_at', 'updated_at', 'status'])
                ->setAttribute('operational_hour', $cart->vendingMachine->operational_hour)
                ->setAttribute('image_path', $cart->vendingMachine->image_path);
            }
    
            // Process each cart meta data
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
            
            if( !$cart->tax ) {
                $taxSettings = Option::getTaxesSettings();
                $cart->tax = Helper::numberFormatV2( ( $taxSettings ? (Helper::numberFormatV2(($taxSettings->option_value/100),2) * $cart->total_price) : 0 ), 2, true);
            }
    
            return $cart;
        });

        foreach( $userCarts as $userCart ) {
            $userCart->cart_metas = $userCart->cartMetas;
            // $userCart->cartMetas = null;
            unset($userCart->cartMetas);
            $userCart->cartMetas = $userCart->cart_metas;

        }
    
        // Return the response with the paginated carts data
        return response()->json([
            'message' => '',
            'message_key' => 'get_cart_success',
            'carts' => $userCarts,
        ]);
    }

    public static function addToCart( $request ) {

        if( !isset( $request->items ) ) {
            $request->merge(['items'=> []]);
        }

        $validator = Validator::make($request->all(), [
            'bundle' => [ 'nullable', 'exists:product_bundles,id'  ],
            'user_bundle' => [ 'nullable', 'exists:user_bundles,id'  ],
            'vending_machine' => [ 'nullable', 'exists:vending_machines,id'  ],
            'items' => ['nullable', 'array'],
            'items.*.product' => ['required', 'exists:products,id'],
            'items.*.froyo' => ['nullable', 'array'],
            'items.*.froyo.*' => ['exists:froyos,id'], // Validate each froyo ID
            'items.*.syrup' => ['nullable', 'array'],
            'items.*.syrup.*' => ['exists:syrups,id'], // Validate each syrup ID
            'items.*.topping' => ['nullable', 'array'],
            'items.*.topping.*' => ['exists:toppings,id'], // Validate each topping ID
             'promo_code' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $existsInPromoCode = \DB::table('vouchers')->where('promo_code', $value)->exists();
                    $existsInId = \DB::table('vouchers')->where('id', $value)->exists();

                    if (!$existsInPromoCode && !$existsInId) {
                        $fail(__('The :attribute must exist in either the promo_code or id column.'));
                    }
                },
            ],
        ]);

        if (isset($request->items)) {
            $validator->after(function ($validator) use ($request) {
                foreach ($request->items as $index => $item) {
                    // Fetch the product and its default quantities
                    $product = Product::find($item['product']);

                    if (!$product) {
                        $validator->errors()->add("items.$index.product", 'Invalid product ID.');
                        continue;
                    }

                    // Check froyo quantity
                    if (isset($item['froyo']) && count($item['froyo']) > $product->default_froyo_quantity) {
                        $validator->errors()->add("items.$index.froyo", "You can select up to {$product->default_froyo_quantity} froyos.");
                    }
        
                    // Check syrup quantity
                    if (isset($item['syrup']) && count($item['syrup']) > $product->default_syrup_quantity) {
                        $validator->errors()->add("items.$index.syrup", "You can select up to {$product->default_syrup_quantity} syrups.");
                    }
        
                    // Check topping quantity
                    if (isset($item['topping']) && count($item['topping']) > $product->default_topping_quantity) {
                        $validator->errors()->add("items.$index.topping", "You can select up to {$product->default_topping_quantity} toppings.");
                    }
                }
            });
        }
        
        if ($validator->fails()) {
            $rawErrors = $validator->errors()->toArray();
            $formattedErrors = [
                'vending_machine' => $rawErrors['vending_machine'][0] ?? null, // Include vending machine error
                'promo_code' => $rawErrors['promo_code'][0] ?? null, // Include promo_code error
                'bundle' => $rawErrors['bundle'][0] ?? null, // Include bundle error
                'user_bundle' => $rawErrors['user_bundle'][0] ?? null, // Include bundle error
                'items' => []
            ];

            foreach ($rawErrors as $key => $messages) {
                // Handle items validation errors
                if (preg_match('/items\.(\d+)\.(\w+)/', $key, $matches)) {
                    $index = $matches[1]; // Extract index (e.g., 0)
                    $field = $matches[2]; // Extract field (e.g., froyo)
        
                    // Group errors by index
                    if (!isset($formattedErrors['items'][$index])) {
                        $formattedErrors['items'][$index] = [];
                    }
        
                    $formattedErrors['items'][$index][$field] = $messages[0]; // Add the first error message
                }
            }

            // Remove null vending machine error if not present
            if (!$formattedErrors['vending_machine']) {
                unset($formattedErrors['vending_machine']);
            }

            if (!$formattedErrors['promo_code']) {
                unset($formattedErrors['promo_code']);
            }

            if (!$formattedErrors['bundle']) {
                unset($formattedErrors['bundle']);
            }

            if (!$formattedErrors['user_bundle']) {
                unset($formattedErrors['user_bundle']);
            }

            return response()->json(["message"=> "The given data was invalid.",'errors' => $formattedErrors], 422);
        }
        // end of laravel validation

        // custom validation
        // bundle rules
        $validateCBR = self::validateCartBundleRules($request);

        if ($validateCBR->getStatusCode() === 422) {
            return $validateCBR;
        }

        // voucher rules
        if ( $request->promo_code ) {

            $voucher = Voucher::where( 'id', $request->promo_code )
            ->orWhere('promo_code', $request->promo_code)->first();

            if( !$voucher ){
                return response()->json( [
                    'message' => 'Voucher not found',
                    'message_key' => 'voucher_not_found',
                    'errors' => [
                        'voucher' => 'Voucher not found'
                    ]
                ] , 422);
            }

            $test = self::validateCartVoucher($request);

            if ($test->getStatusCode() === 422) {
                return $test;
            }
        }

        // validate bundle product
        if( $request->bundle ){

            $bundle = ProductBundle::where( 'id', $request->bundle )->where( 'status', 10 )->first();
            $bundleRules = $bundle->bundle_rules;
            $bundleMetaRules = $bundle->bundle_meta_rules; //mix product bundle

            $isValid = true;
            $error = 0;

            // if ( ( isset( $request->items ) ? count($request->items) : 0 ) > $bundleRules['quantity'] ) {

            //     return response()->json( [
            //         'message' => 'Product exceeeds bundle quantity',
            //         'message_key' => 'product_exceeds_bundle_quantity',
            //         'errors' => [
            //             'bundle' => 'Product exceeeds bundle quantity',
            //         ]
            //     ] , 422);
            // }

            foreach ($bundleMetaRules as $rule) {
                $items = collect( $request->items );
                $selectedQuantity = $items->where('product', $rule['product']['id'])->count();

                if ($selectedQuantity > $rule['quantity']) {
                    return response()->json([
                        'message' => "Quantity for {$rule['product']['title']} exceeds the limit.",
                        'errors' => ['products' => "Selected: $selectedQuantity, Allowed: {$rule['quantity']}"]
                    ], 422);
                }
            }

            foreach ($request->items as $item) {
                if ( !collect($bundleMetaRules)->firstWhere('product.id', $item['product']) ) {
                    $isValid = false;
                    $error = 1;
                    break;
                }

                $validator = Validator::make([], []); // Initialize an empty validator

                foreach ($request->items as $index => $item) {
                    $product = $bundleRules['product']; // Access the product object from bundle rules
                
                    // Check froyo quantity
                    if (isset($item['froyo']) && count($item['froyo']) > $product->default_froyo_quantity) {
                        $validator->errors()->add(
                            "items.$index.froyo",
                            "You can select up to {$product->default_froyo_quantity} froyo(s)."
                        );
                    }
                
                    // Check syrup quantity
                    if (isset($item['syrup']) && count($item['syrup']) > $product->default_syrup_quantity) {
                        $validator->errors()->add(
                            "items.$index.syrup",
                            "You can select up to {$product->default_syrup_quantity} syrup(s)."
                        );
                    }
                
                    // Check topping quantity
                    if (isset($item['topping']) && count($item['topping']) > $product->default_topping_quantity) {
                        $validator->errors()->add(
                            "items.$index.topping",
                            "You can select up to {$product->default_topping_quantity} topping(s)."
                        );
                    }
                
                    // Check if product ID matches bundle rule's product ID
                    if ($item['product'] !== $product->id) {
                        $validator->errors()->add(
                            "items.$index.product",
                            "The product ID must match the bundle's product ID: {$product->id}."
                        );
                    }
                }
                
                // If errors are present, return them in the response
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 400);
                }
            }

            if (!$isValid) {

                if( $error == 1 ){
                    return response()->json( [
                        'message' => 'Product not available in bundle',
                        'message_key' => 'product_not_available_in_bundle',
                        'errors' => [
                            'bundle' => 'Product not available in bundle',
                        ]
                    ] , 422);
                } else {
                    return response()->json( [
                        'message' => 'Bundle cant be used together with promotion',
                        'message_key' => 'bundle_not_available',
                        'errors' => [
                            'bundle' => 'Bundle cant be used together with promotion',
                        ]
                    ] , 422);
                }
            }
        }

        // validate bundle product
        if( $request->user_bundle ){

            $userBundle = UserBundle::where( 'id', $request->user_bundle )->where( 'user_id', auth()->user()->id )->where( 'status', 10 )->first();

            if ( !$userBundle ) {
                return response()->json( [
                    'message' => 'User Bundle Not Found',
                    'message_key' => 'user_bundle_not_found',
                    'errors' => [
                        'user_bundle' => 'User Bundle Not Found',
                    ]
                ], 422 );
            }

            if ( !isset( $request->items ) ) {
                return response()->json( [
                    'message' => 'Please add item to bundle',
                    'message_key' => 'please_add_item_to_bundle',
                    'errors' => [
                        'user_bundle' => 'Please add item to bundle',
                    ]
                ], 422 );
            }

            if ( count( $request->items ) == 0 ) {
                return response()->json( [
                    'message' => 'Please add item to bundle',
                    'message_key' => 'please_add_item_to_bundle',
                    'errors' => [
                        'user_bundle' => 'Please add item to bundle',
                    ]
                ], 422 );
            }

            // check own bundle's cups left
            if ( ( isset( $request->items ) ? count($request->items) : 0 ) > $userBundle->cups_left ) {
                return response()->json( [
                    'message' => 'You have redeemed all cups from bundle',
                    'message_key' => 'no_cups_left',
                    'errors' => [
                        'user_bundle' => 'You have redeemed all cups from bundle',
                    ]
                ], 422 );
            }

            // check for specific product cups left
            if($userBundle->cups_left_metas){
                $cupLeftMetas = json_decode($userBundle->cups_left_metas,true);

                foreach ($cupLeftMetas as $product => $quantity) {
                    $items = collect( $request->items );
                    $selectedQuantity = $items->where('product', $product)->count();
    
                    if ($selectedQuantity > $quantity) {
                        $productName = Product::find($product)->title;
                        return response()->json([
                            'message' => "Quantity for {$productName} exceeds your remaining cups.",
                            'errors' => ['products' => "Selected: $selectedQuantity, Allowed: {$quantity}"]
                        ], 422);
                    }
                }
            }

            $bundleRules = $userBundle->productBundle->bundle_rules;
            $bundleMetaRules = $userBundle->productBundle->bundle_meta_rules;

            $isValid = true;
            $error = 0;
            
            // if ( ( isset( $request->items ) ? count($request->items) : 0 ) > $bundleRules['quantity'] ) {
            //     return response()->json( [
            //         'message' => 'Product exceeeds bundle quantity',
            //         'message_key' => 'product_exceeds_bundle_quantity',
            //         'errors' => [
            //             'user_bundle' => 'Product exceeeds bundle quantity',
            //         ]
            //     ] , 422);
            // }

            foreach ($bundleMetaRules as $rule) {
                $items = collect( $request->items );
                $selectedQuantity = $items->where('product', $rule['product']['id'])->count();

                if ($selectedQuantity > $rule['quantity']) {
                    return response()->json([
                        'message' => "Quantity for {$rule['product']['title']} exceeds the limit.",
                        'errors' => ['products' => "Selected: $selectedQuantity, Allowed: {$rule['quantity']}"]
                    ], 422);
                }
            }

            foreach ($request->items as $item) {
                if ( !collect($bundleMetaRules)->firstWhere('product.id', $item['product']) ) {
                    $isValid = false;
                    $error = 1;
                    break;
                }

                $validator = Validator::make([], []); // Initialize an empty validator

                foreach ($request->items as $index => $item) {
                    $product = $bundleRules['product']; // Access the product object from bundle rules
                
                    // Check froyo quantity
                    if (isset($item['froyo']) && count($item['froyo']) > $product->default_froyo_quantity) {
                        $validator->errors()->add(
                            "items.$index.froyo",
                            "You can select up to {$product->default_froyo_quantity} froyo(s)."
                        );
                    }
                
                    // Check syrup quantity
                    if (isset($item['syrup']) && count($item['syrup']) > $product->default_syrup_quantity) {
                        $validator->errors()->add(
                            "items.$index.syrup",
                            "You can select up to {$product->default_syrup_quantity} syrup(s)."
                        );
                    }
                
                    // Check topping quantity
                    if (isset($item['topping']) && count($item['topping']) > $product->default_topping_quantity) {
                        $validator->errors()->add(
                            "items.$index.topping",
                            "You can select up to {$product->default_topping_quantity} topping(s)."
                        );
                    }
                
                    // Check if product ID matches bundle rule's product ID
                    if ($item['product'] !== $product->id) {
                        $validator->errors()->add(
                            "items.$index.product",
                            "The product ID must match the bundle's product ID: {$product->id}."
                        );
                    }
                }
                
                // If errors are present, return them in the response
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 400);
                }
            }

            if (!$isValid) {

                if( $error == 1 ){
                    return response()->json( [
                        'message' => 'Product not available in bundle',
                        'message_key' => 'product_not_available_in_bundle',
                        'errors' => [
                            'bundle' => 'Product not available in bundle',
                        ]
                    ] , 422);
                } else {
                    return response()->json( [
                        'message' => 'Bundle cant be used together with promotion',
                        'message_key' => 'bundle_not_available',
                        'errors' => [
                            'bundle' => 'Bundle cant be used together with promotion',
                        ]
                    ] , 422);
                }
            }
        }
        
        $validator->validate();

        DB::beginTransaction();
        try {
        
            $orderPrice = 0;
            $voucher = Voucher::where( 'promo_code', $request->promo_code )->where( 'status', 10 )->first();
            $bundle = ProductBundle::where( 'id', $request->bundle )->where( 'status', 10 )->first();
            $userBundle = UserBundle::where( 'id', $request->user_bundle )->where( 'status', 10 )->first();

            $cart = Cart::create( [
                'user_id' => auth()->user()->id,
                'product_id' => null,
                'outlet_id' => null,
                'vending_machine_id' => $request->vending_machine,
                'total_price' => $orderPrice,
                'discount' => 0,
                'status' => 10,
                'session_key' => Helper::generateCartSessionKey(),
                'voucher_id' => $voucher ? $voucher->id :null,
                'product_bundle_id' => $bundle ? $bundle->id :null,
                'user_bundle_id' => $userBundle ? $userBundle->id :null,
            ] );

            if(isset($request->items)){
                foreach ( $request->items as $product ) {

                    $froyos = $product['froyo'];
                    $froyoCount = count($froyos);
                    $syrups = $product['syrup'];
                    $syrupCount = count($syrups);
                    $toppings = $product['topping'];
                    $toppingCount = count($toppings);
                    $product = Product::find($product['product']);
                    $metaPrice = 0;
    
                    $orderMeta = CartMeta::create( [
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'product_bundle_id' => null,
                        'froyos' =>  json_encode($froyos),
                        'syrups' =>  json_encode($syrups),
                        'toppings' =>  json_encode($toppings),
                        'total_price' =>  $metaPrice,
                    ] );
    
                    $orderPrice += $product->price ?? 0;
                    $metaPrice += $product->price ?? 0;
    
                    // new calculation 
                    $froyoPrices = Froyo::whereIn('id', $froyos)->sum('price');
                    $orderPrice += $froyoPrices;
                    $metaPrice += $froyoPrices;

                    $syrupPrices = Syrup::whereIn('id', $syrups)->sum('price');
                    $orderPrice += $syrupPrices;
                    $metaPrice += $syrupPrices;

                    $toppingPrices = Topping::whereIn('id', $toppings)->sum('price');
                    $orderPrice += $toppingPrices;
                    $metaPrice += $toppingPrices;

                    // calculate free item
                    $froyoPrices = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
                    asort($froyoPrices);

                    $froyoCount = count($froyos);
                    $freeCount = $product->free_froyo_quantity;
                    $chargableAmount = 0;

                    if ($froyoCount > $freeCount) {
                        $chargeableCount = $froyoCount - $freeCount;
                        $chargeableFroyoPrices = array_slice($froyoPrices, 0, $chargeableCount, true);
                        $totalDeduction = array_sum($chargeableFroyoPrices);
                        $orderPrice -= $totalDeduction;
                        $metaPrice -= $totalDeduction;

                        $froyoPrices2 = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
                        rsort($froyoPrices2);

                        $chargeableCount = $froyoCount - $freeCount;
                        $chargeableFroyoPrices = array_slice($froyoPrices2, 0, $chargeableCount, true);
                        $totalDeduction2 = array_sum($chargeableFroyoPrices);
                        $chargableAmount += $totalDeduction2;

                    }else{
                        $totalDeduction = array_sum($froyoPrices);
                        $orderPrice -= $totalDeduction;
                        $metaPrice -= $totalDeduction;
                    }

                    // free item module
                    $syrupPrices = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
                    asort($syrupPrices);
                    
                    $syrupCount = count($syrups);
                    $freeCount = $product->free_syrup_quantity;

                    if ($syrupCount > $freeCount) {
                        $chargeableCount = $syrupCount - $freeCount;
                        $chargeablesyrupPrices = array_slice($syrupPrices, 0, $chargeableCount, true);

                        $totalDeduction = array_sum($chargeablesyrupPrices);
                        $orderPrice -= $totalDeduction;
                        $metaPrice -= $totalDeduction;

                        $syrupPrices2 = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
                        rsort($syrupPrices2);

                        $chargeableCount = $syrupCount - $freeCount;
                        $chargeableFroyoPrices = array_slice($syrupPrices2, 0, $chargeableCount, true);
                        $totalDeduction2 = array_sum($chargeableFroyoPrices);
                        $chargableAmount += $totalDeduction2;

                    }else{
                        $totalDeduction = array_sum($syrupPrices);
                        $orderPrice -= $totalDeduction;
                        $metaPrice -= $totalDeduction;
                    }
                
                    $toppingPrices = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
                    asort($toppingPrices);
                    
                    $toppingCount = count($toppings);
                    $freeCount = $product->free_topping_quantity;

                    if ($toppingCount > $freeCount) {
                        $chargeableCount = $toppingCount - $freeCount;
                        $chargeabletoppingPrices = array_slice($toppingPrices, 0, $chargeableCount, true);
                        $totalDeduction = array_sum($chargeabletoppingPrices);

                        $orderPrice -= $totalDeduction;
                        $metaPrice -= $totalDeduction;

                        $toppingPrices2 = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
                        rsort($toppingPrices2);

                        $chargeableCount = $toppingCount - $freeCount;
                        $chargeabletoppingPrices = array_slice($toppingPrices2, 0, $chargeableCount, true);
                        $totalDeduction2 = array_sum($chargeabletoppingPrices);
                        $chargableAmount += $totalDeduction2;

                    }else{
                        $totalDeduction = array_sum($toppingPrices);
                        $orderPrice -= $totalDeduction;
                        $metaPrice -= $totalDeduction;
                    }

                    $orderMeta->total_price = $metaPrice;
                    $orderMeta->additional_charges = $chargableAmount;
                    $orderMeta->save();
                }
            }
            
            // load relationship for later use
            $cart->load('cartMetas');
            $cart->subtotal = $orderPrice;


            if( $request->promo_code ){
                $voucher = Voucher::where( 'id', $request->promo_code )
                ->orWhere('promo_code', $request->promo_code)->first();

                if ( $voucher->discount_type == 3 ) {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $requestedProductIds = collect($request->input('items'))->pluck('product');
                    $x = $requestedProductIds->intersect($adjustment->buy_products)->count();
        
                    if ( $x >= $adjustment->buy_quantity ) {
                        $getProductMeta = $cart->cartMetas
                        ->where('product_id', $adjustment->get_product)
                        ->sortBy('total_price')
                        ->first();

                        if ($getProductMeta) {
                            $orderPrice -= Helper::numberFormatV2($getProductMeta->total_price,2,false,true);
                            $orderPrice += $getProductMeta->additional_charges;
                            $cart->discount = Helper::numberFormatV2($getProductMeta->total_price,2,false,true);
                            $getProductMeta->total_price = 0 + $getProductMeta->additional_charges;
                            $getProductMeta->save();
                        }
                    }

                } else if ( $voucher->discount_type == 2 ) {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );

                    $x = $orderPrice;

                    if ( $x >= $adjustment->buy_quantity ) {
                        $orderPrice -= $adjustment->discount_quantity;
                        $cart->discount = Helper::numberFormatV2($adjustment->discount_quantity,2 ,false, true);
                    }
        
                } else {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $orderPrice;
                    if ( $x >= $adjustment->buy_quantity ) {
                        $cart->discount = Helper::numberFormatV2(( $orderPrice * $adjustment->discount_quantity / 100 ),2 ,false, true);
                        $orderPrice = $orderPrice - ( $orderPrice * $adjustment->discount_quantity / 100 );
                    }
                }

                $cart->voucher_id = $voucher->id;
            }

            // handle bundle
            if( $bundle ){

                $cartMetas = $cart->cartMetas;

                $totalCartDeduction = self::calculateBundleCharges( $cartMetas );

                $orderPrice = $bundle->price + $totalCartDeduction;
                $cart->subtotal = $orderPrice;

            }

            if( $request->user_bundle ){

                $cartMetas = $cart->cartMetas;

                $totalCartDeduction = self::calculateBundleCharges( $cartMetas );

                $orderPrice = 0;
                $orderPrice += $totalCartDeduction;
                $cart->subtotal = $orderPrice;

                $userBundle->cups_left -= count( $cart->cartMetas );

                $bundleMetas = $userBundle->productBundle->productBundleMetas;

                $bundleCupLeft = [];
                $cartMetas = $cart->cartMetas;
                foreach($bundleMetas as $key => $bundleMeta){
                    $bundleCupLeft[$bundleMeta->product_id] = $bundleMeta->quantity - $cartMetas->where('product_id',$bundleMeta->product_id)->count();
                }

                $userBundle->cups_left_metas =json_encode( $bundleCupLeft );
                
                $userBundle->save();
            }

            $cart->total_price = Helper::numberFormatV2($orderPrice,2);
            $taxSettings = Option::getTaxesSettings();
            $cart->tax = $taxSettings ? (Helper::numberFormatV2(($taxSettings->option_value/100),2) * Helper::numberFormatV2($cart->total_price,2)) : 0;
            
            $cart->total_price += Helper::numberFormatV2($cart->tax, 2,false,true);
            $cart->save();
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        $cartMetas = $cart->cartMetas->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'product' => $meta->product->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->product->image_path),
                'froyo' => $meta->froyos_metas,
                'syrup' => $meta->syrups_metas,
                'topping' => $meta->toppings_metas,
            ];
        });

        if($cart->voucher){
            $cart->voucher->makeHidden( [ 'created_at', 'updated_at', 'type', 'status', 'min_spend', 'min_order', 'buy_x_get_y_adjustment', 'discount_amount' ] )
            ->append(['decoded_adjustment', 'image_path','voucher_type','voucher_type_label']);
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'add_to_cart_success',
            'sesion_key' => $cart->session_key,
            'cart_id' => $cart->id,
            'vending_machine' => $cart->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $cart->vendingMachine->operational_hour),
            'total' => Helper::numberFormatV2($cart->total_price, 2,false, true),
            'cart_metas' => $cartMetas,
            'subtotal' => Helper::numberFormatV2($cart->subtotal, 2,false, true),
            'discount' =>  Helper::numberFormatV2($cart->discount, 2,false, true),
            'tax' =>  Helper::numberFormatV2($cart->tax, 2,false, true),
            'voucher' => $cart->voucher ? $cart->voucher->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
            'bundle' => $cart->productBundle ? $cart->productBundle->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
            'user_bundle' => $cart->userBundle ? $cart->userBundle->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
        ] );
    }

    public static function updateCart( $request ) {

        if( !isset( $request->items ) ) {
            $request->merge(['items'=> []]);
        }

        $validator = Validator::make( $request->all(), [
            'id' => ['nullable', 'exists:carts,id', 'required_without:session_key'],
            'user_bundle' => [ 'nullable', 'exists:user_bundles,id'  ],
            'bundle' => [ 'nullable', 'exists:product_bundles,id'  ],
            'session_key' => ['nullable', 'exists:carts,session_key', 'required_without:id'],
            'vending_machine' => [ 'nullable', 'exists:vending_machines,id'  ],
            'items' => ['nullable', 'array'],
            'items.*.product' => ['required', 'exists:products,id'],
            'items.*.froyo' => ['nullable', 'array'],
            'items.*.froyo.*' => ['exists:froyos,id'], // Validate each froyo ID
            'items.*.syrup' => ['nullable', 'array'],
            'items.*.syrup.*' => ['exists:syrups,id'], // Validate each syrup ID
            'items.*.topping' => ['nullable', 'array'],
            'items.*.topping.*' => ['exists:toppings,id'], // Validate each topping ID
            'cart_item' => ['nullable', 'exists:cart_metas,id'],
            'promo_code' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $existsInPromoCode = \DB::table('vouchers')->where('promo_code', $value)->exists();
                    $existsInId = \DB::table('vouchers')->where('id', $value)->exists();

                    if (!$existsInPromoCode && !$existsInId) {
                        $fail(__('The :attribute must exist in either the promo_code or id column.'));
                    }
                },
            ],
        ] );

        $validateCBR = self::validateCartBundleRules($request);

        if ($validateCBR->getStatusCode() === 422) {
            return $validateCBR;
        }

        if (isset($request->items)) {
            $validator->after(function ($validator) use ($request) {

                foreach ($request->items as $index => $item) {
                    // Fetch the product and its default quantities

                    $product = Product::find($item['product']);
                    if (!$product) {
                        $validator->errors()->add("items.$index.product", 'Invalid product ID.');
                        continue;
                    }
        
                    // Check froyo quantity
                    if (isset($item['froyo']) && count($item['froyo']) > $product->default_froyo_quantity) {
                        $validator->errors()->add("items.$index.froyo", "You can select up to {$product->default_froyo_quantity} froyos.");
                    }
        
                    // Check syrup quantity
                    if (isset($item['syrup']) && count($item['syrup']) > $product->default_syrup_quantity) {
                        $validator->errors()->add("items.$index.syrup", "You can select up to {$product->default_syrup_quantity} syrups.");
                    }
        
                    // Check topping quantity
                    if (isset($item['topping']) && count($item['topping']) > $product->default_topping_quantity) {
                        $validator->errors()->add("items.$index.topping", "You can select up to {$product->default_topping_quantity} toppings.");
                    }
                }
            });
        }

        if ($validator->fails()) {
            $rawErrors = $validator->errors()->toArray();

            $formattedErrors = [
                'vending_machine' => $rawErrors['vending_machine'][0] ?? null, // Include vending machine error
                'promo_code' => $rawErrors['promo_code'][0] ?? null, // Include promo_code error
                'bundle' => $rawErrors['bundle'][0] ?? null, // Include bundle error
                'cart_item' => $rawErrors['cart_item'][0] ?? null, // Include bundle error
                'user_bundle' => $rawErrors['user_bundle'][0] ?? null, // Include bundle error
                'items' => []
            ];

        
            foreach ($rawErrors as $key => $messages) {
                // Handle items validation errors
                if (preg_match('/items\.(\d+)\.(\w+)/', $key, $matches)) {
                    $index = $matches[1]; // Extract index (e.g., 0)
                    $field = $matches[2]; // Extract field (e.g., froyo)
        
                    // Group errors by index
                    if (!isset($formattedErrors['items'][$index])) {
                        $formattedErrors['items'][$index] = [];
                    }
        
                    $formattedErrors['items'][$index][$field] = $messages[0]; // Add the first error message
                }
            }

            // Remove null vending machine error if not present
            if (!$formattedErrors['vending_machine']) {
                unset($formattedErrors['vending_machine']);
            }

            if (!$formattedErrors['promo_code']) {
                unset($formattedErrors['promo_code']);
            }

            if (!$formattedErrors['bundle']) {
                unset($formattedErrors['bundle']);
            }

            if (!$formattedErrors['cart_item']) {
                unset($formattedErrors['cart_item']);
            }

            if (!$formattedErrors['user_bundle']) {
                unset($formattedErrors['user_bundle']);
            }

            return response()->json(["message"=> "The given data was invalid.",'errors' => $formattedErrors], 422);
        }
        // check voucher type
        if ( $request->promo_code ) {

            $voucher = Voucher::where( 'id', $request->promo_code )
            ->orWhere('promo_code', $request->promo_code)->first();

            if( !$voucher ){
                return response()->json( [
                    'message' => 'Voucher not found',
                    'message_key' => 'voucher_not_found',
                    'errors' => [
                        'voucher' => 'Voucher not found'
                    ]
                ] , 422);
            }

            // if( $voucher->type == 1 ){
            //     return response()->json( [
            //         'message' => 'Voucher Not applicable to cart',
            //         'message_key' => 'voucher_not_applicable_to_cart',
            //         'errors' => [
            //             'voucher' => 'Voucher Not applicable to cart'
            //         ]
            //     ], 422 );
            // }

            $test = self::validateCartVoucher($request);

            if ($test->getStatusCode() === 422) {
                return $test;
            }
        }

        // validate delete cart item
        if ($request->has('cart_item')) {
            $cartMeta = CartMeta::find($request->cart_item);
            if (!$cartMeta) {
                return response()->json( [
                    'message' => 'Cart item not found.',
                    'message_key' => 'cart_not_found',
                    'errors' => [
                        'cart' => 'Cart item not found.',
                    ]
                ], 422);
            }

            if( !$request->items && $request->promo_codes ){
                $cartMetaToDelete = CartMeta::find($request->cart_item);

                if ($cartMetaToDelete) {
                    $cart = $cartMetaToDelete->cart;
            
                    $remainingCartMetas = $cart->cartMetas->where('id', '!=', $cartMetaToDelete->id);
            
                    if( $request->promo_code || $cart->voucher_id ){
                        $isEligible = self::checkCartEligibility($request, $remainingCartMetas);

                        if ($isEligible->getStatusCode() === 422) {
                            return $isEligible;
                        }

                        if (!$isEligible) {
                            
                            return response()->json( [
                                'message' => 'Deleting this item will make the cart ineligible.',
                                'message_key' => 'cart_ineligible',
                                'errors' => [
                                    'cart' => 'Deleting this item will make the cart ineligible.',
                                ]
                            ], 422 );
                        }
                    }
                }
            } else {
                $cartMetaToDelete = CartMeta::find($request->cart_item);

                if ($cartMetaToDelete) {
                    $cart = $cartMetaToDelete->cart;

                    if( $request->promo_code || $cart->voucher_id ){

                        $product = Product::find($request->items[0]['product']);
                        $froyos = $request->items[0]['froyo'] ?? [];
                        $syrups = $request->items[0]['syrup'] ?? [];
                        $toppings = $request->items[0]['topping'] ?? [];

                        $metaPrice = 0;
                        $metaPrice += $product->price ?? 0;
                        $froyoPrices = Froyo::whereIn('id', $froyos)->sum('price');
                        $metaPrice += $froyoPrices;

                        $syrupPrices = Syrup::whereIn('id', $syrups)->sum('price');
                        $metaPrice += $syrupPrices;

                        $toppingPrices = Topping::whereIn('id', $toppings)->sum('price');
                        $metaPrice += $toppingPrices;

                        $cartMetaToDelete->product_id = $product->id;
                        $cartMetaToDelete->froyos = json_encode($froyos);
                        $cartMetaToDelete->syrups = json_encode($syrups);
                        $cartMetaToDelete->toppings = json_encode($toppings);
                        $cartMetaToDelete->total_price = $metaPrice;
                        $cartMetaToDelete->save();

                        $remainingCartMetas = $cart->cartMetas;

                        $isEligible = self::checkCartEligibility($request, $remainingCartMetas);

                        if ($isEligible->getStatusCode() === 422) {
                            return $isEligible;
                        }

                        if (!$isEligible) {
                            
                            return response()->json( [
                                'message' => 'Deleting this item will make the cart ineligible.',
                                'message_key' => 'cart_ineligible',
                                'errors' => [
                                    'cart' => 'Deleting this item will make the cart ineligible.',
                                ]
                            ], 422 );
                        }
                    }

                }
            }
        }

        // validate bundle product
        if( $request->bundle ){

            $bundle = ProductBundle::where( 'id', $request->bundle )->where( 'status', 10 )->first();
            $bundleRules = $bundle->bundle_rules;
            $bundleMetaRules = $bundle->bundle_meta_rules; //mix product bundle

            $isValid = true;
            $error = 0;

            $cartMetaCount = Cart::find($request->id)->cartMetas->count();

            if( $request->cart_item ){
                $cartMetaCount -= ( isset( $request->items ) ? count($request->items) : 0 );
            }

            if( !$request->cart_item && $request->items ){
                $cartMetaCount = 0;
            }

            // if ( ( isset( $request->items ) ? count($request->items) : 0 ) + $cartMetaCount > $bundleRules['quantity'] ) {
            //     return response()->json( [
            //         'message' => 'Product exceeeds bundle quantity',
            //         'message_key' => 'product_exceeds_bundle_quantity',
            //         'errors' => [
            //             'bundle' => 'Product exceeeds bundle quantity',
            //         ]
            //     ] , 422);
            // }

            foreach ($bundleMetaRules as $rule) {
                $items = collect( $request->items );
                $selectedQuantity = $items->where('product', $rule['product']['id'])->count();

                if (($selectedQuantity + $cartMetaCount) > $rule['quantity']) {
                    return response()->json([
                        'message' => "Quantity for {$rule['product']['title']} exceeds the limit.",
                        'errors' => ['products' => "Selected: $selectedQuantity, Allowed: {$rule['quantity']}"]
                    ], 422);
                }
            }

            foreach ($request->items as $item) {
                if ( !collect($bundleMetaRules)->firstWhere('product.id', $item['product']) ) {
                    $isValid = false;
                    $error = 1;
                    break;
                }

                $validator = Validator::make([], []); // Initialize an empty validator

                foreach ($request->items as $index => $item) {
                    $product = $bundleRules['product']; // Access the product object from bundle rules
                
                    // Check froyo quantity
                    if (isset($item['froyo']) && count($item['froyo']) > $product->default_froyo_quantity) {
                        $validator->errors()->add(
                            "items.$index.froyo",
                            "You can select up to {$product->default_froyo_quantity} froyo(s)."
                        );
                    }
                
                    // Check syrup quantity
                    if (isset($item['syrup']) && count($item['syrup']) > $product->default_syrup_quantity) {
                        $validator->errors()->add(
                            "items.$index.syrup",
                            "You can select up to {$product->default_syrup_quantity} syrup(s)."
                        );
                    }
                
                    // Check topping quantity
                    if (isset($item['topping']) && count($item['topping']) > $product->default_topping_quantity) {
                        $validator->errors()->add(
                            "items.$index.topping",
                            "You can select up to {$product->default_topping_quantity} topping(s)."
                        );
                    }
                
                    // Check if product ID matches bundle rule's product ID
                    if ($item['product'] !== $product->id) {
                        $validator->errors()->add(
                            "items.$index.product",
                            "The product ID must match the bundle's product ID: {$product->id}."
                        );
                    }
                }
                
                // If errors are present, return them in the response
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 400);
                }
            }

            if (!$isValid) {

                if( $error == 1 ){
                    return response()->json( [
                        'message' => 'Product not available in bundle',
                        'message_key' => 'product_not_available_in_bundle',
                        'errors' => [
                            'bundle' => 'Product not available in bundle',
                        ]
                    ] , 422);
                } else {
                    return response()->json( [
                        'message' => 'Bundle cant be used together with promotion',
                        'message_key' => 'bundle_not_available',
                        'errors' => [
                            'bundle' => 'Bundle cant be used together with promotion',
                        ]
                    ] , 422);
                }
            }
        }

        // validate bundle product
        if( $request->user_bundle ){

            $userBundle = UserBundle::where( 'id', $request->user_bundle )->where( 'user_id', auth()->user()->id )->where( 'status', 10 )->first();
            $cartMetaCount = Cart::find($request->id)->cartMetas->count();
            $userBundle->cups_left += $cartMetaCount;

            if( $request->cart_item ){
                $cartMetaCount -= ( isset( $request->items ) ? count($request->items) : 0 );
            }

            if( !$request->cart_item && $request->items ){
                $userBundle->save();
                $cartMetaCount = 0;
            }

            if ( !$userBundle ) {
                return response()->json( [
                    'message' => 'User Bundle Not Found',
                    'message_key' => 'user_bundle_not_found',
                    'errors' => [
                        'user_bundle' => 'User Bundle Not Found',
                    ]
                ] , 422);
            }

            // check own bundle's cups left
            if ( $cartMetaCount > $userBundle->cups_left ) {
                return response()->json( [
                    'message' => 'You have redeemed all cups from bundle',
                    'message_key' => 'no_cups_left',
                    'errors' => [
                        'user_bundle' => 'You have redeemed all cups from bundle',
                    ]
                ] , 422);
            }

            $bundleRules = $userBundle->productBundle->bundle_rules;
            $bundleMetaRules = $userBundle->productBundle->bundle_meta_rules; //mix product bundle

            $isValid = true;
            $error = 0;
            
            // if ( ( isset( $request->items ) ? count($request->items) : 0 ) + $cartMetaCount > $bundleRules['quantity'] ) {
            //     return response()->json( [
            //         'message' => 'Product exceeeds bundle quantity',
            //         'message_key' => 'product_exceeds_bundle_quantity',
            //         'errors' => [
            //             'user_bundle' => 'Product exceeeds bundle quantity',
            //         ]
            //     ] , 422);
            // }
            
            if( !$request->cart_item ){
                foreach ($bundleMetaRules as $rule) {
                    $items = collect( $request->items );
    
                    $cartMetaCount = Cart::find($request->id)->cartMetas->where( 'product_id', $rule['product']['id'] );
    
                    $selectedQuantity = $items->where('product', $rule['product']['id'])->count() + $cartMetaCount;
    
                    if ( $selectedQuantity > $rule['quantity']) {
                        return response()->json([
                            'message' => "Quantity for {$rule['product']['title']} exceeds the limit.",
                            'errors' => ['products' => "Selected: $selectedQuantity, Allowed: {$rule['quantity']}"]
                        ], 422);
                    }
                }
            }

            foreach ($request->items as $item) {
                if ( !collect($bundleMetaRules)->firstWhere('product.id', $item['product']) ) {
                    $isValid = false;
                    $error = 1;
                    break;
                }

                $validator = Validator::make([], []); // Initialize an empty validator

                foreach ($request->items as $index => $item) {
                    $product = $bundleRules['product']; // Access the product object from bundle rules
                
                    // Check froyo quantity
                    if (isset($item['froyo']) && count($item['froyo']) > $product->default_froyo_quantity) {
                        $validator->errors()->add(
                            "items.$index.froyo",
                            "You can select up to {$product->default_froyo_quantity} froyo(s)."
                        );
                    }
                
                    // Check syrup quantity
                    if (isset($item['syrup']) && count($item['syrup']) > $product->default_syrup_quantity) {
                        $validator->errors()->add(
                            "items.$index.syrup",
                            "You can select up to {$product->default_syrup_quantity} syrup(s)."
                        );
                    }
                
                    // Check topping quantity
                    if (isset($item['topping']) && count($item['topping']) > $product->default_topping_quantity) {
                        $validator->errors()->add(
                            "items.$index.topping",
                            "You can select up to {$product->default_topping_quantity} topping(s)."
                        );
                    }
                
                    // Check if product ID matches bundle rule's product ID
                    if ($item['product'] !== $product->id) {
                        $validator->errors()->add(
                            "items.$index.product",
                            "The product ID must match the bundle's product ID: {$product->id}."
                        );
                    }
                }
                
                // If errors are present, return them in the response
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 400);
                }
            }

            if (!$isValid) {

                if( $error == 1 ){
                    return response()->json( [
                        'message' => 'Product not available in bundle',
                        'message_key' => 'product_not_available_in_bundle',
                        'errors' => [
                            'bundle' => 'Product not available in bundle',
                        ]
                    ] , 422);
                } else {
                    return response()->json( [
                        'message' => 'Bundle cant be used together with promotion',
                        'message_key' => 'bundle_not_available',
                        'errors' => [
                            'bundle' => 'Bundle cant be used together with promotion',
                        ]
                    ] , 422);
                }
            }
        }

        $validator->validate();

        $user = auth()->user();
        $query = Cart::where('user_id', $user->id)
        ->with(['cartMetas','vendingMachine'])
        ->where('status',10);
    
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        if ($request->has('session_key')) {
            $query->where('session_key', $request->session_key);
        }
    
        // Retrieve the cart(s) based on the applied filters
        $updateCart = $query->first();

        if ( !$updateCart ) {
            return response()->json( [
                'message' => 'Cart not found',
                'message_key' => 'cart_not_found',
                'errors' => [
                    'cart' => 'Cart not found'
                ]
            ] , 422);
        }

        DB::beginTransaction();

        try {
        
            $orderPrice = 0;

            $updateCart->load( ['cartMetas'] );
            $updateCart->vending_machine_id = $request->vending_machine;

            $voucher = Voucher::where( 'promo_code', $request->promo_code )->where( 'status', 10 )->first();
            $bundle = ProductBundle::where( 'id', $request->bundle )->where( 'status', 10 )->first();
            $userBundle = UserBundle::where( 'id', $request->user_bundle )->where( 'status', 10 )->first();

            $updateCart->voucher_id = $voucher ? $voucher->id :null;
            $updateCart->product_bundle_id = $bundle ? $bundle->id :null;
            $updateCart->user_bundle_id = $userBundle ? $userBundle->id :null;
            
            $updateCart->load( ['cartMetas'] );

            if ($request->has('cart_item')) {
                $cartMeta = CartMeta::find($request->cart_item);
                if (!$cartMeta) {
                    return response()->json( [
                        'message' => 'Cart item not found.',
                        'message_key' => 'cart_not_found',
                        'errors' => [
                            'cart' => 'Cart item not found.',
                        ]
                    ], 422);
                }
                
                if( !$request->items ){
                    $orderPrice -= $cartMeta->total_price;
                    $orderPrice += $updateCart->cartMetas->sum('total_price') ?? 0;
                    $cartMeta->delete();
                }else{
                    // Update specific cart item
                    $product = Product::find($request->items[0]['product']);
                    $froyos = $request->items[0]['froyo'] ?? [];
                    $syrups = $request->items[0]['syrup'] ?? [];
                    $toppings = $request->items[0]['topping'] ?? [];

                    // Calculate new total price for this cart item
                    $metaPrice = 0;
                    $metaPrice += $product->price ?? 0;
                    $orderPrice -= $cartMeta->total_price;
                    $orderPrice += $updateCart->cartMetas->sum('total_price') ?? 0;

                    // new calculation 
                    $froyoPrices = Froyo::whereIn('id', $froyos)->sum('price');
                    // $orderPrice += $froyoPrices;
                    $metaPrice += $froyoPrices;

                    $syrupPrices = Syrup::whereIn('id', $syrups)->sum('price');
                    // $orderPrice += $syrupPrices;
                    $metaPrice += $syrupPrices;

                    $toppingPrices = Topping::whereIn('id', $toppings)->sum('price');
                    // $orderPrice += $toppingPrices;
                    $metaPrice += $toppingPrices;

                    $cartMeta->product_id = $product->id;
                    $cartMeta->froyos = json_encode($froyos);
                    $cartMeta->syrups = json_encode($syrups);
                    $cartMeta->toppings = json_encode($toppings);
                    $cartMeta->total_price = $metaPrice;
                    $chargableAmount = 0;

                    // calculate free item
                    $froyoPrices = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
                    asort($froyoPrices);

                    $froyoCount = count($froyos);
                    $freeCount = $product->free_froyo_quantity;

                    if ($froyoCount > $freeCount) {
                        $chargeableCount = $froyoCount - $freeCount;
                        $chargeableFroyoPrices = array_slice($froyoPrices, 0, $chargeableCount, true);
                        $totalDeduction = array_sum($chargeableFroyoPrices);
                        $cartMeta->total_price -= $totalDeduction;
                        $orderPrice-= $totalDeduction;

                        $froyoPrices2 = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
                        rsort($froyoPrices2);

                        $chargeableCount = $froyoCount - $freeCount;
                        $chargeableFroyoPrices = array_slice($froyoPrices2, 0, $chargeableCount, true);
                        $totalDeduction2 = array_sum($chargeableFroyoPrices);
                        $chargableAmount += $totalDeduction2;

                    }else{
                        $totalDeduction = array_sum($froyoPrices);
                        $cartMeta->total_price -= $totalDeduction;
                        $orderPrice-= $totalDeduction;
                    }
                
                    $syrupPrices = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
                    asort($syrupPrices);

                    $syrupCount = count($syrups);
                    $freeCount = $product->free_syrup_quantity;

                    if ($syrupCount > $freeCount) {
                        $chargeableCount = $syrupCount - $freeCount;
                        $chargeablesyrupPrices = array_slice($syrupPrices, 0, $chargeableCount, true);

                        $totalDeduction = array_sum($chargeablesyrupPrices);
                        $cartMeta->total_price -= $totalDeduction;
                        $orderPrice-= $totalDeduction;

                        $syrupPrices2 = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
                        rsort($syrupPrices2);

                        $chargeableCount = $syrupCount - $freeCount;
                        $chargeableFroyoPrices = array_slice($syrupPrices2, 0, $chargeableCount, true);
                        $totalDeduction2 = array_sum($chargeableFroyoPrices);
                        $chargableAmount += $totalDeduction2;

                    }else{
                        $totalDeduction = array_sum($syrupPrices);
                        $cartMeta->total_price -= $totalDeduction;
                        $orderPrice-= $totalDeduction;
                    }
                
                    $toppingPrices = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
                    asort($toppingPrices);
                    
                    $toppingCount = count($toppings);
                    $freeCount = $product->free_topping_quantity;

                    if ($toppingCount > $freeCount) {
                        $chargeableCount = $toppingCount - $freeCount;
                        $chargeabletoppingPrices = array_slice($toppingPrices, 0, $chargeableCount, true);
                        $totalDeduction = array_sum($chargeabletoppingPrices);

                        $cartMeta->total_price -= $totalDeduction;
                        $orderPrice-= $totalDeduction;

                        $toppingPrices2 = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
                        rsort($toppingPrices2);

                        $chargeableCount = $toppingCount - $freeCount;
                        $chargeabletoppingPrices = array_slice($toppingPrices2, 0, $chargeableCount, true);
                        $totalDeduction2 = array_sum($chargeabletoppingPrices);
                        $chargableAmount += $totalDeduction2;

                    }else{
                        $totalDeduction = array_sum($toppingPrices);
                        $cartMeta->total_price -= $totalDeduction;
                        $orderPrice-= $totalDeduction;
                    }

                    $cartMeta->additional_charges = $chargableAmount;
                    $cartMeta->save();
                    $updateCart->load( ['cartMetas'] );

                    // $orderPrice += $metaPrice;
                    // $updateCart->subtotal = $orderPrice;
                    // $updateCart->save();

                    // update all other cartsMeta
                    $remainingCartMetas = $updateCart->cartMetas->where('id', '!=', $cartMeta->id);
                    foreach( $remainingCartMetas as $rcm ){

                        $rcm->total_price = 0;
                        $rcm->additional_charges = 0;
                        $rcm->total_price += $rcm->product->price;

                        $froyoPrices = Froyo::whereIn('id', json_decode($rcm->froyos, true))->sum('price');
                        $rcm->total_price += $froyoPrices;
    
                        $syrupPrices = Syrup::whereIn('id', json_decode($rcm->syrups, true))->sum('price');
                        $rcm->total_price += $syrupPrices;
    
                        $toppingPrices = Topping::whereIn('id', json_decode($rcm->toppings, true))->sum('price');
                        $rcm->total_price += $toppingPrices;

                        // calculate free item
                        $froyoPrices = Froyo::whereIn('id', json_decode($rcm->froyos, true))->pluck('price', 'id')->toArray();
                        asort($froyoPrices);

                        $froyoCount = count(json_decode($rcm->froyos, true));
                        $freeCount = $product->free_froyo_quantity;
                        $chargableAmount = 0;

                        if ($froyoCount > $freeCount) {
                            $chargeableCount = $froyoCount - $freeCount;
                            $chargeableFroyoPrices = array_slice($froyoPrices, 0, $chargeableCount, true);
                            $totalDeduction = array_sum($chargeableFroyoPrices);
                            $rcm->total_price -= $totalDeduction;

                            $froyoPrices2 = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
                            rsort($froyoPrices2);

                            $chargeableCount = $froyoCount - $freeCount;
                            $chargeableFroyoPrices = array_slice($froyoPrices2, 0, $chargeableCount, true);
                            $totalDeduction2 = array_sum($chargeableFroyoPrices);
                            $rcm->additional_charges += $totalDeduction2;
                            $rcm->save();

                        }else{
                            $totalDeduction = array_sum($froyoPrices);
                            $rcm->total_price -= $totalDeduction;
                            $rcm->save();
                        }
                        // free item module
                        $syrupPrices = Syrup::whereIn('id', json_decode($rcm->syrups, true))->pluck('price', 'id')->toArray();
                        asort($syrupPrices);
                        
                        $syrupCount = count(json_decode($rcm->syrups, true));
                        $freeCount = $product->free_syrup_quantity;

                        if ($syrupCount > $freeCount) {
                            $chargeableCount = $syrupCount - $freeCount;
                            $chargeablesyrupPrices = array_slice($syrupPrices, 0, $chargeableCount, true);

                            $totalDeduction = array_sum($chargeablesyrupPrices);
                            $rcm->total_price -= $totalDeduction;

                            $syrupPrices2 = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
                            rsort($syrupPrices2);
                            
                            $chargeableCount = $syrupCount - $freeCount;
                            $chargeableFroyoPrices = array_slice($syrupPrices2, 0, $chargeableCount, true);
                            $totalDeduction2 = array_sum($chargeableFroyoPrices);
                            $rcm->additional_charges += $totalDeduction2;
                            $rcm->save();

                        }else{
                            $totalDeduction = array_sum($syrupPrices);
                            $rcm->total_price -= $totalDeduction;
                            $rcm->save();
                        }

                        $toppingPrices = Topping::whereIn('id', json_decode($rcm->toppings, true))->pluck('price', 'id')->toArray();
                        asort($toppingPrices);
                        
                        $toppingCount = count(json_decode($rcm->toppings, true));
                        $freeCount = $product->free_topping_quantity;

                        if ($toppingCount > $freeCount) {
                            $chargeableCount = $toppingCount - $freeCount;
                            $chargeabletoppingPrices = array_slice($toppingPrices, 0, $chargeableCount, true);
                            $totalDeduction = array_sum($chargeabletoppingPrices);

                            $rcm->total_price -= $totalDeduction;

                            $toppingPrices2 = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
                            rsort($toppingPrices2);

                            $chargeableCount = $toppingCount - $freeCount;
                            $chargeabletoppingPrices = array_slice($toppingPrices2, 0, $chargeableCount, true);
                            $totalDeduction2 = array_sum($chargeabletoppingPrices);
                            $rcm->additional_charges += $totalDeduction2;
                            $rcm->save();

                        }else{
                            $totalDeduction = array_sum($toppingPrices);
                            $rcm->total_price -= $totalDeduction;
                            $rcm->save();
                        }

                        $rcm->save();
                    }

                    $orderPrice = $updateCart->cartMetas->sum('total_price');
                    $updateCart->save();
                    DB::commit();
                    $updateCart->load( ['cartMetas'] );

                    if( $request->promo_code ){
                        $voucher = Voucher::where( 'id', $request->promo_code )
                        ->orWhere('promo_code', $request->promo_code)->first();

                        if ( $voucher->discount_type == 3 ) {
        
                            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
                
                            $requestedProductIds = $updateCart->cartMetas->pluck('product_id');

                            $x = $requestedProductIds->intersect($adjustment->buy_products)->count();

                            if ( $x >= $adjustment->buy_quantity ) {
                                $getProductMeta = $updateCart->cartMetas
                                ->where('product_id', $adjustment->get_product)
                                ->sortBy('total_price')
                                ->first();                    

                                if ($getProductMeta) {
                                    $updateCart->discount = Helper::numberFormatV2($getProductMeta->total_price,2,false,true);
                                    $orderPrice -= Helper::numberFormatV2($getProductMeta->total_price,2,false,true);
                                    $orderPrice += $getProductMeta->additional_charges;
                                    $getProductMeta->total_price = 0 + $getProductMeta->additional_charges;
                                    $getProductMeta->save();
                                }
                            }
        
                        } else if ( $voucher->discount_type == 2 ) {
        
                            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
                
                            $x = $updateCart->total_price;
                            if ( $x >= $adjustment->buy_quantity ) {
                                $orderPrice -= $adjustment->discount_quantity;
                                $updateCart->discount = Helper::numberFormatV2($adjustment->discount_quantity,2,false,true);
                            }
                
                        } else {
        
                            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
                
                            $x = $updateCart->total_price;
                            if ( $x >= $adjustment->buy_quantity ) {
                                $updateCart->discount = Helper::numberFormatV2(( $orderPrice * $adjustment->discount_quantity / 100 ),2,false,true);
                                $orderPrice = $orderPrice - ( $orderPrice * $adjustment->discount_quantity / 100 );
                            }
                        }
        
                        $updateCart->voucher_id = $voucher->id;
                    }
                }
    
            } else {
                // Update the entire cart, deleting all previous items
                CartMeta::where('cart_id', $updateCart->id)->delete();
    
                foreach ($request->items as $product) {
                    $froyos = $product['froyo'];
                    $froyoCount = count($froyos);
                    $syrups = $product['syrup'];
                    $syrupCount = count($syrups);
                    $toppings = $product['topping'];
                    $toppingCount = count($toppings);
                    $product = Product::find($product['product']);
                    $metaPrice = 0;
    
                    $orderMeta = CartMeta::create( [
                        'cart_id' => $updateCart->id,
                        'product_id' => $product->id,
                        'product_bundle_id' => null,
                        'froyos' =>  json_encode($froyos),
                        'syrups' =>  json_encode($syrups),
                        'toppings' =>  json_encode($toppings),
                        'total_price' =>  $metaPrice,
                    ] );
    
                    $orderPrice += $product->price ?? 0;
                    $metaPrice += $product->price ?? 0;
    
                    // new calculation 
                    $froyoPrices = Froyo::whereIn('id', $froyos)->sum('price');
                    $orderPrice += $froyoPrices;
                    $metaPrice += $froyoPrices;

                    $syrupPrices = Syrup::whereIn('id', $syrups)->sum('price');
                    $orderPrice += $syrupPrices;
                    $metaPrice += $syrupPrices;

                    $toppingPrices = Topping::whereIn('id', $toppings)->sum('price');
                    $orderPrice += $toppingPrices;
                    $metaPrice += $toppingPrices;
                    $cartDeduction = 0;
                    $discount = 0;

                    // calculate free item
                    $froyoPrices = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
                    asort($froyoPrices);

                    $froyoCount = count($froyos);
                    $freeCount = $product->free_froyo_quantity;
                    $chargableAmount = 0;

                    if ($froyoCount > $freeCount) {
                        $chargeableCount = $froyoCount - $freeCount;
                        $chargeableFroyoPrices = array_slice($froyoPrices, 0, $chargeableCount, true);
                        $totalDeduction = array_sum($chargeableFroyoPrices);

                        $orderPrice -= $totalDeduction;
                        $metaPrice -= $totalDeduction;
                        $cartDeduction += $totalDeduction;
                        $discount++;

                        $froyoPrices2 = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
                        rsort($froyoPrices2);

                        $chargeableCount = $froyoCount - $freeCount;
                        $chargeableFroyoPrices = array_slice($froyoPrices2, 0, $chargeableCount, true);
                        $totalDeduction2 = array_sum($chargeableFroyoPrices);
                        $chargableAmount += $totalDeduction2;

                    }else{
                        $totalDeduction = array_sum($froyoPrices);
                        $orderPrice -= $totalDeduction;
                        $metaPrice -= $totalDeduction;
                        $cartDeduction += $totalDeduction;
                        $discount++;
                    }

                    $syrupPrices = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
                    asort($syrupPrices);
                    
                    $syrupCount = count($syrups);
                    $freeCount = $product->free_syrup_quantity;

                    if ($syrupCount > $freeCount) {
                        $chargeableCount = $syrupCount - $freeCount;
                        $chargeablesyrupPrices = array_slice($syrupPrices, 0, $chargeableCount, true);

                        $totalDeduction = array_sum($chargeablesyrupPrices);
                        $orderPrice -= $totalDeduction;
                        $metaPrice -= $totalDeduction;
                        $cartDeduction += $totalDeduction;
                        $discount++;

                        $syrupPrices2 = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
                        rsort($syrupPrices2);

                        $chargeableCount = $syrupCount - $freeCount;
                        $chargeableFroyoPrices = array_slice($syrupPrices2, 0, $chargeableCount, true);
                        $totalDeduction2 = array_sum($chargeableFroyoPrices);
                        $chargableAmount += $totalDeduction2;

                    }else{
                        $totalDeduction = array_sum($syrupPrices);
                        $orderPrice -= $totalDeduction;
                        $metaPrice -= $totalDeduction;
                        $cartDeduction += $totalDeduction;
                        $discount++;
                    }
                
                    $toppingPrices = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
                    asort($toppingPrices);
                    
                    $toppingCount = count($toppings);
                    $freeCount = $product->free_topping_quantity;

                    if ($toppingCount > $freeCount) {
                        $chargeableCount = $toppingCount - $freeCount;
                        $chargeabletoppingPrices = array_slice($toppingPrices, 0, $chargeableCount, true);
                        $totalDeduction = array_sum($chargeabletoppingPrices);

                        $orderPrice -= $totalDeduction;
                        $metaPrice -= $totalDeduction;
                        $cartDeduction += $totalDeduction;
                        $discount++;

                        $toppingPrices2 = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
                        rsort($toppingPrices2);

                        $chargeableCount = $toppingCount - $freeCount;
                        $chargeabletoppingPrices = array_slice($toppingPrices2, 0, $chargeableCount, true);
                        $totalDeduction2 = array_sum($chargeabletoppingPrices);
                        $chargableAmount += $totalDeduction2;

                    }else{
                        $totalDeduction = array_sum($toppingPrices);
                        $orderPrice -= $totalDeduction;
                        $metaPrice -= $totalDeduction;
                        $cartDeduction += $totalDeduction;
                        $discount++;
                    }

                    $orderMeta->total_price = $metaPrice;
                    $orderMeta->additional_charges = $chargableAmount;

                    $orderMeta->save();
                }

                $updateCart->subtotal = $orderPrice;

                $updateCart->load( ['cartMetas'] );

                if( $request->promo_code ){
                    $voucher = Voucher::where( 'id', $request->promo_code )
                    ->orWhere('promo_code', $request->promo_code)->first();

                    if ( $voucher->discount_type == 3 ) {
    
                        $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
            
                        $requestedProductIds = collect($request->input('items'))->pluck('product');
                        $x = $requestedProductIds->intersect($adjustment->buy_products)->count();

                        if ( $x >= $adjustment->buy_quantity ) {
                            $getProductMeta = $updateCart->cartMetas
                            ->where('product_id', $adjustment->get_product)
                            ->sortBy('total_price')
                            ->first();                    

                            if ($getProductMeta) {
                                $orderPrice -= Helper::numberFormatV2($getProductMeta->total_price,2,false,true);
                                $updateCart->discount = Helper::numberFormatV2($getProductMeta->total_price,2,false,true);
                                $getProductMeta->total_price = 0 + $getProductMeta->additional_charges;
                                $getProductMeta->save();
                            }
                        }
    
                    } else if ( $voucher->discount_type == 2 ) {
    
                        $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
            
                        $x = $updateCart->total_price;
                        if ( $x >= $adjustment->buy_quantity ) {
                            $orderPrice -= $adjustment->discount_quantity;
                            $updateCart->discount = Helper::numberFormatV2($adjustment->discount_quantity,2,false,true);
                        }
                            
                    } else {
    
                        $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
            
                        $x = $updateCart->total_price;
                        if ( $x >= $adjustment->buy_quantity ) {
                            $updateCart->discount = Helper::numberFormatV2(( $orderPrice * $adjustment->discount_quantity / 100 ),2,false,true);
                            $orderPrice = $orderPrice - ( $orderPrice * $adjustment->discount_quantity / 100  );
                        }
                    }
    
                    $updateCart->voucher_id = $voucher->id;
                }

                DB::commit();
            }

            // handle bundle
            if( $bundle ){

                $cartMetas = $updateCart->cartMetas;

                $totalCartDeduction = self::calculateBundleCharges( $cartMetas );

                $orderPrice = $bundle->price + $totalCartDeduction;
                $updateCart->subtotal = $orderPrice;

            }

            if( $request->user_bundle ){

                $cartMetas = $updateCart->cartMetas;

                $totalCartDeduction = self::calculateBundleCharges( $cartMetas );

                $orderPrice = 0;
                $orderPrice += $totalCartDeduction;
                $updateCart->subtotal = $orderPrice;

                if( !$request->cart_item ){
                    $userBundle->cups_left -= count( $updateCart->cartMetas );
                    $userBundle->save();
                }
                
                $bundleMetas = $userBundle->productBundle->productBundleMetas;

                $bundleCupLeft = [];
                $cartMetas = $updateCart->cartMetas;
                foreach($bundleMetas as $key => $bundleMeta){
                    $bundleCupLeft[$bundleMeta->product_id] = $bundleMeta->quantity - $cartMetas->where('product_id',$bundleMeta->product_id)->count();
                }

                $userBundle->cups_left_metas =json_encode( $bundleCupLeft );
                $userBundle->save();

            }

            $updateCart->total_price = Helper::numberFormatV2($orderPrice,2);
            $taxSettings = Option::getTaxesSettings();
            $updateCart->tax = $taxSettings ? (Helper::numberFormatV2(($taxSettings->option_value/100),2) * Helper::numberFormatV2($updateCart->total_price,2)) : 0;
            $updateCart->total_price += Helper::numberFormatV2($updateCart->tax,2,false,true);

            $updateCart->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        $updateCart->save();
        $updateCart->load('cartMetas');
        DB::commit();

        $cartMetas = $updateCart->cartMetas->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'product' => $meta->product->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->product->image_path),
                'froyo' => $meta->froyos_metas,
                'syrup' => $meta->syrups_metas,
                'topping' => $meta->toppings_metas,
            ];
        });

        return response()->json( [
            'message' => '',
            'message_key' => 'update_cart_success',
            'sesion_key' => $updateCart->session_key,
            'cart_id' => $updateCart->id,
            'vending_machine' => $updateCart->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $updateCart->vendingMachine->operational_hour),
            'total' => Helper::numberFormatV2($updateCart->total_price, 2,false, true),
            'cart_metas' => $cartMetas,
            'subtotal' => Helper::numberFormatV2($updateCart->subtotal, 2,false, true),
            'discount' =>  Helper::numberFormatV2($updateCart->discount, 2,false, true),
            'tax' =>  Helper::numberFormatV2($updateCart->tax, 2,false, true),
            'voucher' => $updateCart->voucher ? $updateCart->voucher->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
            'bundle' => $updateCart->productBundle ? $updateCart->productBundle->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
            'user_bundle' => $updateCart->userBundle ? $updateCart->userBundle->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
        ] );
    }

    public static function deleteCart( $request ) {

        $validator = Validator::make( $request->all(), [
            'id' => ['nullable', 'exists:carts,id', 'required_without:session_key'],
            'session_key' => ['nullable', 'exists:carts,session_key', 'required_without:id'],
        ] );

        $validator->validate();
        $user = auth()->user();
        $query = Cart::where('user_id', $user->id)
        ->with(['cartMetas','vendingMachine']);
    
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        if ($request->has('session_key')) {
            $query->where('session_key', $request->session_key);
        }
    
        // Retrieve the cart(s) based on the applied filters
        $updateCart = $query->first();

        if ( !$updateCart ) {
            return response()->json( [
                'message' => '',
                'message_key' => 'cart_not_found',
                'errors' => [
                    'cart' => 'Cart not found'
                ]
            ], 422 );
        }

        DB::beginTransaction();

        try {
            $updateCart->status = 20;

            if( $updateCart->userBundle ){
                $userBundle = $updateCart->userBundle;
                $userBundle->cups_left += count($updateCart->cartMetas);
                $userBundle->save();
            }

            $updateCart->save();
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'delete_cart_success',
        ] );
    }

    public static function deleteCartItem( $request ) {

        $validator = Validator::make( $request->all(), [
            'id' => ['nullable', 'exists:carts,id'],
            'cart_item' => ['nullable', 'exists:cart_metas,id'],
        ] );

        $validator->validate();
        $user = auth()->user();
        $query = Cart::where('user_id', $user->id);
    
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        // Retrieve the cart(s) based on the applied filters
        $updateCart = $query->first();

        if ( !$updateCart ) {
            return response()->json( [
                'message' => '',
                'message_key' => 'cart_not_found',
                'errors' => [
                    'cart' => 'Cart not found'
                ]
            ], 422 );
        }

        DB::beginTransaction();

        try {
        
            CartMeta::where('id', $request->cart_item)->delete();
            $orderPrice = 0;
            
            foreach ( $updateCart->cartMetas as $cartProduct ) {

                $froyos = json_decode($cartProduct->froyos,true);
                $froyoCount = count($froyos);
                $syrups = json_decode($cartProduct->syrups,true);
                $syrupCount = count($syrups);
                $toppings = json_decode($cartProduct->toppings,true);
                $toppingCount = count($toppings);
                $product = Product::find($cartProduct->product_id);

                $orderPrice += $product->price ?? 0;

                    $froyoPrices = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
                    asort($froyoPrices);

                    $froyoCount = count($froyos);
                    $freeCount = $product->free_froyo_quantity;

                    if ($froyoCount >= $freeCount) {
                        $chargeableCount = $froyoCount - $freeCount;
                        $chargeableFroyoPrices = array_slice($froyoPrices, 0, $chargeableCount, true);
                        $totalDeduction = array_sum($chargeableFroyoPrices);

                            $metaPrice -= $totalDeduction;
                            $orderPrice -= $totalDeduction;
                    }else{
                        $totalDeduction = array_sum($froyoPrices);
                            $metaPrice -= $totalDeduction;
                            $orderPrice -= $totalDeduction;
                    }

                    $syrupPrices = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
                    asort($syrupPrices);
                        
                    $toppingCount = count($toppings);
                    $freeCount = $product->free_syrup_quantity;

                    if ($syrupCount > $freeCount) {
                        $chargeableCount = $syrupCount - $freeCount;
                        $chargeablesyrupPrices = array_slice($syrupPrices, 0, $chargeableCount, true);
                        $totalDeduction = array_sum($chargeablesyrupPrices);

                            $metaPrice -= $totalDeduction;
                            $orderPrice -= $totalDeduction;
                    }else{
                        $totalDeduction = array_sum($syrupPrices);
                            $metaPrice -= $totalDeduction;
                            $orderPrice -= $totalDeduction;
                    }

                    $toppingPrices = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
                    asort($toppingPrices);
                        
                    $toppingCount = count($toppings);
                    $freeCount = $product->free_topping_quantity;

                    if ($toppingCount > $freeCount) {
                        $chargeableCount = $toppingCount - $freeCount;
                        $chargeabletoppingPrices = array_slice($toppingPrices, 0, $chargeableCount, true);
                        $totalDeduction = array_sum($chargeabletoppingPrices);

                            $metaPrice -= $totalDeduction;
                            $orderPrice -= $totalDeduction;
                    }else{
                        $totalDeduction = array_sum($toppingPrices);
                            $metaPrice -= $totalDeduction;
                            $orderPrice -= $totalDeduction;
                    }
            }

            $updateCart->total_price = Helper::numberFormatV2($orderPrice,2);
            $taxSettings = Option::getTaxesSettings();

            $updateCart->tax = $taxSettings ? (Helper::numberFormatV2(($taxSettings->option_value/100),2) * Helper::numberFormatV2($updateCart->total_price,2)) : 0;
            $updateCart->total_price += Helper::numberFormatV2($updateCart->tax,2);
            $updateCart->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        $cartMetas = $updateCart->cartMetas->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'product' => $meta->product->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->product->image_path),
                'froyo' => $meta->froyos_metas,
                'syrup' => $meta->syrups_metas,
                'topping' => $meta->toppings_metas,
            ];
        });

        return response()->json( [
            'message' => '',
            'message_key' => 'delete_cart_item_success',
            'sesion_key' => $updateCart->session_key,
            'cart_id' => $updateCart->id,
            'vending_machine' => $updateCart->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $updateCart->vendingMachine->operational_hour),
            'total' => $updateCart->total_price,
            'cart_metas' => $cartMetas
        ] );
    }

    public static function validateCartVoucher( $request ){

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

        $user = auth()->user();
        $voucherUsages = VoucherUsage::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->get();

        if ( $voucherUsages->count() >= $voucher->usable_amount ) {
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
        
        // check is has claimed this
        if( $voucher->type != 1 ){
            $userVoucher = UserVoucher::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->where('status',10)->first();
            if(!$userVoucher){
                if( $voucher->type == 2 ){
                    return response()->json( [
                        'message_key' => 'voucher_unclaimed',
                        'message' => __('voucher.voucher_unclaimed'),
                        'errors' => [
                            'voucher' => __('voucher.voucher_unclaimed')
                        ]
                    ], 422 );
                }else{
                    return response()->json( [
                        'message_key' => 'voucher_condition_not_met',
                        'message' => __('voucher.voucher_condition_not_met'),
                        'errors' => [
                            'voucher' => __('voucher.voucher_condition_not_met')
                        ]
                    ], 422 );
                }
            }
        }

        if( !$request->cart_item ){

            if ( $voucher->discount_type == 3 ) {

                $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
    
                $requestedProductIds = collect($request->input('items'))->pluck('product');
                $x = $requestedProductIds->intersect($adjustment->buy_products)->count();

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
                
                $y = $requestedProductIds->intersect($adjustment->get_product)->count();
    
                if (in_array($adjustment->get_product, $adjustment->buy_products)) {
                    if( $adjustment->buy_quantity == $adjustment->get_quantity ){
                        $y = $x;
                    } else {
                        $y -= $adjustment->buy_quantity;
                    }
                } 
    
                if ( $y < $adjustment->get_quantity ) {
                    return response()->json( [
                        'required_amount' => $adjustment->get_quantity,
                        'message' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->get_quantity . ' ' . Product::where( 'id',  $adjustment->get_product[0] )->value( 'title' ) ] ),
                        'message_key' => 'voucher.min_quantity_of_y',
                        'errors' => [
                            'voucher' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->get_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] )
                        ]
                    ], 422 );
                }
    
            } else {
    
                $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
                $orderPrice = 0;
                if(isset($request->items)){
                    foreach ( $request->items as $product ) {
    
                        $froyos = $product['froyo'];
                        $froyoCount = count($froyos);
                        $syrups = $product['syrup'];
                        $syrupCount = count($syrups);
                        $toppings = $product['topping'];
                        $toppingCount = count($toppings);
                        $product = Product::find($product['product']);
                        $metaPrice = 0;
        
                        $orderPrice += $product->price ?? 0;
        
                        // new calculation 
                        $froyoPrices = Froyo::whereIn('id', $froyos)->sum('price');
                        $orderPrice += $froyoPrices;
    
                        $syrupPrices = Syrup::whereIn('id', $syrups)->sum('price');
                        $orderPrice += $syrupPrices;
    
                        $toppingPrices = Topping::whereIn('id', $toppings)->sum('price');
                        $orderPrice += $toppingPrices;
                    }
                }
    
                if ( $orderPrice < $adjustment->buy_quantity ) {
                    return response()->json( [
                        'required_amount' => $adjustment->buy_quantity,
                        'message' => __( 'voucher.min_spend_of_x', [ 'title' => $adjustment->buy_quantity ] ),
                        'message_key' => 'voucher.min_spend_of_x',
                        'errors' => [
                            'voucher' => __( 'voucher.min_spend_of_x', [ 'title' => $adjustment->buy_quantity ] )
                        ]
                    ], 422 );
                }
    
            }
        }
    
        return response()->json( [
            'message' => 'voucher.voucher_validated',
        ] );
    }

    public static function checkCartEligibility( $request, $cartMeta ){

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
                'message' => 'voucher.voucher_not_available',
                'message_key' => 'voucher.voucher_not_available',
                'errors' => [
                    'voucher' => __( 'voucher.voucher_not_available' )
                ]
            ], 422 );
        }

        $user = auth()->user();
        $voucherUsages = VoucherUsage::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->get();

        if ( $voucherUsages->count() >= $voucher->usable_amount ) {
            return response()->json( [
                'message' => __('voucher.voucher_you_have_maximum_used'),
                'message_key' => 'voucher.voucher_you_have_maximum_used',
                'errors' => [
                    'voucher' => __( 'voucher.voucher_you_have_maximum_used' )
                ]
            ], 422 );
        }

        // total claimable
        if ( $voucher->total_claimable <= 0 ) {
            return response()->json( [
                'message' => __('voucher.voucher_fully_claimed'),
               'message_key' => 'voucher.voucher_fully_claimed',
                'errors' => [
                    'voucher' => __( 'voucher.voucher_fully_claimed' )
                ]
            ], 422 );
        }
        
        // check is has claimed this
        if( $voucher->type != 1 ){
            $userVoucher = UserVoucher::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->where('status',10)->first();
            if(!$userVoucher){
                if( $voucher->type == 2 ){
                    return response()->json( [
                        'message_key' => 'voucher_unclaimed',
                        'message' => __('voucher.voucher_unclaimed'),
                        'errors' => [
                            'voucher' => __( 'voucher.voucher_unclaimed' )
                        ]
                    ], 422 );
                }else{
                    return response()->json( [
                        'message_key' => 'voucher_unclaimed',
                        'message' => __('voucher.voucher_condition_not_met'),
                        'errors' => [
                            'voucher' => __( 'voucher.voucher_condition_not_met' )
                        ]
                    ], 422 );
                }
            }
        }

        if ( $voucher->discount_type == 3 ) {

            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );

            $requestedProductIds = $cartMeta->pluck('product_id');
            $x = $requestedProductIds->intersect($adjustment->buy_products)->count();
 
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
            
            $y = $requestedProductIds->intersect($adjustment->get_product)->count();

            if (in_array($adjustment->get_product, $adjustment->buy_products)) {
                if( $adjustment->buy_quantity == $adjustment->get_quantity ){
                    $y = $x;
                } else {
                    $y -= $adjustment->buy_quantity;
                }
            } 

            if ( $y < $adjustment->get_quantity ) {
                return response()->json( [
                    'required_amount' => $adjustment->get_quantity,
                    'message' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->get_quantity . ' ' . Product::where( 'id',  $adjustment->get_product[0] )->value( 'title' ) ] ),
                    'message_key' => 'voucher.min_quantity_of_y',
                    'errors' => [
                        'voucher' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->get_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] )
                    ]
                ], 422 );
            }

        } else {

            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
            $orderPrice = 0;
            if(isset($request->items)){
                foreach ( $request->items as $product ) {

                    $froyos = $product['froyo'];
                    $froyoCount = count($froyos);
                    $syrups = $product['syrup'];
                    $syrupCount = count($syrups);
                    $toppings = $product['topping'];
                    $toppingCount = count($toppings);
                    $product = Product::find($product['product']);
                    $metaPrice = 0;
    
                    $orderPrice += $product->price ?? 0;
    
                    // new calculation 
                    $froyoPrices = Froyo::whereIn('id', $froyos)->sum('price');
                    $orderPrice += $froyoPrices;

                    $syrupPrices = Syrup::whereIn('id', $syrups)->sum('price');
                    $orderPrice += $syrupPrices;

                    $toppingPrices = Topping::whereIn('id', $toppings)->sum('price');
                    $orderPrice += $toppingPrices;
                }
            }

            if ( $orderPrice < $adjustment->buy_quantity ) {
                return response()->json( [
                    'required_amount' => $adjustment->buy_quantity,
                    'message' => __( 'voucher.min_spend_of_x', [ 'title' => $adjustment->buy_quantity ] ),
                    'message_key' => 'voucher.min_spend_of_x',
                    'errors' => [
                        'voucher' => __( 'voucher.min_spend_of_x', [ 'title' => $adjustment->buy_quantity ] )
                    ]
                ], 422 );
            }

        }
    
        return response()->json( [
            'message' => 'voucher.voucher_validated',
        ] );
    }

    public static function calculateBundleCharges( $cartMetas ){
        
        $totalCartDeduction = 0;

        foreach( $cartMetas as $cartMeta ){
            $cartMeta->total_price = 0;

            $froyos = json_decode($cartMeta->froyos, true);
            $syrups = json_decode($cartMeta->syrups, true);
            $toppings = json_decode($cartMeta->toppings, true);

            $product = Product::find( $cartMeta->product_id );

            // calculate free item
            $froyoPrices = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
            rsort($froyoPrices);

            $froyoCount = count($froyos);
            $freeCount = $product->free_froyo_quantity;

            if ($froyoCount > $freeCount) {
                $chargeableCount = $froyoCount - $freeCount;
                $chargeableFroyoPrices = array_slice($froyoPrices, 0, $chargeableCount, true);
                $totalDeduction = array_sum($chargeableFroyoPrices);
                $cartMeta->total_price += $totalDeduction;
                $totalCartDeduction += $totalDeduction;
            }

            $syrupPrices = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
            rsort($syrupPrices);

            $syrupCount = count($syrups);
            $freeCount = $product->free_syrup_quantity;

            if ($syrupCount > $freeCount) {
                $chargeableCount = $syrupCount - $freeCount;
                $chargeablesyrupPrices = array_slice($syrupPrices, 0, $chargeableCount, true);

                $totalDeduction = array_sum($chargeablesyrupPrices);
                $cartMeta->total_price += $totalDeduction;
                $totalCartDeduction += $totalDeduction;
            }
        
            $toppingPrices = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
            rsort($toppingPrices);
            
            $toppingCount = count($toppings);
            $freeCount = $product->free_topping_quantity;

            if ($toppingCount > $freeCount) {
                $chargeableCount = $toppingCount - $freeCount;
                $chargeabletoppingPrices = array_slice($toppingPrices, 0, $chargeableCount, true);
                $totalDeduction = array_sum($chargeabletoppingPrices);

                $cartMeta->total_price += $totalDeduction;
                $totalCartDeduction += $totalDeduction;

            }

            $cartMeta->save();

        }
        return $totalCartDeduction;
    }
    
    public static function validateCartBundleRules ( $request ){

        // check for bundle with voucher
        if (($request->promo_code && ($request->bundle || $request->user_bundle)) || 
            ($request->bundle && $request->user_bundle)) {
            return response()->json([
                'message' => 'Invalid combination of bundle and promotion.',
                'message_key' => 'bundle_not_available',
                'errors' => [
                    'voucher' => 'Promo code, bundle, and user bundle cannot be used together.',
                ]
            ], 422);
        }

        return response()->json('',200);
    }
}