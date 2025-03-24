<?php

namespace App\Services;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
    File,
};
use Barryvdh\DomPDF\Facade\Pdf as PDF;

use App\Models\{
    FileManager,
    Option,
    Order,
    OrderTransaction,
    OrderMeta,
    Product,
    Froyo,
    Syrup,
    Topping,
    Cart,
    CartMeta,
    Voucher,
    VoucherUsage,
    UserVoucher,
    ProductBundle,
    UserBundle,
    UserBundleHistory,
    UserBundleHistoryMeta,
    UserBundleTransaction,
    VendingMachine,
    VendingMachineStock,
    MachineSalesData,
    OrderTransactionLog,
};

use Helper;
use Illuminate\Validation\Rule;

use Carbon\Carbon;

class OrderService
{
    public static function calendarAllOrders( $request ) {

        $orders = Order::where( 'invoice_date', '>=', $request->start )
            ->where( 'invoice_date', '<=', $request->end )
            ->orderBy( 'invoice_date', 'ASC' )
            ->get();

        $currentOrders = [];
        foreach ( $orders as $order ) {

            $plateNumber = $order->vehicle ? $order->vehicle->license_plate : '-';
            $notes = $order->notes ? $order->notes : '-';

            array_push( $currentOrders, [
                'id' => Helper::encode( $order->id ),
                'allDay' => true,
                'start' => $order->invoice_date . ' 00:00:00',
                'end' => $order->invoice_date . ' 23:59:59',
                'title' => [
                    'html' => 'Reference:' . $order->reference . '<br>Plate Number:' . $plateNumber . '<br>Notes:' . $notes,
                ],
                'color' => '#aad418',
            ] );
        }

        return response()->json( $currentOrders );
    }

