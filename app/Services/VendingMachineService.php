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
    VendingMachine,
    FileManager,
    VendingMachineStock
};

use Illuminate\Validation\Rule;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class VendingMachineService
{

    public static function createVendingMachine( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
            'code' => [ 'nullable' ],
            'opening_hour' => [ 'nullable' ],
            'closing_hour' => [ 'nullable' ],
            'navigation_links' => [ 'nullable' ],
            'latitude' => [ 'nullable' ],
            'longitude' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'vending_machine.title' ),
            'description' => __( 'vending_machine.description' ),
            'image' => __( 'vending_machine.image' ),
            'thumbnail' => __( 'vending_machine.thumbnail' ),
            'url_slug' => __( 'vending_machine.url_slug' ),
            'structure' => __( 'vending_machine.structure' ),
            'size' => __( 'vending_machine.size' ),
            'phone_number' => __( 'vending_machine.phone_number' ),
            'sort' => __( 'vending_machine.sort' ),
            'code' => __( 'vending_machine.code' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $vendingmachineCreate = VendingMachine::create([
                'title' => $request->title,
                'description' => $request->description,
                'code' => $request->code,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'state' => $request->state,
                'postcode' => $request->postcode,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'navigation_links' => $request->navigation_links,
                'api_key' => \Str::random( 16 ),
                'closing_hour' => now()->format('Y-m-d') . ' ' . $request->closing_hour,
                'opening_hour' => now()->format('Y-m-d') . ' ' . $request->opening_hour,
            ]);

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'vending_machine/' . $vendingmachineCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $vendingmachineCreate->image = $target;
                   $vendingmachineCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'vending_machine/' . $vendingmachineCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $vendingmachineCreate->thumbnail = $target;
                   $vendingmachineCreate->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.vending_machines' ) ) ] ),
        ] );
    }
    
    public static function updateVendingMachine( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
            'opening_hour' => ['nullable'],
            'closing_hour' => ['nullable'],
            'code' => [ 'nullable' ],
            'latitude' => [ 'nullable' ],
            'longitude' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'vending_machine.title' ),
            'description' => __( 'vending_machine.description' ),
            'image' => __( 'vending_machine.image' ),
            'thumbnail' => __( 'vending_machine.thumbnail' ),
            'url_slug' => __( 'vending_machine.url_slug' ),
            'structure' => __( 'vending_machine.structure' ),
            'size' => __( 'vending_machine.size' ),
            'phone_number' => __( 'vending_machine.phone_number' ),
            'sort' => __( 'vending_machine.sort' ),
            'code' => __( 'vending_machine.code' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateVendingMachine = VendingMachine::find( $request->id );
    
            $updateVendingMachine->title = $request->title;
            $updateVendingMachine->description = $request->description;
            $updateVendingMachine->code = $request->code;
            $updateVendingMachine->address_1 = $request->address_1;
            $updateVendingMachine->address_2 = $request->address_2;
            $updateVendingMachine->latitude = $request->latitude;
            $updateVendingMachine->longitude = $request->longitude;
            $updateVendingMachine->city = $request->city;
            $updateVendingMachine->state = $request->state;
            $updateVendingMachine->postcode = $request->postcode;
            $updateVendingMachine->navigation_links = $request->navigation_links;
            $updateVendingMachine->closing_hour = now()->format('Y-m-d') . ' ' . $request->closing_hour;
            $updateVendingMachine->opening_hour = now()->format('Y-m-d') . ' ' . $request->opening_hour;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'vending_machine/' . $updateVendingMachine->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateVendingMachine->image = $target;
                   $updateVendingMachine->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateVendingMachine->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.vending_machines' ) ) ] ),
        ] );
    }

    public static function allVendingMachines( $request ) {

        $vendingMachines = VendingMachine::select( 'vending_machines.*');

        $filterObject = self::filter( $request, $vendingMachines );
        $vendingMachine = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $vendingMachine->orderBy( 'vending_machines.created_at', $dir );
                    break;
                case 2:
                    $vendingMachine->orderBy( 'vending_machines.title', $dir );
                    break;
                case 3:
                    $vendingMachine->orderBy( 'vending_machines.description', $dir );
                    break;
            }
        }

            $vendingmachineCount = $vendingMachine->count();

            $limit = $request->length;
            $offset = $request->start;

            $vendingMachines = $vendingMachine->skip( $offset )->take( $limit )->get();

            if ( $vendingMachines ) {
                $vendingMachines->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }

            $totalRecord = VendingMachine::count();

            $data = [
                'vending_machines' => $vendingMachines,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $vendingmachineCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'vending_machines.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->code ) ) {
            $model->where( 'vending_machines.code', 'LIKE', '%' . $request->code . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'vending_machines.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_vending_machine)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_vending_machine . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
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

    public static function oneVendingMachine( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $vendingMachine = VendingMachine::find( $request->id );

        $vendingMachine->append( ['encrypted_id','image_path'] );
        
        return response()->json( $vendingMachine );
    }

    public static function deleteVendingMachine( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'vending_machine.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            VendingMachine::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.vending_machines' ) ) ] ),
        ] );
    }

    public static function updateVendingMachineStatus( $request ) {
        
        if( $request->id ){
            $request->merge( [
                'id' => Helper::decode( $request->id ),
            ] );
        }

        $validator = Validator::make($request->all(), [
            'status' => [ 'nullable', 'in:10,20,21'  ],
        ]);

        $validator->validate();

        DB::beginTransaction();

        try {

            $updateVendingMachine = $request->id ? VendingMachine::find( $request->id ) : VendingMachine::where('api_key', $request->header('X-Vending-Machine-Key'))->first();
            
            $updateVendingMachine->status = $updateVendingMachine->status == 10 ? 20 : 10;
            if( $request->status ){
                $updateVendingMachine->status = $request->status;
            }

            $updateVendingMachine->save();
            $updateVendingMachine->append(['status_label']);
            DB::commit();

            return response()->json( [
                'data' => [
                    'vending_machine' => $updateVendingMachine,
                    'message_key' => 'update_vending_machine_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_vending_machine_failed',
            ], 500 );
        }
    }

    public static function getVendingMachineStatus( $request ) {
        
        if( $request->id ){
            $request->merge( [
                'id' => Helper::decode( $request->id ),
            ] );
        }

        $validator = Validator::make($request->all(), [
            'status' => [ 'nullable', 'in:10,20,21'  ],
        ]);

        $validator->validate();

        DB::beginTransaction();

        try {

            $vendingMachine = $request->id ? VendingMachine::with( ['stocks.froyo','stocks.syrup','stocks.topping'] )->find( $request->id ) : VendingMachine::with( ['stocks.froyo','stocks.syrup','stocks.topping'] )->where('api_key', $request->header('X-Vending-Machine-Key'))->first();

            return response()->json( [
                'data' => [
                    'vending_machine' => $vendingMachine,
                    'message_key' => 'get_vending_machine_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'get_vending_machine_failed',
            ], 500 );
        }
    }

    public static function removeVendingMachineGalleryImage( $request ) {

        $updateVendingMachine = VendingMachine::find( Helper::decode($request->id) );
        $updateVendingMachine->image = null;
        $updateVendingMachine->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }

    public static function getVendingMachines( $request ) {

        $vendingMachines = VendingMachine::select( 'vending_machines.*')->where('status',10);

        $filterObject = self::filter( $request, $vendingMachines );
        $vendingMachine = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $vendingMachine->orderBy( 'vending_machines.created_at', $dir );
                    break;
                case 2:
                    $vendingMachine->orderBy( 'vending_machines.title', $dir );
                    break;
                case 3:
                    $vendingMachine->orderBy( 'vending_machines.description', $dir );
                    break;
            }
        }

        $vendingmachineCount = $vendingMachine->count();

        $limit = 10;
        $offset = 0;

        $vendingMachines = $vendingMachine->skip( $offset )->take( $limit )->get();

        if ( $vendingMachines ) {

            $vendingMachines->makeHidden([
                'opening_hour',
                'closing_hour',
                'created_at',
                'updated_at',
                'outlet_id',
                'api_key',
            ]);

            $vendingMachines->append( [
                'image_path',
                'operational_hour',
                'formatted_closing_hour',
                'formatted_opening_hour',
            ] );
        }

        return response()->json([
            'message' => '',
            'message_key' => 'get_vending_machine_success',
            'data' => $vendingMachines,
        ]);

    }

    public static function updateVendingMachineStock($request)
    {
        DB::beginTransaction();

        $vendingMachine = VendingMachine::where('api_key', $request->header('X-Vending-Machine-Key'))->first();

        $stockData = $request->all();
        $updateMethod = $request->update_method;
    
        try {
            // Iterate over each stock type
            foreach (['froyos', 'syrups', 'toppings'] as $stockType) {

                if (!empty($stockData[$stockType])) {
                    self::processStockUpdates($vendingMachine->id, $stockData[$stockType], $stockType, $updateMethod);
                }
            }
    
            DB::commit();
    
            return response()->json([
                'message' => __('template.x_updated', ['title' => Str::singular(__('template.vending_machine_stocks'))]),
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
    
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
    }
    
    private static function processStockUpdates($vendingMachineId, array $stocks, $stockType, $updateMethod)
    {
        $columnMap = [
            'froyos' => 'froyo_id',
            'syrups' => 'syrup_id',
            'toppings' => 'topping_id'
        ];
    
        $column = $columnMap[$stockType] ?? null;
        if (!$column) {
            throw new \Exception("Invalid stock type: $stockType.");
        }
    
        foreach ($stocks as $stockItem) {
            foreach ($stockItem as $stockId => $change) {
                $vendingMachineStock = VendingMachineStock::where('vending_machine_id', $vendingMachineId)
                    ->where($column, $stockId)
                    ->first();

                if ($vendingMachineStock) {
                    $newQuantity = $updateMethod == 1 ? $vendingMachineStock->quantity + $change : $change;
    
                    // Prevent negative stock values
                    // if ($newQuantity < 0) {
                    //     throw new \Exception("Stock quantity for $column: $stockId cannot be negative.");
                    // }
    
                    $vendingMachineStock->old_quantity = $vendingMachineStock->quantity;
                    $vendingMachineStock->quantity = $newQuantity;
                    $vendingMachineStock->save();
                } 
            }
        }
    }
    
}