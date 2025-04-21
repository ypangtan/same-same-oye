<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
    Mail,
};

use App\Mail\InvoiceMail;

use Helper;

use App\Models\{
    Company,
    Customer,
    Administrator,
    Invoice,
    InvoiceMeta,
    Booking,
    FileManager,
    Product,
    DeliveryOrder,
    DeliveryOrderMeta,
    ProductVariant,
    TaxMethod,
    Bundle,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InvoiceService
{

    public static function createInvoice( $request ) {

        $validator = Validator::make( $request->all(), [
            'remarks' => [ 'nullable' ],
            'attachment' => [ 'nullable' ],
            'products' => [ 'nullable' ],
            'warehouse' => [ 'nullable', 'exists:warehouses,id' ],
            'products.*.id' => [ 'nullable',  function ($attribute, $value, $fail) {
                    if (!preg_match('/^(product|bundle|variant)-(\d+)$/', $value, $matches)) {
                        return $fail("The {$attribute} format is invalid.");
                    }
        
                    $type = $matches[1];
                    $identifier = $matches[2];
        
                    // Check if the identifier exists in the corresponding table
                    if ($type === 'product' && !\DB::table('products')->where('id', $identifier)->exists()) {
                        return $fail("The {$attribute} does not exist in products.");
                    } elseif ($type === 'bundle' && !\DB::table('bundles')->where('id', $identifier)->exists()) {
                        return $fail("The {$attribute} does not exist in bundles.");
                    } elseif ($type === 'variant' && !\DB::table('product_variants')->where('id', $identifier)->exists()) {
                        return $fail("The {$attribute} does not exist in bundles.");
                    }
                },
            ],
            'products.*.quantity' => [
                'nullable',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1]; // Extract the product index
                    $productId = $request->input("products.{$index}.id");

                    if (!preg_match('/^(product|bundle|variant)-(\d+)$/', $productId, $matches)) {
                        return $fail("The ID format is invalid for {$attribute}.");
                    }

                    $type = $matches[1];
                    $identifier = $matches[2];

                    // Validate the quantity against the stock in the respective warehouse table
                    $availableStock = 0;

                    if ($type === 'product') {
                        $availableStock = \DB::table('warehouses_products')
                            ->where('warehouse_id', $request->warehouse)
                            ->where('product_id', $identifier)
                            ->sum('quantity');

                        $name = 'Product: ' . Product::find($identifier)->value('title');

                    } elseif ($type === 'bundle') {
                        $availableStock = \DB::table('warehouses_bundles')
                            ->where('warehouse_id', $request->warehouse)
                            ->where('bundle_id', $identifier)
                            ->sum('quantity');

                        $name = 'Bundle: ' . Bundle::find($identifier)->value('title');

                    } elseif ($type === 'variant') {
                        $availableStock = \DB::table('warehouses_variants')
                            ->where('warehouse_id', $request->warehouse)
                            ->where('variant_id', $identifier)
                            ->sum('quantity');

                        $name = 'Variant: ' . ProductVariant::find($identifier)->value('title');

                    }

                    if ($value > $availableStock) {
                        return $fail(" The requested quantity for {$name} exceeds available stock ({$availableStock} stocks available).");
                    }
                },
            ],
            'supplier' => [ 'nullable', 'exists:suppliers,id' ],
            'salesman' => [ 'nullable', 'exists:administrators,id' ],
            'customer' => [ 'nullable', 'exists:users,id' ],
            'discount' => [ 'nullable', 'numeric' ,'min:0' ],
            'shipping_cost' => [ 'nullable', 'numeric' ,'min:0' ],
            'tax_method' => [ 'nullable', 'exists:tax_methods,id' ],

        ] );

        $attributeName = [
            'title' => __( 'invoice.title' ),
            'description' => __( 'invoice.description' ),
            'image' => __( 'invoice.image' ),
            'thumbnail' => __( 'invoice.thumbnail' ),
            'url_slug' => __( 'invoice.url_slug' ),
            'structure' => __( 'invoice.structure' ),
            'size' => __( 'invoice.size' ),
            'phone_number' => __( 'invoice.phone_number' ),
            'sort' => __( 'invoice.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $amount = 0;
            $originalAmount = 0;
            $paidAmount = 0;
            $finalAmount = 0;
            $taxAmount = 0;

            if( $request->products ) {

                $products = $request->products;

                foreach( $products as $product ){

                    preg_match('/^(product|bundle|variant)-(\d+)$/', $product['id'], $matches);

                    $type = $matches[1];
                    $identifier = $matches[2];

                    switch ($type) {
                        case 'product':
                            $productData = Product::find( $identifier );
                            // turnoff warehouse price
                            if( count( $productData->warehouses ) > 0 ){
                                $warehouseProduct = $productData->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                                $amount += $warehouseProduct->pivot->price > 0 ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->price * $product['quantity'];
                            } else {
                                $amount += $productData->price * $product['quantity'];
                            }
                            break;

                        case 'variant':
                            $productData = ProductVariant::find( $identifier );
                            // turnoff warehouse price
                            if( count( $productData->product->warehouses ) > 0 ){
                                $warehouseProduct = $productData->product->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                                $amount += $warehouseProduct->pivot->price > 0 ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->product->price * $product['quantity'];
                            } else {
                                $amount += $productData->product->price * $product['quantity'];
                            }
                            break;

                        case 'bundle':
                            $productData = Bundle::find( $identifier );
                            // turnoff warehouse price
                            $amount += $productData->price * $product['quantity'];
                            break;
                        
                        default:
                            $productData = Product::find( $identifier );
                            // turnoff warehouse price
                            if( count( $productData->warehouses ) > 0 ){
                                $warehouseProduct = $productData->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                                $amount += $warehouseProduct->pivot->price > 0 ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->price * $product['quantity'];
                            } else {
                                $amount += $productData->price * $product['quantity'];
                            }
                            break;
                    }

                }
            }

            // $taxAmount = $amount * Helper::taxTypes()[$request->tax_type ?? 1]['percentage'];\
            $taxAmount = $amount * TaxMethod::find( $request->tax_method )->formatted_percentage;
            $finalAmount = $amount - $request->discount + $taxAmount;

            $invoiceCreate = Invoice::create([
                'supplier_id' => $request->supplier,
                'warehouse_id' => $request->warehouse,
                'salesman_id' => $request->salesman,
                'customer_id' => $request->customer,
                'remarks' => $request->remarks,
                'reference' => Helper::generateInvoiceNumber(),
                'tax_type' => 1,
                // 'tax_method_id' => $request->tax_method,
                'amount' => $amount,
                'original_amount' => $amount,
                'paid_amount' => $paidAmount,
                'final_amount' => $amount,
                'order_tax' => $taxAmount,
                'order_discount' => $request->discount,
                'shipping_cost' => $request->shipping_cost,
                'tax_method_id' => $request->tax_method,
                'status' => 10,
            ]);

            $attachment = explode( ',', $request->attachment );
            $attachmentFiles = FileManager::whereIn( 'id', $attachment )->get();

            if ( $attachmentFiles ) {
                foreach ( $attachmentFiles as $attachmentFile ) {

                    $fileName = explode( '/', $attachmentFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'quotation/' . $invoiceCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $invoiceCreate->attachment = $target;
                   $invoiceCreate->save();

                    $attachmentFile->status = 10;
                    $attachmentFile->save();

                }
            }

            $products = $request->products;

            if( $products ){
                foreach( $products as $product ){

                    preg_match('/^(product|bundle|variant)-(\d+)$/', $product['id'], $matches);

                    $type = $matches[1];
                    $identifier = $matches[2];

                    switch ($type) {
                        case 'product':
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, $product['id'], $product['quantity'], true  );
                            break;

                        case 'variant':
                            $productVariant = ProductVariant::find( $identifier );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $productVariant->product_id, $identifier, $product['quantity'], true  );
                            break;

                        case 'bundle':
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, $product['id'], $product['quantity'], true  );
                            break;
                        
                        default:
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, $product['id'], $product['quantity'], true  );
                            break;
                    }

                    $invoiceMetaCreate = InvoiceMeta::create([
                        'invoice_id' => $invoiceCreate->id,
                        'product_id' => $type == 'product' ? $identifier : null,
                        'variant_id' => $type == 'variant' ? $identifier :null,
                        'bundle_id' => $type == 'bundle' ? $identifier :null,
                        'quantity' => $product['quantity'],
                        'tax_method_id' => $request->tax_method,
                        'status' => 10,
                    ]);

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.invoices' ) ) ] ),
        ] );
    }
    
    public static function updateInvoice( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'remarks' => [ 'nullable' ],
            'attachment' => [ 'nullable' ],
            'warehouse' => [ 'nullable', 'exists:warehouses,id' ],
            'products' => [ 'nullable' ],
            'products.*.id' => [ 'nullable',  function ($attribute, $value, $fail) {
                    if (!preg_match('/^(product|bundle|variant)-(\d+)$/', $value, $matches)) {
                        return $fail("The {$attribute} format is invalid.");
                    }
        
                    $type = $matches[1];
                    $identifier = $matches[2];
        
                    // Check if the identifier exists in the corresponding table
                    if ($type === 'product' && !\DB::table('products')->where('id', $identifier)->exists()) {
                        return $fail("The {$attribute} does not exist in products.");
                    } elseif ($type === 'bundle' && !\DB::table('bundles')->where('id', $identifier)->exists()) {
                        return $fail("The {$attribute} does not exist in bundles.");
                    } elseif ($type === 'variant' && !\DB::table('product_variants')->where('id', $identifier)->exists()) {
                        return $fail("The {$attribute} does not exist in bundles.");
                    }
                },
            ],
            'products.*.quantity' => [
                'nullable',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1]; // Extract the product index
                    $productId = $request->input("products.{$index}.id");

                    if (!preg_match('/^(product|bundle|variant)-(\d+)$/', $productId, $matches)) {
                        return $fail("The ID format is invalid for {$attribute}.");
                    }

                    $type = $matches[1];
                    $identifier = $matches[2];

                    // Validate the quantity against the stock in the respective warehouse table
                    $availableStock = 0;

                    if ($type === 'product') {
                        $availableStock = \DB::table('warehouses_products')
                            ->where('warehouse_id', $request->warehouse)
                            ->where('product_id', $identifier)
                            ->sum('quantity');

                        $name = 'Product: ' . Product::find($identifier)->value('title');

                    } elseif ($type === 'bundle') {
                        $availableStock = \DB::table('warehouses_bundles')
                            ->where('warehouse_id', $request->warehouse)
                            ->where('bundle_id', $identifier)
                            ->sum('quantity');

                        $name = 'Bundle: ' . Bundle::find($identifier)->value('title');

                    } elseif ($type === 'variant') {
                        $availableStock = \DB::table('warehouses_variants')
                            ->where('warehouse_id', $request->warehouse)
                            ->where('variant_id', $identifier)
                            ->sum('quantity');

                        $name = 'Variant: ' . ProductVariant::find($identifier)->value('title');

                    }

                    if ($value > $availableStock) {
                        return $fail(" The requested quantity for {$name} exceeds available stock ({$availableStock} stocks available).");
                    }
                },
            ],
            'supplier' => [ 'nullable', 'exists:suppliers,id' ],
            'salesman' => [ 'nullable', 'exists:administrators,id' ],
            'customer' => [ 'nullable', 'exists:users,id' ],
            'discount' => [ 'nullable', 'numeric' ,'min:0' ],
            'shipping_cost' => [ 'nullable', 'numeric' ,'min:0' ],
            'tax_method' => [ 'nullable', 'exists:tax_methods,id' ],
        ] );

        $attributeName = [
            'title' => __( 'invoice.title' ),
            'description' => __( 'invoice.description' ),
            'image' => __( 'invoice.image' ),
            'thumbnail' => __( 'invoice.thumbnail' ),
            'url_slug' => __( 'invoice.url_slug' ),
            'structure' => __( 'invoice.structure' ),
            'size' => __( 'invoice.size' ),
            'phone_number' => __( 'invoice.phone_number' ),
            'sort' => __( 'invoice.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $amount = 0;
            $originalAmount = 0;
            $paidAmount = 0;
            $finalAmount = 0;
            $taxAmount = 0;

            if( $request->products ) {

                $products = $request->products;

                foreach( $products as $product ){
                    preg_match('/^(product|bundle|variant)-(\d+)$/', $product['id'], $matches);

                    $type = $matches[1];
                    $identifier = $matches[2];

                    switch ($type) {
                        case 'product':
                            $productData = Product::find( $identifier );
                            // turnoff warehouse price
                            if( count( $productData->warehouses ) > 0 ){
                                $warehouseProduct = $productData->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                                $amount += $warehouseProduct->pivot->price > 0 ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->price * $product['quantity'];
                            } else {
                                $amount += $productData->price * $product['quantity'];
                            }
                            break;

                        case 'variant':
                            $productData = ProductVariant::find( $identifier );
                            // turnoff warehouse price
                            if( count( $productData->product->warehouses ) > 0 ){
                                $warehouseProduct = $productData->product->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                                $amount += $warehouseProduct->pivot->price > 0 ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->product->price * $product['quantity'];
                            } else {
                                $amount += $productData->product->price * $product['quantity'];
                            }
                            break;

                        case 'bundle':
                            $productData = Bundle::find( $identifier );
                            // turnoff warehouse price
                            $amount += $productData->price * $product['quantity'];
                            break;
                        
                        default:
                            $productData = Product::find( $identifier );
                            // turnoff warehouse price
                            if( count( $productData->warehouses ) > 0 ){
                                $warehouseProduct = $productData->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                                $amount += $warehouseProduct->pivot->price > 0 ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->price * $product['quantity'];
                            } else {
                                $amount += $productData->price * $product['quantity'];
                            }
                            break;
                    }

                }
            }
            
            $taxAmount = $amount * Helper::taxTypes()[$request->tax_type ?? 1]['percentage'];
            $finalAmount = $amount - $request->discount + $taxAmount;

            $updateInvoice = Invoice::find( $request->id );

            $updateInvoice->remarks = $request->remarks ?? $updateInvoice->remarks;
            $updateInvoice->warehouse_id = $request->warehouse ?? $updateInvoice->warehouse_id;
            $updateInvoice->salesman_id = $request->salesman ?? $updateInvoice->salesman_id;
            $updateInvoice->customer_id = $request->customer ?? $updateInvoice->customer_id;
            $updateInvoice->tax_type = $request->tax_type ?? 1 ?? $updateInvoice->tax_type;
            $updateInvoice->amount = $amount;
            $updateInvoice->original_amount = $amount;
            $updateInvoice->paid_amount = $paidAmount;
            $updateInvoice->final_amount = $amount;
            $updateInvoice->order_tax = $taxAmount;
            $updateInvoice->order_discount = $request->discount;
            $updateInvoice->shipping_cost = $request->shipping_cost;
            $updateInvoice->tax_method_id = $request->tax_method;

            $attachment = explode( ',', $request->attachment );

            $attachmentFiles = FileManager::whereIn( 'id', $attachment )->get();

            if ( $attachmentFiles ) {
                foreach ( $attachmentFiles as $attachmentFile ) {

                    $fileName = explode( '/', $attachmentFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'quotation/' . $updateInvoice->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $updateInvoice->attachment = $target;
                   $updateInvoice->save();

                    $attachmentFile->status = 10;
                    $attachmentFile->save();

                }
            }

            $oldInvoiceMetas = $updateInvoice->invoiceMetas;
            $oldInvoiceMetasArray = $oldInvoiceMetas->pluck('id')->toArray();
            $products = $request->products;

            if( $products ) {

                $incomingProductIds = array_column($products, 'metaId');
    
                $incomingProductIds = array_filter($incomingProductIds, function ($id) {
                    return $id !== null && $id !== 'null';
                });

                $idsToDelete = array_diff($oldInvoiceMetasArray, $incomingProductIds);

                foreach( $idsToDelete as $idToDelete ){

                    $invoice = InvoiceMeta::find( $idToDelete );

                    if( $invoice->variant_id ){
                        $prevWarehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $invoice->product_id, $invoice->variant_id, -$invoice->quantity, true );
                    }

                    else if( $invoice->bundle_id ){
                        $prevWarehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'bundle-' . $invoice->product_id, -$invoice->quantity, true );
                    }

                    else{
                        $prevWarehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'product-' . $invoice->product_id, -$invoice->quantity, true );
                    }

                }

                InvoiceMeta::whereIn('id', $idsToDelete)->delete();
                
                foreach( $products as $product ){

                    if( in_array( $product['metaId'], $oldInvoiceMetasArray ) ){

                        $removeInvoiceMeta = InvoiceMeta::find( $product['metaId'] );

                        // Remove previous
                        if( $removeInvoiceMeta->product_id ) {
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'product-'.$removeInvoiceMeta->product_id, -$removeInvoiceMeta->quantity, true  );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'product-'.$removeInvoiceMeta->product_id, $product['quantity'], false );
                        }elseif( $removeInvoiceMeta->variant_id ) {
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $removeInvoiceMeta->product_id, $removeInvoiceMeta->variant_id, -$removeInvoiceMeta->quantity, true  );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $removeInvoiceMeta->product_id, $removeInvoiceMeta->variant_id, $product['quantity'], false  );
                        }else{
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'bundle'.$removeInvoiceMeta->bundle_id, -$removeInvoiceMeta->quantity, true  );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'bundle'.$removeInvoiceMeta->bundle_id, $product['quantity'], false );
                        }
                        
                        $removeInvoiceMeta->invoice_id = $updateInvoice->id;
                        $removeInvoiceMeta->quantity= $product['quantity'];
                        $removeInvoiceMeta->save();

                    } else {

                        if( $product['metaId'] == 'null' ){

                            preg_match('/^(product|bundle|variant)-(\d+)$/', $product['id'], $matches);

                            $type = $matches[1];
                            $identifier = $matches[2];

                            switch ($type) {
                                case 'product':
                                    $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, $product['id'], $product['quantity'], true  );
                                    break;
        
                                case 'variant':
                                    $productVariant = ProductVariant::find( $identifier );
                                    $warehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $productVariant->product_id, $identifier, $product['quantity'], true  );
                                    break;
        
                                case 'bundle':
                                    $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, $product['id'], $product['quantity'], true  );
                                    break;
                                
                                default:
                                    $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, $product['id'], $product['quantity'], true  );
                                    break;
                            }

                            $invoiceMetaCreate = InvoiceMeta::create([
                                'invoice_id' => $updateInvoice->id,
                                'product_id' => $product['id'],
                                'quantity' => $product['quantity'],
                                'product_id' => $type == 'product' ? $identifier : null,
                                'variant_id' => $type == 'variant' ? $identifier :null,
                                'bundle_id' => $type == 'bundle' ? $identifier :null,
                                'tax_method_id' => $request->tax_method,
                                'status' => 10,
                            ]);
                        } else{
                            $removeInvoiceMeta = InvoiceMeta::find( $product['metaId'] );
                            $removeInvoiceMeta->delete();
                        }
                    }
    
                }
            } else {
                foreach ($oldInvoiceMetas as $meta) {
                    $meta->delete();
                }
            }

            $updateInvoice->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.invoices' ) ) ] ),
        ] );
    }

     public static function allInvoices( $request ) {

        $invoices = Invoice::with( [ 'salesOrder', 'salesman', 'customer','warehouse', 'supplier'] )->select( 'invoices.*');

        $filterObject = self::filter( $request, $invoices );
        $invoice = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $invoice->orderBy( 'invoices.created_at', $dir );
                    break;
                case 2:
                    $invoice->orderBy( 'invoices.title', $dir );
                    break;
                case 3:
                    $invoice->orderBy( 'invoices.description', $dir );
                    break;
            }
        }

            $invoiceCount = $invoice->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $invoices = $invoice->skip( $offset )->take( $limit )->get();

            if ( $invoices ) {
                $invoices->append( [
                    'encrypted_id',
                    'attachment_path',
                ] );
            }

            $totalRecord = Invoice::count();

            $data = [
                'invoices' => $invoices,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $invoiceCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'invoices.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->reference ) ) {
            $model->where( 'invoices.reference', 'LIKE', '%' . $request->reference . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'invoices.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        if ( !empty( $request->id ) ) {
            $model->where( 'invoices.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->product)) {
            $model->whereHas('invoiceMetas', function ($query) use ($request) {
                $query->whereHas('product', function ($query) use ($request) {
                    $query->where('title', 'LIKE', '%' . $request->product . '%');
                });
            });
            $filter = true;
        }

        if (!empty($request->warehouse)) {
            $model->whereHas('warehouses', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->warehouse . '%');
            });
            $filter = true;
        }

        if (!empty($request->supplier)) {
            $model->whereHas('supplier', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->supplier . '%');
            });
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneInvoice( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $invoice = Invoice::with( [ 'invoiceMetas.product.warehouses','invoiceMetas.bundle','invoiceMetas.variant.product.warehouses', 'taxMethod', 'salesman', 'customer','warehouse', 'supplier'] )->find( $request->id );

        $invoice->append( [
            'encrypted_id',
            'attachment_path',
        ] );

        $invoice->taxMethod?->append( [
            'formatted_tax'
        ] );
        
        return response()->json( $invoice );
    }

    public static function deleteInvoice( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'invoice.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Invoice::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.invoices' ) ) ] ),
        ] );
    }

    public static function updateInvoiceStatus( $request ) {
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateInvoice = Invoice::find( $request->id );
            $updateInvoice->status = $updateInvoice->status == 10 ? 20 : 10;

            $updateInvoice->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'quotation' => $updateInvoice,
                    'message_key' => 'update_invoice_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_invoice_failed',
            ], 500 );
        }
    }

    public static function removeInvoiceAttachment( $request ) {

        $updateFarm = Invoice::find( Helper::decode($request->id) );
        $updateFarm->attachment = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'invoice.attachment' ) ) ] ),
        ] );
    }

    public static function oneInvoiceTransaction( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $invoice = InvoiceTransaction::with( [ 'quotation', 'account'] )->find( $request->id );

        $invoice->append( [
            'encrypted_id',
        ] );
        
        return response()->json( $invoice );
    }

    public static function createInvoiceTransaction( $request ) {

        $validator = Validator::make( $request->all(), [
            'quotation' => [ 'nullable', 'exists:invoices,id' ],
            'account' => [ 'nullable', 'exists:expenses_accounts,id' ],
            'paid_amount' => [ 'nullable', 'numeric' ,'min:0' ],
            'paid_by' => [ 'nullable' ],

        ] );

        $attributeName = [
            'title' => __( 'invoice.title' ),
            'description' => __( 'invoice.description' ),
            'image' => __( 'invoice.image' ),
            'thumbnail' => __( 'invoice.thumbnail' ),
            'url_slug' => __( 'invoice.url_slug' ),
            'structure' => __( 'invoice.structure' ),
            'size' => __( 'invoice.size' ),
            'phone_number' => __( 'invoice.phone_number' ),
            'sort' => __( 'invoice.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $invoiceCreate = InvoiceTransaction::create([
                'invoice_id' => $request->quotation,
                'account_id' => $request->account,
                'reference' => Helper::generateInvoiceTransactionNumber(),
                'remarks' => $request->remarks,
                'paid_amount' => $request->paid_amount,
                'paid_by' => $request->paid_by,
                'status' => 10,
            ]);

            $invoice = Invoice::find($request->quotation);
            $invoice->paid_amount += $request->paid_amount;
            $invoice->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.invoice_transactions' ) ) ] ),
        ] );
    }

    public static function updateInvoiceTransactionStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateInvoiceTransaction = InvoiceTransaction::find( $request->id );
            $updateInvoiceTransaction->status = $updateInvoice->status == 10 ? 20 : 10;
            $updateInvoiceTransaction->save();

            $invoice = Invoice::find($updateInvoice->invoice_id);
            if( $updateInvoiceTransaction->status == 10 ) {
                $invoice->paid_amount += $updateInvoiceTransaction->paid_amount;
            }else{
                $invoice->paid_amount -= $updateInvoiceTransaction->paid_amount;
            }
            $invoice->save();

            DB::commit();

            return response()->json( [
                'data' => [
                    'quotation' => $updateInvoice,
                    'message_key' => 'update_invoice_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_invoice_success',
            ], 500 );
        }
    }

    public static function convertDeliveryOrder($request) {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);
    
        DB::beginTransaction();
    
        try {
            $invoice = Invoice::find($request->id);
    
            if (!$invoice) {
                throw new \Exception('Sales Order not found.');
            }

            if ($invoice->status != 10) {
                throw new \Exception('Sales Order not available.');
            }
    
            $deliveryOrder = new DeliveryOrder();
            $deliveryOrder->invoice_id = $invoice->id;
            $deliveryOrder->customer_id = $invoice->customer_id;
            $deliveryOrder->salesman_id = $invoice->salesman_id;
            $deliveryOrder->warehouse_id = $invoice->warehouse_id;
            $deliveryOrder->supplier_id = $invoice->supplier_id;
            $deliveryOrder->tax_method_id = $invoice->tax_method_id;
            $deliveryOrder->order_tax = $invoice->order_tax;
            $deliveryOrder->order_discount = $invoice->order_discount;
            $deliveryOrder->shipping_cost = $invoice->shipping_cost;
            $deliveryOrder->remarks = $invoice->remarks;
            $deliveryOrder->attachment = $invoice->attachment;
            $deliveryOrder->status = 10;
            $deliveryOrder->amount = $invoice->amount;
            $deliveryOrder->original_amount = $invoice->original_amount;
            $deliveryOrder->final_amount = $invoice->final_amount;
            $deliveryOrder->paid_amount = $invoice->paid_amount;
            $deliveryOrder->reference = Helper::generateSalesOrderNumber();
            $deliveryOrder->save();
    
            $invoiceMetas = $invoice->invoiceMetas;
            foreach ($invoiceMetas as $invoiceMeta) {
                $deliveryOrderMeta = new DeliveryOrderMeta();
                $deliveryOrderMeta->delivery_order_id = $deliveryOrder->id;
                $deliveryOrderMeta->product_id = $invoiceMeta->product_id;
                $deliveryOrderMeta->custom_discount = $invoiceMeta->custom_discount;
                $deliveryOrderMeta->custom_tax = $invoiceMeta->custom_tax;
                $deliveryOrderMeta->custom_shipping_cost = $invoiceMeta->custom_shipping_cost;
                $deliveryOrderMeta->quantity = $invoiceMeta->quantity;
                $deliveryOrderMeta->product_id = $invoiceMeta->product_id;
                $deliveryOrderMeta->variant_id = $invoiceMeta->variant_id;
                $deliveryOrderMeta->bundle_id = $invoiceMeta->bundle_id;
                $deliveryOrderMeta->tax_method_id = $invoiceMeta->tax_method_id;
                $deliveryOrderMeta->status = 10;
                $deliveryOrderMeta->save();
            }
    
            $invoice->status = 14;
            $invoice->save();
    
            DB::commit();
    
            return response()->json([
                'data' => [
                    'delivery_order' => $deliveryOrder,
                    'message_key' => 'convert_delivery_order_success',
                ]
            ]);
    
        } catch (\Throwable $th) {
            DB::rollBack();
    
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'convert_delivery_order_failure',
            ], 500);
        }
    }

    public static function sendEmail( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $invoice = Invoice::with( [ 'invoiceMetas.product.warehouses','invoiceMetas.bundle','invoiceMetas.variant.product.warehouses', 'taxMethod', 'salesman', 'customer','warehouse', 'supplier'] )->find( $request->id );
            $invoice->action = 'invoice';
            // Mail::to( $invoice->customer->email )->send(new InvoiceMail( $invoice ));

            DB::commit();

            return response()->json( [
                'data' => [
                    'quotation' => $invoice,
                    'message_key' => 'mail_sent',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'mail_send_failed',
            ], 500 );
        }
    }
    
}