    public static function allOrders( $request, $export = false ) {

        $order = Order::with( [
            'vendingMachine',
            'user',
            'orderMetas',
        ] )->select( 'orders.*' )
        ->orderBy( 'created_at', 'DESC' );
            
        $filterObject = self::filter( $request, $order );
        $order = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $order->orderBy( 'orders.order_date', $dir );
                    break;
                case 2:
                    $order->orderBy( 'orders.reference', $dir );
                    break;
                case 3:
                    $order->orderBy( 'orders.owner_id', $dir );
                    break;
                case 4:
                    $order->orderBy( 'orders.farm_id', $dir );
                    break;
                case 5:
                    $order->orderBy( 'orders.buyer_id', $dir );
                    break;
                case 6:
                    $order->orderBy( 'orders.status', $dir );
                    break;
            }
        }

        if ( $export == false ) {

            $orderCount = $order->count();

            $limit = $request->length;
            $offset = $request->start;

            $orders = $order->skip( $offset )->take( $limit )->get();

            if ( $orders ) {
                $orders->append( [
                    'encrypted_id',
                ] );
            }

            $totalRecord = Order::count();

            $data = [
                'orders' => $orders,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $orderCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

        } else {

            return $order->get();
        }        
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->order_date ) ) {
            if ( str_contains( $request->order_date, 'to' ) ) {
                $dates = explode( ' to ', $request->order_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'orders.order_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->order_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'orders.order_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->reference ) ) {
            $model->where( 'orders.reference', 'LIKE', '%' . $request->customer . '%' );
            $filter = true;
        }

        if ( !empty( $request->owner ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'owner', function ( $q ) use ( $request ) {
                    $q->where( 'fullname', 'LIKE', '%' . $request->owner . '%' );
                });
            });
            $filter = true;
        }

        if ( !empty( $request->farm ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'farm', function ( $q ) use ( $request ) {
                    $q->where( 'title', 'LIKE', '%' . $request->farm . '%' );
                });
            });
            $filter = true;
        }

        if ( !empty( $request->buyer ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'buyer', function ( $q ) use ( $request ) {
                    $q->where( 'name', 'LIKE', '%' . $request->buyer . '%' );
                });
            });
            $filter = true;
        }

        if ( !empty( $request->user ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'user', function ( $q ) use ( $request ) {
                    $q->where( 'phone_number', 'LIKE', '%' . $request->user . '%' );
                });
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'orders.status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneOrder( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $order = Order::with( [
            'orderMetas','vendingMachine','user', 'productBundle', 'userBundle'
        ] )->find( $request->id );

        $order->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $order->vendingMachine->operational_hour)->setAttribute('image_path', $order->vendingMachine->image_path);

        $orderMetas = $order->orderMetas->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'product' => $meta->product->makeHidden(['created_at', 'updated_at', 'status'])->setAttribute('image_path', $meta->product->image_path),
                'froyo' => $meta->froyos_metas,
                'syrup' => $meta->syrups_metas,
                'topping' => $meta->toppings_metas,
            ];
        });
    
        // Attach the cart metas to the cart object
        $order->orderMetas = $orderMetas;
        $order->qr_code = $order->status != 20 && in_array($order->status, [3, 10]) ? self::generateQrCode($order) : null;

        return response()->json( $order );
    }

    public static function getLatestOrderIncrement() {

        $latestOrder = Order::where( 'reference', 'LIKE', '%' . date( 'Y/m' ) . '%' )
            ->orderBy( 'reference', 'DESC' )
            ->first();

        if ( $latestOrder ) {
            $parts = explode( ' ', $latestOrder->reference );
            return $parts[1];
        }

        return 0;
    }

    public static function createOrder( $request ) {

        if ($request->has('products')) {
            $decodedProducts = [];
            foreach ($request->products as $product) {
                $productArray = json_decode($product, true);
        
                $productArray['productId'] = explode('-', $productArray['productId'])[0];
        
                $decodedProducts[] = $productArray;
            }
        
            $request->merge(['products' => $decodedProducts]);
        }

        $validator = Validator::make( $request->all(), [
            'user' => [ 'required', 'exists:users,id'  ],
            'vending_machine' => [ 'nullable', 'exists:vending_machines,id'  ],
            'products' => [ 'nullable' ],
            'products.*.productId' => [ 'nullable', 'exists:products,id' ],
            'products.*.froyo' => [ 'nullable', 'exists:froyos,id' ],
            'products.*.syrup' => [ 'nullable', 'exists:syrups,id' ],
            'products.*.topping' => [ 'nullable', 'exists:toppings,id' ],
        ] );

        $attributeName = [
            'user' => __( 'order.user' ),
            'vending_machine' => __( 'order.vending_machine' ),
            'products' => __( 'order.products' ),
            'froyo' => __( 'order.froyo' ),
            'syrup' => __( 'order.syrup' ),
            'topping' => __( 'order.topping' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $orderPrice = 0;

            $createOrder = Order::create( [
                'product_id' => null,
                'product_bundle_id' => null,
                'outlet_id' => null,
                'user_id' => $request->user,
                'vending_machine_id' => $request->vending_machine,
                'total_price' => 0,
                'discount' => 0,
                'reference' => Helper::generateOrderReference(),
                'payment_method' => 1,
                'status' => 3,
            ] );

            foreach ( $request->products as $product ) {
                $metaPrice = 0;

                $froyos = $product['froyo'];
                $froyoCount = count($froyos);
                $syrups = $product['syrup'];
                $syrupCount = count($syrups);
                $toppings = $product['topping'];
                $toppingCount = count($toppings);
                $product = Product::find($product['productId']);

                $orderMeta = OrderMeta::create( [
                    'order_id' => $createOrder->id,
                    'product_id' => $product->id,
                    'product_bundle_id' => null,
                    'froyos' =>  json_encode($froyos),
                    'syrups' =>  json_encode($syrups),
                    'toppings' =>  json_encode($toppings),
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

                /*

                if (($product->default_froyo_quantity != null || $product->default_froyo_quantity != 0 ) && $froyoCount > $product->default_froyo_quantity) {
                    $froyoPrices = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
                    asort($froyoPrices);
                    $mostExpensiveFroyoPrice = end($froyoPrices);
                    $orderPrice += $mostExpensiveFroyoPrice;
                } 
                
                if (($product->default_syrup_quantity != null || $product->default_syrup_quantity != 0 ) && $syrupCount > $product->default_syrup_quantity) {
                    $syrupPrices = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
                    asort($syrupPrices);
                    $mostExpensiveSyrupPrice = end($syrupPrices);
                    $orderPrice += $mostExpensiveSyrupPrice;
                } 

                if (($product->default_topping_quantity != null || $product->default_topping_quantity != 0 ) && $toppingCount > $product->default_topping_quantity) {
                    $toppingPrices = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
                    asort($toppingPrices);
                    $mostExpensiveToppingPrice = end($toppingPrices);
                    $orderPrice += $mostExpensiveToppingPrice;
                } 
                */

                $orderMeta->total_price = $metaPrice;
                $orderMeta->save();
            }

            $createOrder->total_price = Helper::numberFormatV2($orderPrice,2);
            $createOrder->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.orders' ) ) ] ),
        ] );
    }

    public static function updateOrder( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        if ($request->has('products')) {
            $decodedProducts = [];
            foreach ($request->products as $product) {
                $productArray = json_decode($product, true);
        
                $productArray['productId'] = explode('-', $productArray['productId'])[0];
        
                $decodedProducts[] = $productArray;
            }
        
            $request->merge(['products' => $decodedProducts]);
        }

        $validator = Validator::make( $request->all(), [
            'id' => [ 'required', 'exists:orders,id'  ],
            'user' => [ 'required', 'exists:users,id'  ],
            'vending_machine' => [ 'nullable', 'exists:vending_machines,id'  ],
            'products' => [ 'nullable' ],
            'products.*.productId' => [ 'nullable', 'exists:products,id' ],
            'products.*.froyo' => [ 'nullable', 'exists:froyos,id' ],
            'products.*.syrup' => [ 'nullable', 'exists:syrups,id' ],
            'products.*.topping' => [ 'nullable', 'exists:toppings,id' ],
        ] );

        $attributeName = [
            'reference' => __( 'order.reference' ),
            'farm' => __( 'order.farm' ),
            'buyer' => __( 'order.buyer' ),
            'grade' => __( 'order.grade' ),
            'weight' => __( 'order.weight' ),
            'rate' => __( 'order.rate' ),
            'total' => __( 'order.total' ),
            // 'subtotal' => __( 'order.subtotal' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $orderPrice = 0;

            $updateOrder = Order::find( $request->id );
            $updateOrder->user_id = $request->user;
            $updateOrder->vending_machine_id = $request->vending_machine;
            $updateOrder->save();

            OrderMeta::where( 'order_id', $updateOrder->id )->delete();

            foreach ( $request->products as $product ) {
                $metaPrice = 0;

                $froyos = $product['froyo'];
                $froyoCount = count($froyos);
                $syrups = $product['syrup'];
                $syrupCount = count($syrups);
                $toppings = $product['topping'];
                $toppingCount = count($toppings);
                $product = Product::find($product['productId']);

                $orderMeta = OrderMeta::create( [
                    'order_id' => $updateOrder->id,
                    'product_id' => $product->id,
                    'product_bundle_id' => null,
                    'froyos' =>  json_encode($froyos),
                    'syrups' =>  json_encode($syrups),
                    'toppings' =>  json_encode($toppings),
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

                /*
                if (($product->default_froyo_quantity != null || $product->default_froyo_quantity != 0 ) && $froyoCount > $product->default_froyo_quantity) {
                    $froyoPrices = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
                    asort($froyoPrices);
                    $mostExpensiveFroyoPrice = end($froyoPrices);
                    $orderPrice += $mostExpensiveFroyoPrice;
                } 
                
                if (($product->default_syrup_quantity != null || $product->default_syrup_quantity != 0 ) && $syrupCount > $product->default_syrup_quantity) {
                    $syrupPrices = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
                    asort($syrupPrices);
                    $mostExpensiveSyrupPrice = end($syrupPrices);
                    $orderPrice += $mostExpensiveSyrupPrice;
                } 

                if (($product->default_topping_quantity != null || $product->default_topping_quantity != 0 ) && $toppingCount > $product->default_topping_quantity) {
                    $toppingPrices = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
                    asort($toppingPrices);
                    $mostExpensiveToppingPrice = end($toppingPrices);
                    $orderPrice += $mostExpensiveToppingPrice;
                } 
                */

                $orderMeta->total_price = $metaPrice;
                $orderMeta->save();
            }

            $updateOrder->total_price = Helper::numberFormatV2($orderPrice,2);
            $updateOrder->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.orders' ) ) ] ),
        ] );
    }

    public static function exportOrders($request)
    {
        $orders = self::allOrders($request, true);

        $grades = [
            'A',
            'B',
            'C',
            'D',
        ];

        $grandSubtotalTotal = $grandTotalTotal = 0;
        $grandRates['A']['rates'] = 0;
        $grandRates['A']['weight'] = 0;
        $grandRates['B']['rates'] = 0;
        $grandRates['B']['weight'] = 0;
        $grandRates['C']['rates'] = 0;
        $grandRates['C']['weight'] = 0;
        $grandRates['D']['rates'] = 0;
        $grandRates['D']['weight'] = 0;
    
        $html = '<table>';
    
        $html .= '
            <thead>
                <tr>
                    <th colspan="6"></th>
                    <th colspan="' . (count($grades) * 3) . '" class="text-center"><strong>' . __('order.order_items') . '</strong></th>
                    <th colspan="2"></th>
                <tr>
                    <th><strong>' . __('datatables.no') . '</strong></th>
                    <th><strong>' . __('order.reference') . '</strong></th>
                    <th><strong>' . __('order.order_date') . '</strong></th>
                    <th><strong>' . __('order.owner') . '</strong></th>
                    <th><strong>' . __('order.farm') . '</strong></th>
                    <th><strong>' . __('order.buyer') . '</strong></th>';
    
        foreach ($grades as $grade) {
            $html .= '<th><strong>' . __('order.grade') . '</strong></th>';
            $html .= '<th><strong>' . __('order.rate') . '</strong></th>';
            $html .= '<th><strong>' . __('order.weight') . '</strong></th>';
        }
    
        $html .= '<th><strong>' . __('order.total') . '</strong></th>';
        $html .= '</tr>
            </thead>';
        $html .= '<tbody>';
    
        foreach ($orders as $key => $order) {
    
            $html .= '
                <tr>
                    <td>' . (intval($key) + 1) . '</td>
                    <td>' . $order['reference'] . '</td>
                    <td>' . $order['order_date'] . '</td>
                    <td>' . ($order->farm->owner->name ?? '-') . '</td>
                    <td>' . ($order->farm->title ?? '-') . '</td>
                    <td>' . ($order->buyer->name ?? '-') . '</td>';
    
            $grandRates = [];
                            
            foreach($grades as $grade) {
                $grandRates[$grade]['rates'] = 0;
                $grandRates[$grade]['weight'] = 0;
            }
        
            foreach($order->orderMetas as $orderMeta) {
                $grade = $orderMeta['grade'];
                $grandRates[$grade]['rates'] += $orderMeta['rate'];
                $grandRates[$grade]['weight'] += $orderMeta['weight'];
            }

            foreach($grades as $grade) {
                 $html .= '<td>' . $grade . '</td>';
                 $html .= '<td>' . ( $grandRates[$grade]['rates'] != 0 ? $grandRates[$grade]['rates'] : '-' ) . '</td>';
                 $html .= '<td>' . ( $grandRates[$grade]['weight'] != 0 ? $grandRates[$grade]['weight'] : '-' ) . '</td>';              
            }
    
            // $html .= '<td>' . $order['subtotal'] . '</td>';
            $html .= '<td>' . $order['total'] . '</td>';
    
            $grandTotalTotal += $order['total'];
            $grandSubtotalTotal += $order['subtotal'];
    
            $html .= '</tr>';
        }
    
        $html .= '
            </tbody></table>';
    
        Helper::exportReport($html, 'Order');
    }

    public static function salesReport( $request ) {

        $date = $request->date ? $request->date : date( 'Y m' );

        $start = Carbon::createFromFormat( 'Y m', $date, 'Asia/Kuala_Lumpur' )->startOfMonth()->timezone( 'UTC' );

        $end = Carbon::createFromFormat( 'Y m', $date, 'Asia/Kuala_Lumpur' )->endOfMonth()->timezone( 'UTC' );

        $currenctPeriodSales = [];

        $salesRecords = Order::with( [
            'farm.owner',
            'buyer',
            'orderMetas',
        ] )->where( 'created_at', '>=', $start->format( 'Y-m-d H:i:s' ) )
            ->where( 'created_at', '<=', $end->format( 'Y-m-d H:i:s' ) )
            ->orderBy( 'created_at', 'DESC' )
            ->get();

        if ( $salesRecords ) {
            $salesRecords->append( [
                'encrypted_id',
            ] );
        }

        return [
            'orders' => $salesRecords->toArray(),
        ];
    }

    private static function getIngredientPrice($id, $type, $prices = null)
    {
        // If we already have the prices, we can directly use them
        if ($prices && isset($prices[$id])) {
            return $prices[$id];
        }
    
        // Otherwise, fall back to the original way (if necessary)
        $amount = 0;
    
        switch ($type) {
            case 'froyo':
                $amount = Froyo::find($id)->price;
                break;
            case 'syrup':
                $amount = Syrup::find($id)->price;
                break;
            case 'topping':
                $amount = Topping::find($id)->price;
                break;
            default:
                $amount = Froyo::find($id)->price;
                break;
        }
    
        return $amount;
    }

    public static function updateOrderStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateOrder = Order::find( $request->id );
            
            $updateOrder->status = $updateOrder->status != 20 ? 20 : 1;

            $updateOrder->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'froyo' => $updateOrder,
                    'message_key' => 'update_order_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_order_failed',
            ], 500 );
        }
    }
    
    public static function generateTestOrder()
    {
        DB::beginTransaction();
    
        try {
            // Generate 3 test orders
            $orders = Order::factory()->count(3)->create();

            foreach( $orders as $order ) {
                $order->qr_code = $order->status != 20 && in_array($order->status, [3, 10]) ? self::generateQrCode($order) : null;
            }

            // Commit transaction
            DB::commit();
    
            // Generate PDF
            $pdf = \App::make('dompdf.wrapper');
            $pdf->setPaper('a4', 'portrait');
    
            // Load the orders into the Blade view for the PDF
            $pdf->loadView('admin.order.test_orders_pdf', compact('orders'));
    
            // Return the PDF as a downloadable file
            return $pdf->download('test_orders.pdf');
    
        } catch (\Throwable $th) {
            DB::rollBack();
    
            return back()->with('error', 'Failed to generate test orders: ' . $th->getMessage());
        }
    }

    public static function updateOrderStatusView( $request ) {

        DB::beginTransaction();

        try {

            $updateOrder = Order::find( $request->id );
            $updateOrder->status = $request->status;

            $updateOrder->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'froyo' => $updateOrder,
                    'message_key' => 'update_order_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_froyo_failed',
            ], 500 );
        }
    }

    public static function generateQrCode($order)
    {

        $orderId = $order->reference;

        // Set QR code options (optional)
        $options = new QROptions([
            'version'    => 5,   // Controls the size of the QR code
            'eccLevel'   => QRCode::ECC_L, // Error correction level (L, M, Q, H)
            'outputType' => QRCode::OUTPUT_IMAGE_PNG, // Image output format (PNG)
            'scale'      => 5,   // Pixel size
        ]);

        // Generate the QR code
        $qrcode = new QRCode($options);
        $qrImage = $qrcode->render($orderId);

        // Remove the "data:image/png;base64," prefix
        $base64Image = str_replace('data:image/png;base64,', '', $qrImage);
    
        // Decode the Base64 string
        $decodedImage = base64_decode($base64Image);

        $fileName = "qr-codes/order-{$orderId}.png";
        $filePath = "public/{$fileName}";

        // Save the QR code image in storage/app/public
        Storage::put($filePath, $decodedImage);

        // Generate the URL for the QR code
        $qrUrl = asset("storage/{$fileName}");

        return $qrUrl;
    }
    
    public static function getOrder($request)
    {
        // Validate the incoming request parameters (id and reference)
        $validator = Validator::make($request->all(), [
            'id' => ['nullable', 'exists:orders,id'],
            'status' => ['nullable', 'in:1,2,3,10,20'],
            'reference' => ['nullable', 'exists:orders,reference'],
            'per_page' => ['nullable', 'integer', 'min:1'], // Validate per_page input
        ]);
    
        // If validation fails, it will automatically throw an error
        $validator->validate();
    
        // Get the current authenticated user
        $user = auth()->user();
    
        // Start by querying orders for the authenticated user
        $query = Order::where('user_id', $user->id)
            ->with(['vendingMachine', 'voucher', 'productBundle', 'userBundle'])
            ->orderBy('created_at', 'DESC');
    
        // Apply filters
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
    
        if ($request->has('reference')) {
            $query->where('reference', $request->reference);
        }
    
        if ($request->has('user_bundle')) {
            $query->whereHas('userBundle');
        }
    
        // Use paginate instead of get
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $userOrders = $query->paginate($perPage);
    
        // Modify each order and its related data
        $userOrders->getCollection()->transform(function ($order) {
            $order->append( ['order_status_label'] );

            if($order->vendingMachine){
                $order->vendingMachine->makeHidden(['created_at', 'updated_at', 'status'])
                ->setAttribute('operational_hour', $order->vendingMachine->operational_hour)
                ->setAttribute('image_path', $order->vendingMachine->image_path);
            }
    
            $orderMetas = $order->orderMetas->map(function ($meta) {
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

            $order->orderMetas = $orderMetas;
            
            if( $order->userBundle ) {
                $order->userBundle->productBundle->append( ['image_path','bundle_rules'] );

                $orderMetaCount = Order::query()
                ->where('user_bundle_id', $order->user_bundle_id)
                ->where('id', '<>', $order->id)
                ->where('created_at', '<', $order->created_at)
                ->latest('created_at')
                ->withCount('orderMetas')
                ->get()
                ->sum('order_metas_count');

                $order->cup_used = count( $order->orderMetas );
                $order->cup_redeemed = $orderMetaCount + $order->cup_used;
                $order->cup_left = $order->userBundle->productBundle->productBundleMetas->first()->quantity - $orderMetaCount - $order->cup_used;

            }else{
                $order->cup_used = null;
                $order->cup_redeemed = null;
                $order->cup_left = null;
            }

            $order->qr_code = $order->status != 20 && in_array($order->status, [3, 10]) ? self::generateQrCode($order) : null;

            return $order;
        });

        foreach( $userOrders as $userOrder ) {
            $userOrder->order_metas = $userOrder->orderMetas;
            $userOrder->orderMetas = null;
            unset($userOrder->orderMetas);
        }
    
        // Return the paginated response
        return response()->json([
            'message' => '',
            'message_key' => 'get_order_success',
            'orders' => $userOrders,
        ]);
    }
    

    public static function checkout( $request ) {

        $validator = Validator::make($request->all(), [
            'cart' => ['required', 'exists:carts,id'],
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
            'payment_method' => ['nullable', 'in:1,2'],
        ]);

        $user = auth()->user();

        $query = Cart::where('user_id', $user->id)
        ->where('id', $request->cart)
        ->where('status',10);
    
        $userCart = $query->first();
        
        if (!$userCart) {
            return response()->json([
                'message' => '',
                'message_key' => 'cart_is_empty',
                'carts' => []
            ], 422);
        }
        
        $validator->validate();

        if( $request->promo_code ){
            $test = self::validateVoucher($request);

            if ($test->getStatusCode() === 422) {
                return $test;
            }
        }
        
        // check wallet balance 
        $userWallet = $user->wallets->where('type',1)->first();

        if( $userCart->total_price == 0 ) {
            $request->merge( [
                'payment_method' => 1
            ] );
        }

        if( $request->payment_method == 1 ){

            if (!$userWallet) {
                return response()->json([
                    'message' => 'Wallet Not Found',
                    'message_key' => 'wallet_not_found',
                ]);
            }else{
                if( $request->promo_code ){
                    $voucher = Voucher::where( 'id', $request->promo_code )
                    ->orWhere('promo_code', $request->promo_code)->first();
                    $orderPrice = $userCart->total_price;
    
                    if ( $voucher->discount_type == 3 ) {
    
                        $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
            
                        $x = $userCart->cartMetas->whereIn( 'product_id', $adjustment->buy_products )->count();
            
                        if ( $x >= $adjustment->buy_quantity ) {
                            $getProductMeta = $userCart->cartMetas
                            ->where('product_id', $adjustment->get_product)
                            ->sortBy('total_price')
                            ->first();                    
    
                            if ($getProductMeta) {
                                $getProduct = Product::find($adjustment->get_product);
                                if ($getProduct && $getProduct->price) {
                                    $orderPrice -= $getProduct->price;
                                }
                            }
                        }
            
                    } else if ( $voucher->discount_type == 2 ) {
    
                        $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
    
                        $x = $userCart->total_price;
    
                        if ( $x >= $adjustment->buy_quantity ) {
                            $orderPrice -= floatVal($adjustment->discount_quantity);
                        }
            
                    } else {
    
                        $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
            
                        $x = $userCart->total_price;
            
                        if ( $x >= $adjustment->buy_quantity ) {
                            $orderPrice = $orderPrice - ( $orderPrice * $adjustment->discount_quantity / 100 );
                        }
                    }
    
                    if( $userWallet->balance < $orderPrice ){
                        return response()->json([
                            'message' => 'Balance is not enough, please top up to continue',
                            'message_key' => 'insufficient_balance',
                            'errors' => [
                                'wallet' => 'Balance is not enough, please top up to continue',
                            ]
                        ], 422);
                    }
    
                }else{
                    if( $userWallet->balance < $userCart->total_price ){
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
        }

        DB::beginTransaction();
        try {
        
            $orderPrice = 0;
            $user = auth()->user();
            $userWallet = $user->wallets->where( 'type', 1 )->first();
            $bundle = ProductBundle::where( 'id', $userCart->product_bundle_id )->where( 'status', 10 )->first();
            $userBundle = UserBundle::where( 'id', $userCart->user_bundle_id )->where( 'status', 10 )->first();
            $taxSettings = Option::getTaxesSettings();

            $order = Order::create( [
                'user_id' => $user->id,
                'product_id' => null,
                'product_bundle_id' => $userCart->product_bundle_id,
                'outlet_id' => null,
                'vending_machine_id' => $userCart->vending_machine_id,
                'user_bundle_id' => $userCart->user_bundle_id,
                'total_price' => $orderPrice,
                'discount' => 0,
                'status' => 1,
                'reference' => Helper::generateOrderReference(),
                'tax' => 0,
            ] );

            foreach ( $userCart->cartMetas as $cartProduct ) {

                $froyos = json_decode($cartProduct->froyos,true);
                $froyoCount = count($froyos);
                $syrups = json_decode($cartProduct->syrups,true);
                $syrupCount = count($syrups);
                $toppings = json_decode($cartProduct->toppings,true);
                $toppingCount = count($toppings);
                $product = Product::find($cartProduct->product_id);
                $metaPrice = 0;

                $orderMeta = OrderMeta::create( [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_bundle_id' => null,
                    'froyos' =>  $cartProduct->froyos,
                    'syrups' =>  $cartProduct->syrups,
                    'toppings' =>  $cartProduct->toppings,
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

                $cartProduct->status = 20;
                $cartProduct->save();
            }

            $order->subtotal = $orderPrice;

            if( $request->promo_code || $userCart->voucher_id ){

                if( $request->promo_code ) {
                    $voucher = Voucher::where( 'id', $request->promo_code )
                    ->orWhere('promo_code', $request->promo_code)->first();
                }else if( $userCart->voucher_id ) {
                    $voucher = Voucher::where( 'id', $userCart->voucher_id )->first();
                }

                if ( $voucher->discount_type == 3 ) {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $userCart->cartMetas->whereIn( 'product_id', $adjustment->buy_products )->count();

                    if ( $x >= $adjustment->buy_quantity ) {
                        $getProductMeta = $userCart->cartMetas
                        ->where('product_id', $adjustment->get_product)
                        ->sortBy('total_price')
                        ->first();                    

                        if ($getProductMeta) {

                            $discount = 0;
                            $discount += $getProductMeta->product->price;

                            // $froyoPrices = Froyo::whereIn('id', json_decode($getProductMeta->froyos, true))->sum('price');
                            // $discount += $froyoPrices;
        
                            // $syrupPrices = Syrup::whereIn('id', json_decode($getProductMeta->syrups, true))->sum('price');
                            // $discount += $syrupPrices;
        
                            // $toppingPrices = Topping::whereIn('id', json_decode($getProductMeta->toppings, true))->sum('price');
                            // $discount += $toppingPrices;

                            $orderPrice -= Helper::numberFormatV2($discount,2,false,true);
                            $order->discount = Helper::numberFormatV2($discount,2,false,true);
                            $getProductMeta->total_price = 0 + $getProductMeta->additional_charges;
                            $getProductMeta->save();
                        }
                    }

                } else if ( $voucher->discount_type == 2 ) {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $userCart->total_price;
                    if ( $x >= $adjustment->buy_quantity ) {
                        $orderPrice -= $adjustment->discount_quantity;
                        $order->discount = Helper::numberFormatV2($adjustment->discount_quantity,2,false,true);
                    }
        
                } else {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $userCart->total_price;
                    if ( $x >= $adjustment->buy_quantity ) {
                        $order->discount = Helper::numberFormatV2(( $orderPrice * $adjustment->discount_quantity / 100 ),2,false,true);
                        $orderPrice = $orderPrice - ( $orderPrice * $adjustment->discount_quantity / 100 );
                    }
                }

                $order->voucher_id = $voucher->id;
                
                VoucherUsage::create( [
                    'user_id' => auth()->user()->id,
                    'order_id' => $order->id,
                    'voucher_id' => $voucher->id,
                    'status' => 10
                ] );

                // if user have voucher, else
                $userVoucher = UserVoucher::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->where( 'status', 10 )->first();

                if($userVoucher){
                    $userVoucher->used_at = Carbon::now();
                    $userVoucher->status = 20;
                    $userVoucher->total_used += 1;
                    $userVoucher->total_left -= 1;
                    $userVoucher->save();
                }else{
                    if( $voucher->points_required > 0 ){
                        WalletService::transact( $userWallet, [
                            'amount' => -$voucher->points_required,
                            'remark' => 'Claim Voucher',
                            'type' => 2,
                            'transaction_type' => 11,
                        ] );
                    }

                    UserVoucher::create([
                        'user_id' => $user->id,
                        'voucher_id' => $voucher->id,
                        'expired_date' => Carbon::now()->addDays($voucher->validity_days),
                        'status' => 20,
                        'redeem_from' => 1,
                        'total_left' => 0,
                        'used_at' => Carbon::now(),
                        'secret_code' => strtoupper( \Str::random( 8 ) ),
                    ]);

                    $voucher->total_claimable -= 1;
                    $voucher->save();
                }

            }

            $order->load( ['orderMetas'] );

            if( $bundle ){

                $orderMetas = $order->orderMetas;

                $totalCartDeduction = CartService::calculateBundleCharges( $orderMetas );

                $orderPrice = $bundle->price + $totalCartDeduction;
                $order->subtotal = $orderPrice;
            }

            if( $userBundle ){

                $orderMetas = $order->orderMetas;

                $totalCartDeduction = CartService::calculateBundleCharges( $orderMetas );

                $orderPrice = 0;
                $orderPrice += $totalCartDeduction;
                $order->subtotal = $orderPrice;

            }

            $order->total_price = Helper::numberFormatV2($orderPrice,2,false,true);
            $order->tax = $taxSettings ? (Helper::numberFormatV2(($taxSettings->option_value/100),2) * Helper::numberFormatV2($order->total_price,2)) : 0;
            $order->total_price += Helper::numberFormatV2($order->tax,2,false,true);

            $userCart->status = 20;
            $userCart->save();

            if( $request->payment_method == 1 ){
                WalletService::transact( $userWallet, [
                    'amount' => -$order->total_price,
                    'remark' => 'Order Placed: ' . $order->reference,
                    'type' => $userWallet->type,
                    'transaction_type' => 10,
                ] );
                $order->status = 3;

                // assign purchasing bonus
                $spendingBonus = Option::getSpendingSettings();
                if( $spendingBonus ){

                    $userBonusWallet = $user->wallets->where( 'type', 2 )->first();

                    WalletService::transact( $userBonusWallet, [
                        'amount' => $order->total_price * $spendingBonus->option_value,
                        'remark' => 'Purchasing Bonus',
                        'type' => 2,
                        'transaction_type' => 24,
                    ] );
                }

                // assign referral's purchasing bonus
                $referralSpendingBonus = Option::getReferralSpendingSettings();
                if( $user->referral && $referralSpendingBonus){

                    $referralWallet = $user->referral->wallets->where('type',2)->first();

                    if($referralWallet){
                        WalletService::transact( $referralWallet, [
                            'amount' => $order->total_price * $referralSpendingBonus->option_value,
                            'remark' => 'Referral Purchasing Bonus',
                            'type' => $referralWallet->type,
                            'transaction_type' => 22,
                        ] );
                    }
                    
                }

                // create bundle
                if( $order->product_bundle_id ){
                    $bundleMetas = $bundle->productBundleMetas;

                    $bundleCupLeft = [];
                    $orderMetas = $order->orderMetas;
                    foreach($bundleMetas as $key => $bundleMeta){
                        $bundleCupLeft[intval($bundleMeta->product_id)] = $bundleMeta->quantity - $orderMetas->where('product_id',$bundleMeta->product_id)->count();
                    }
                            
                    $userBundle = UserBundle::create([
                        'user_id' => $user->id,
                        'product_bundle_id' => $bundle->id,
                        'status' => $request->payment_method == 1 ? 10 : 20,
                        'total_cups' => $bundle->productBundleMetas->sum('quantity'),
                        'cups_left' => $bundle->productBundleMetas->sum('quantity') - count( $order->orderMetas ),
                        'cups_left_metas' => json_encode( $bundleCupLeft ),
                        'last_used' => Carbon::now(),
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

                    $order->user_bundle_id = $userBundle->id;
                    $order->save();

                }

                // update stock
                VendingMachineStockService::updateVendingMachineStock( $order->vending_machine_id, $order->orderMetas );

                // notification
                UserService::createUserNotification(
                    $order->user->id,
                    'notification.user_order_success',
                    'notification.user_order_success_content',
                    'order',
                    'order'
                );

                self::sendNotification( $order->user, 'order', __( 'notification.user_order_success_content' )  );
                
                if( $order->userBundle ){
                    $order->status = 3;
                }

            }else {
                
                $data = [
                    'TransactionType' => 'SALE',
                    'PymtMethod' => 'ANY',
                    'ServiceID' => config('services.eghl.merchant_id'),
                    'PaymentID' => $order->reference . '-' . $order->payment_attempt,
                    'OrderNumber' => $order->reference,
                    'PaymentDesc' => $order->reference,
                    'MerchantName' => 'Yobe Froyo',
                    'MerchantReturnURL' => config('services.eghl.staging_callabck_url'),
                    'MerchantApprovalURL' => config('services.eghl.staging_success_url'),
                    'MerchantUnApprovalURL' => config('services.eghl.staging_failed_url'),
                    'MerchantCallbacklURL' => config('services.eghl.staging_fallback_url'),
                    'MerchantCallbacklURL' => config('services.eghl.staging_fallback_url'),
                    'Amount' => Helper::numberFormatV2($order->total_price, 2),
                    'CurrencyCode' => 'MYR',
                    'CustIP' => request()->ip(),
                    'CustName' => $order->user->username ?? 'Yobe Guest',
                    'HashValue' => '',
                    'CustEmail' => $order->user->email ?? 'yobeguest@gmail.com',
                    'CustPhone' => $order->user->phone_number,
                    'MerchantTermsURL' => null,
                    'LanguageCode' => 'en',
                    'PageTimeout' => '780',
                ];

                $data['HashValue'] = Helper::generatePaymentHash($data);
                $url2 = config('services.eghl.test_url') . '?' . http_build_query($data);
                
                $orderTransaction = OrderTransaction::create( [
                    'order_id' => $order->id,
                    'checkout_id' => null,
                    'checkout_url' => null,
                    'payment_url' => $url2,
                    'transaction_id' => null,
                    'layout_version' => 'v1',
                    'redirect_url' => null,
                    'notify_url' => null,
                    'order_no' => $order->reference . '-' . $order->payment_attempt,
                    'order_title' => $order->reference,
                    'order_detail' => $order->reference,
                    'amount' => $order->total_price,
                    'currency' => 'MYR',
                    'transaction_type' => 1,
                    'status' => 10,
                ] );

                $order->payment_url = $url2;
                $order->order_transaction_id = $orderTransaction->id;

                if( $order->userBundle && $order->total_price == 0 ){
                    $order->status = 3;
                    $order->payment_url = null;
                }
            }

            $order->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        $orderMetas = $order->orderMetas->map(function ($meta) {
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
            'message_key' => $order->userBundle ? 'bundle redeemed success' : 'create_order_success',
            'payment_url' => $order->payment_url,
            'sesion_key' => $order->session_key,
            'order_id' => $order->id,
            'vending_machine' => $order->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $order->vendingMachine->operational_hour),
            'total' => Helper::numberFormatV2($order->total_price , 2 ,true),
            'order_metas' => $orderMetas,
            'voucher' => $order->voucher ? $order->voucher->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
            'bundle' => $order->productBundle ? $order->productBundle->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
            'user_bundle' => $order->userBundle ? $order->userBundle->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
        ] );
    }

    public static function scannedOrder( $request ) {
        
        DB::beginTransaction();

        try {

            $updateOrder = Order::with( [
                'orderMetas','vendingMachine','user'
            ] )->where( 'reference', $request->reference )
            ->whereNotIn('status', [10, 20])->first();

            if( !$updateOrder ){
                return response()->json( [
                    'errors' => [
                        'message' => 'Order Not found',
                        'message_key' => 'scan order failed',
                    ]
                ], 500 );
            }

            if( $updateOrder ){
                if( $updateOrder->status == 1 ){
                    return response()->json( [
                        'message' => 'Unpaid Order',
                        'message_key' => 'scan order failed',
                    ], 500 );
                }
                $updateOrder->status = 10;
                $updateOrder->save();
                DB::commit();
                return response()->json( [
                    
                    'errors' => [
                        'message' => 'Order Pickep Up',
                        'message_key' => 'scan order success',
                    ]
                ] );
            }

            $updateOrder = $updateOrder->paginate(10);
    
            // Modify each order and its related data
            $updateOrder->getCollection()->transform(function ($order) {
                $order->vendingMachine->makeHidden(['created_at', 'updated_at', 'status'])
                    ->setAttribute('operational_hour', $order->vendingMachine->operational_hour)
                    ->setAttribute('image_path', $order->vendingMachine->image_path);
        
                $orderMetas = $order->orderMetas->map(function ($meta) {
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
        
                $order->orderMetas = $orderMetas;

                return $order;
            });
        
            // Return the paginated response
            return response()->json([
                'message' => '',
                'message_key' => 'get_order_success',
                'orders' => $updateOrder,
            ]);

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_froyo_failed',
            ], 500 );
        }
    }

    public static function validateVoucher( $request ){
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
        
        // check is user able to claim this
        // $userVoucher = UserVoucher::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->where('status',10)->first();
        // if(!$userVoucher){
        //     $userPoints = $user->wallets->where( 'type', 2 )->first();

        //     if ( $userPoints->balance < $voucher->points_required ) {
    
        //         return response()->json( [
        //             'required_amount' => $voucher->points_required,
        //             'message' => 'Mininum of ' . $voucher->points_required . ' points is required to claim this voucher',
        //             'errors' => 'voucher',
        //         ], 422 );
    
        //     }
        // }

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
            
            $y = $cart->cartMetas->whereIn( 'product_id', $adjustment->get_product )->count();

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
                    'message' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id', $adjustment->buy_products[0] )->value( 'title' ) ] ),
                    'message_key' => 'voucher.min_quantity_of_y',
                        'errors' => [
                            'voucher' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->get_quantity . ' ' . Product::where( 'id', $adjustment->buy_products[0] )->value( 'title' ) ] )
                        ]
                ], 422 );
            }

        } else {

            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );

            if ( $cart->total_price < $adjustment->buy_quantity ) {
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

    public static function retryPayment( $request ) {

        $validator = Validator::make($request->all(), [
            'order_id' => ['required', 'exists:orders,id'],
        ]);
        
        $validator->validate();

        $order = Order::where('id', $request->order_id)
            ->where('status', '!=', 3)
            ->where('user_id', auth()->user()->id )
            ->first();

        if (!$order) {
            return response()->json([
                'message' => '',
                'message_key' => 'order_not_available',
                'errors' => [
                    'order' => 'order not available'
                ]
            ], 422);
        }

        DB::beginTransaction();
        try {
                
            $data = [
                'TransactionType' => 'SALE',
                'PymtMethod' => 'ANY',
                'ServiceID' => config('services.eghl.merchant_id'),
                'PaymentID' => $order->reference . '-' . $order->payment_attempt,
                'OrderNumber' => $order->reference,
                'PaymentDesc' => $order->reference,
                'MerchantName' => 'Yobe Froyo',
                'MerchantReturnURL' => config('services.eghl.staging_callabck_url'),
                'MerchantApprovalURL' => config('services.eghl.staging_success_url'),
                'MerchantUnApprovalURL' => config('services.eghl.staging_failed_url'),
                'MerchantCallbacklURL' => config('services.eghl.staging_fallback_url'),
                'Amount' => Helper::numberFormatV2($order->total_price, 2),
                'CurrencyCode' => 'MYR',
                'CustIP' => request()->ip(),
                'CustName' => $order->user->username ?? 'Yobe Guest',
                'HashValue' => '',
                'CustEmail' => $order->user->email ?? 'yobeguest@gmail.com',
                'CustPhone' => $order->user->phone_number,
                'MerchantTermsURL' => null,
                'LanguageCode' => 'en',
                'PageTimeout' => '780',
            ];

            $data['HashValue'] = Helper::generatePaymentHash($data);
            $url2 = config('services.eghl.test_url') . '?' . http_build_query($data);
            
            $orderTransaction = OrderTransaction::create( [
                'order_id' => $order->id,
                'checkout_id' => null,
                'checkout_url' => null,
                'payment_url' => $url2,
                'transaction_id' => null,
                'layout_version' => 'v1',
                'redirect_url' => null,
                'notify_url' => null,
                'order_no' => $order->reference . '-' . $order->payment_attempt,
                'order_title' => $order->reference,
                'order_detail' => $order->reference,
                'amount' => $order->total_price,
                'currency' => 'MYR',
                'transaction_type' => 1,
                'status' => 10,
            ] );

            $order->payment_url = $url2;
            $order->order_transaction_id = $orderTransaction->id;
            $order->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        $orderMetas = $order->orderMetas->map(function ($meta) {
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
            'message_key' => 'retry_payment_inititate',
            'payment_url' => $order->payment_url,
            'sesion_key' => $order->session_key,
            'order_id' => $order->id,
            'vending_machine' => $order->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $order->vendingMachine->operational_hour),
            'total' => Helper::numberFormatV2($order->total_price , 2 ,true),
            'order_metas' => $orderMetas
        ] );
    }

    public static function updateOrderStatusOperation( $request ) {

        $validator = Validator::make($request->all(), [
            'reference' => [
                'required',
                function ($attribute, $value, $fail) {
                    $order = Order::where('reference', $value)->first();
        
                    if (!$order) {
                        return $fail('The selected reference does not exist.');
                    }
        
                    if ($order->status == 1) {
                        return $fail('Unpaid order detected. Please complete payment.');
                    }
        
                    if (in_array($order->status, [10, 20])) {
                        return $fail('Order Completed.');
                    }
                },
            ],
        ]);
        

        $validator->validate();

        DB::beginTransaction();

        try {

            $updateOrder = Order::with( [
                'orderMetas','vendingMachine','user'
            ] )->where( 'reference', $request->reference )
            ->whereNotIn('status', [10, 20])->first();

            $vendingMachine = VendingMachine::where('api_key', $request->header('X-Vending-Machine-Key'))->first();

            if( $updateOrder ){
                if( $updateOrder->status == 1 ){
                    return response()->json([
                        'message' => __('order.unpaid_order'),
                        'message_key' => 'unpaid_order',
                        'errors' => [
                            'order' => [
                                __('order.unpaid_order_message'),
                            ]
                        ]
                    ], 422);
                }
                $updateOrder->status = 10;
                $updateOrder->vending_machine_id = $vendingMachine->id;
                $updateOrder->save();

                if( $updateOrder->orderMetas ) {
                    $orderMetas = $updateOrder->orderMetas;

                    foreach ($orderMetas as $orderMeta) {

                        if( $orderMeta->syrups ){
                            $decodedItems = json_decode($orderMeta->syrups, true);
                            $stockData = ['syrups' => []];
                            
                            foreach ($decodedItems as $decodedItem) {
                                $stockData['syrups'][$decodedItem] = 1;
                            }

                            VendingMachineService::processStockUpdates($updateOrder->vending_machine_id, $stockData, 'syrups', 1);
                        }

                        if( $orderMeta->toppings ){
                            $decodedItems = json_decode($orderMeta->toppings, true);
                            $stockData = ['toppings' => []];
                            
                            foreach ($decodedItems as $decodedItem) {
                                $stockData['toppings'][$decodedItem] = 1;
                            }

                            VendingMachineService::processStockUpdates($updateOrder->vending_machine_id, $stockData, 'toppings', 1);
                        }

                        if( $orderMeta->froyos ){
                            $decodedItems = json_decode($orderMeta->froyos, true);
                            $stockData = ['froyos' => []];
                            
                            foreach ($decodedItems as $decodedItem) {
                                $stockData['froyos'][$decodedItem] = 1;
                            }

                            VendingMachineService::processStockUpdates($updateOrder->vending_machine_id, $stockData, 'froyos', 1);
                        }
                    }
                }

                DB::commit();
            }
    
            $transformedOrder = collect([$updateOrder])->map(function ($order) {
                $order->vendingMachine?->makeHidden(['created_at', 'updated_at', 'status'])
                    ->setAttribute('operational_hour', $order->vendingMachine?->operational_hour)
                    ->setAttribute('image_path', $order->vendingMachine?->image_path);
        
                $order->orderMetas = $order->orderMetas->map(function ($meta) {
                    return [
                        'id' => $meta->id,
                        'subtotal' => $meta->total_price,
                        'product' => $meta->product?->makeHidden(['created_at', 'updated_at', 'status'])
                            ->setAttribute('image_path', $meta->product?->image_path),
                        'froyo' => $meta->froyos_metas,
                        'syrup' => $meta->syrups_metas,
                        'topping' => $meta->toppings_metas,
                    ];
                });

                $order->order_metas = $order->orderMetas;
                $order->orderMetas = null;
                unset($order->orderMetas);
        
                return $order;
            });
        
            // Return the paginated response
            return response()->json([
                'message' => '',
                'message_key' => 'update_order_success',
                'orders' => $transformedOrder,
            ]);

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_order_failed',
            ], 500 );
        }
    }

    public static function createMachineOrder( $request ) {

        $validator = Validator::make($request->all(), [
            'reference' => [
                'required'
            ],
            'payment_method' => [ 'nullable', 'in:1,2,3' ],
            'user_bundle' => [ 'nullable', 'exists:user_bundles,id'  ],
            'total_price' => ['nullable', 'numeric'],
            'discount' => ['nullable', 'numeric'],
            'tax' => ['nullable', 'numeric'],
            'items' => ['nullable', 'array'],
            'items.*.product' => ['required', 'exists:products,id',function ($attribute, $value, $fail) {
                $exists = Product::where( 'id', $value )->where( 'status', 10 )->first();

                if (!$exists) {
                    $fail(__('Product is not available'));
                }
            }],
            'items.*.froyo' => ['nullable', 'array'],
            'items.*.froyo.*' => ['exists:froyos,id',function ($attribute, $value, $fail) {
                $exists = Froyo::where( 'id', $value )->where( 'status', 10 )->first();

                if (!$exists) {
                    $fail(__('Froyo is not available'));
                }
            }], // Validate each froyo ID
            'items.*.syrup' => ['nullable', 'array'],
            'items.*.syrup.*' => ['exists:syrups,id',function ($attribute, $value, $fail) {
                $exists = Syrup::where( 'id', $value )->where( 'status', 10 )->first();

                if (!$exists) {
                    $fail(__('Syrup is not available'));
                }
            }], // Validate each syrup ID
            'items.*.topping' => ['nullable', 'array'],
            'items.*.topping.*' => ['exists:toppings,id',function ($attribute, $value, $fail) {
                $exists = Topping::where( 'id', $value )->where( 'status', 10 )->first();

                if (!$exists) {
                    $fail(__('Topping is not available'));
                }
            }], // Validate each topping ID
        ]);
        

        $validator->validate();

        DB::beginTransaction();

        try {

            $vendingMachine = VendingMachine::where('api_key', $request->header('X-Vending-Machine-Key'))->first();

            $orderPrice = 0;

            $createOrder = Order::create( [
                'product_id' => null,
                'product_bundle_id' => null,
                'outlet_id' => null,
                'user_id' => null,
                'vending_machine_id' => $vendingMachine->id,
                'total_price' => 0,
                'discount' => 0,
                'reference' => Helper::generateOrderReference(),
                'payment_method' => 1,
                'status' => 3,
                'machine_reference' => $request->reference,
                'order_type' => 2,
                'machine_total_price' => $request->total_price,
                'machine_discount' => $request->discount,
                'machine_tax' => $request->tax,
                'machine_payment_method' => $request->payment_method,
            ] );

            OrderTransactionLog::create( [
                'order_id' => $createOrder->id,
                'orders_metas' => json_encode( $request->all() ),
                'status' => 10,
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
    
                    $orderMeta = OrderMeta::create( [
                        'order_id' => $createOrder->id,
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
            $createOrder->load('orderMetas');
            $createOrder->subtotal = $orderPrice;
            $taxSettings = Option::getTaxesSettings();
            $createOrder->total_price = Helper::numberFormatV2($orderPrice,2,false,true);
            $createOrder->tax = $taxSettings ? (Helper::numberFormatV2(($taxSettings->option_value/100),2) * Helper::numberFormatV2($createOrder->total_price,2)) : 0;
            $createOrder->total_price += Helper::numberFormatV2($createOrder->tax,2,false,true);

            $createOrder->save();
    
            $transformedOrder = collect([$createOrder])->map(function ($order) {
                $order->vendingMachine?->makeHidden(['created_at', 'updated_at', 'status'])
                    ->setAttribute('operational_hour', $order->vendingMachine?->operational_hour)
                    ->setAttribute('image_path', $order->vendingMachine?->image_path);
        
                $order->orderMetas = $order->orderMetas->map(function ($meta) {
                    return [
                        'id' => $meta->id,
                        'subtotal' => $meta->total_price,
                        'product' => $meta->product?->makeHidden(['created_at', 'updated_at', 'status'])
                            ->setAttribute('image_path', $meta->product?->image_path),
                        'froyo' => $meta->froyos_metas,
                        'syrup' => $meta->syrups_metas,
                        'topping' => $meta->toppings_metas,
                    ];
                });

                $order->order_metas = $order->orderMetas;
                $order->orderMetas = null;
                unset($order->orderMetas);
        
                return $order;
            });

            DB::commit();
        
            // Return the paginated response
            return response()->json([
                'message' => 'Create Order Success',
                'message_key' => 'create_order_success',
                'orders' => $transformedOrder,
            ]);

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_order_failed',
            ], 500 );
        }
    }

    public static function updateSalesData($request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'sales_date' => ['required', 'date_format:Y-m-d'],
            'sales_type' => ['nullable', 'integer'],
            'sales_metas' => ['nullable', 'array'],
            'order_references' => ['nullable', 'array'], // Validate order references
            'order_references.*' => ['string', 'exists:orders,reference'], // Ensure references exist in orders table
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {

            $vendingMachine = VendingMachine::where('api_key', $request->header('X-Vending-Machine-Key'))->first();

            $totalSales = 0;
            $totalRevenue = 0;
            $voucherMetas = [];
            $bundleMetas = [];

            foreach( $request->order_references as $orderReference ){
                $order = Order::where( 'reference', $orderReference )
                ->where('status', 10)->first();

                if( $order ){
                    $totalSales += $order->subtotal;
                    $totalRevenue += $order->total_price;

                    if( $order->user_bundle_id ){
                        array_push( $bundleMetas, $order->userBundle->productBundle->code );
                    }

                    if( $order->voucher_id ){
                        array_push( $voucherMetas, $order->voucher->code );
                    }
                }

            }

            $machineSales = MachineSalesData::create([
                'vending_machine_id' => $vendingMachine->vending_machine_id,
                'sales_date' => $request->sales_date,
                'sales_type' => $request->sales_type ?? 1, // Default to 1
                'sales_metas' => json_encode($request->sales_metas ?? []),
                'orders_metas' => json_encode($request->order_references ?? []), // Store order references
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
                'bundle_metas' => json_encode($bundleMetas),
                'voucher_metas' => json_encode($voucherMetas),
                'status' => 10, // Default status
            ]);

            DB::commit();

            return response()->json([
                'data' => [
                    'machine_sales' => $machineSales,
                    'message_key' => 'store_machine_sales_success',
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'store_machine_sales_failed',
            ], 500);
        }
    }

    private static function sendNotification( $user, $key, $message ) {

        $messageContent = array();

        $messageContent['key'] = $key;
        $messageContent['id'] = $user->id;
        $messageContent['message'] = $message;

        Helper::sendNotification( $user->user_id, $messageContent );
        
    }

}