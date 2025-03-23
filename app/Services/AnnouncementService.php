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
    Announcement,
    Booking,
    FileManager,
    VendingMachine,
    VendingMachineStock,
    AnnouncementUsage,
    Cart,
    CartMeta,
    Order,
    OrderMeta,
    UserAnnouncement,
    Voucher,
    Product,
    AnnouncementReward,
    AnnouncementView,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AnnouncementService
{

    public static function createAnnouncement( $request ) {

        $request->merge( [
            'claim_per_user' => 1,
            'total_claimable' => 100000,
            'usable_amount' => 1,
        ] );

        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'discount_type' => [ 'nullable' ],
            'voucher_type' => [ 'nullable' ],
            'promo_code' => ['nullable', 'unique:announcements,promo_code'],
            'image' => [ 'nullable' ],
            'start_date' => [ 'nullable', 'required_with:discount_type' ],
            'expired_date' => [ 'nullable', 'required_with:discount_type' ],
            'total_claimable' => [ 'nullable' ],
            'points_required' => [ 'nullable' ],
            'usable_amount' => [ 'nullable' ],
            'validity_days' => [ 'nullable' ],
            'adjustment_data' => ['nullable', 'required_with:discount_type' ],
            'view_once' => ['nullable'],
            'new_user_only' => ['nullable'],
        ] );

        $attributeName = [
            'title' => __( 'announcement.title' ),
            'description' => __( 'announcement.description' ),
            'image' => __( 'announcement.image' ),
            'code' => __( 'announcement.code' ),
            'ingredients' => __( 'announcement.ingredients' ),
            'nutritional_values' => __( 'announcement.nutritional_values' ),
            'price' => __( 'announcement.price' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $adjustmentData = json_decode($request->adjustment_data, true);

        if ($request->discount_type == 3) {
            
            // if( $adjustmentData['buy_products'] == null ) {
            //     $adjustmentData['buy_products'] = [ (string) Product::where( 'status', 10 )->first()->id ];
            // }

            if( $adjustmentData['buy_quantity'] == null ) {
                $adjustmentData['buy_quantity'] = 0;
            }

            if (!$adjustmentData) {
                return response()->json(['error' => __('Invalid adjustment data')], 422);
            }

            $validator = Validator::make($adjustmentData, [
                // 'buy_products' => ['required', 'array'],
                'buy_quantity' => ['required', 'numeric', 'min:0'], // Added numeric and min validation
                'get_quantity' => ['required', 'numeric', 'min:1'], // Added numeric and min validation
                'get_product' => ['required', 'exists:products,id'],
            ]);
        
            $attributeName = [
                'buy_products' => __('announcement.buy_products'),
                'buy_quantity' => __('announcement.buy_quantity'),
                'get_quantity' => __('announcement.get_quantity'),
                'get_product' => __('announcement.get_product'),
            ];

            $request->merge( [
                'adjustment_data' => json_encode( $adjustmentData )
            ] );

            $validator->setAttributeNames($attributeName)->validate();
        } elseif ($request->discount_type == 2) {
            $validator = Validator::make($adjustmentData, [
                'buy_quantity' => ['required', 'numeric', 'min:1'], // Added numeric and min validation
                'discount_quantity' => ['required', 'numeric', 'min:0'],
            ]);
        
            $attributeName = [
                'buy_quantity' => __('announcement.buy_quantity'),
                'discount_quantity' => __('announcement.discount_quantity'),
                'discount_type' => __('announcement.discount_type'),
            ];
        
            $validator->setAttributeNames($attributeName)->validate();
        }

        DB::beginTransaction();
        
        try {
            $announcementCreate = Announcement::create([
                'title' => $request->title,
                'discount_type' => $request->discount_type ? $request->discount_type : 1,
                'description' => $request->description,
                'promo_code' => $request->promo_code,
                'total_claimable' => $request->total_claimable,
                'points_required' => $request->points_required,
                'start_date' => $request->start_date,
                'expired_date' => $request->expired_date,
                'buy_x_get_y_adjustment' => $request->adjustment_data,
                'usable_amount' => $request->usable_amount,
                'validity_days' => $request->validity_days,
                'view_once' => $request->view_once,
                'new_user_only' => $request->new_user_only,
            ]);

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $imageFiles1 = FileManager::where( 'id', $request->unclaimed_image )->first();
            $imageFiles2 = FileManager::where( 'id', $request->claiming_image )->first();
            $imageFiles3 = FileManager::where( 'id', $request->claimed_image )->first();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'announcement/' . $announcementCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $announcementCreate->image = $target;
                   $announcementCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $imageFiles1 ) {

                $fileName = explode( '/', $imageFiles1->file );
                $fileExtention = pathinfo($fileName[1])['extension'];

                $target = 'announcement/' . $announcementCreate->id . '/' . $fileName[1];
                Storage::disk( 'public' )->move( $imageFiles1->file, $target );

                $announcementCreate->unclaimed_image = $target;
                $announcementCreate->save();

                $imageFiles1->status = 10;
                $imageFiles1->save();
            }

            if ( $imageFiles2 ) {

                $fileName = explode( '/', $imageFiles2->file );
                $fileExtention = pathinfo($fileName[1])['extension'];

                $target = 'announcement/' . $announcementCreate->id . '/' . $fileName[1];
                Storage::disk( 'public' )->move( $imageFiles2->file, $target );

                $announcementCreate->claiming_image = $target;
                $announcementCreate->save();

                $imageFiles2->status = 10;
                $imageFiles2->save();
            }

            if ( $imageFiles3 ) {

                $fileName = explode( '/', $imageFiles3->file );
                $fileExtention = pathinfo($fileName[1])['extension'];

                $target = 'announcement/' . $announcementCreate->id . '/' . $fileName[1];
                Storage::disk( 'public' )->move( $imageFiles3->file, $target );

                $announcementCreate->claimed_image = $target;
                $announcementCreate->save();

                $imageFiles3->status = 10;
                $imageFiles3->save();
            }

            if( $request->discount_type && $request->promo_code ) {
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
    
                $voucherCreate->image = $announcementCreate->image;
                $voucherCreate->save();

                $announcementCreate->voucher_id = $voucherCreate->id;
                $announcementCreate->save();
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.announcements' ) ) ] ),
        ] );
    }
    
    public static function updateAnnouncement( $request ) {

        $request->merge( [
            'claim_per_user' => 1,
            'total_claimable' => 100000,
        ] );

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'discount_type' => [ 'required' ],
            'voucher_type' => [ 'nullable' ],
            'promo_code' => [ 'nullable', 'unique:announcements,promo_code,' . $request->id, ],
            'image' => [ 'nullable' ],
            'start_date' => [ 'nullable', 'required_with:discount_type' ],
            'expired_date' => [ 'nullable', 'required_with:discount_type' ],
            'total_claimable' => [ 'nullable' ],
            'points_required' => [ 'nullable' ],
            'usable_amount' => [ 'nullable' ],
            'validity_days' => [ 'nullable' ],
            'adjustment_data' => ['nullable', 'required_with:discount_type' ],
            'view_once' => ['nullable'],
            'new_user_only' => ['nullable'],
            
        ] );

        $attributeName = [
            'title' => __( 'announcement.title' ),
            'description' => __( 'announcement.description' ),
            'image' => __( 'announcement.image' ),
            'code' => __( 'announcement.code' ),
            'ingredients' => __( 'announcement.ingredients' ),
            'nutritional_values' => __( 'announcement.nutritional_values' ),
            'price' => __( 'announcement.price' ),
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
                // 'buy_products' => ['required', 'array'],
                'buy_quantity' => ['required', 'numeric', 'min:0'], // Added numeric and min validation
                'get_quantity' => ['required', 'numeric', 'min:1'], // Added numeric and min validation
                'get_product' => ['required', 'exists:products,id'],
            ]);
        
            $attributeName = [
                'buy_products' => __('announcement.buy_products'),
                'buy_quantity' => __('announcement.buy_quantity'),
                'get_quantity' => __('announcement.get_quantity'),
                'get_product' => __('announcement.get_product'),
            ];
        
            $validator->setAttributeNames($attributeName)->validate();
        } elseif ($request->discount_type == 2) {
            $validator = Validator::make($adjustmentData, [
                'buy_quantity' => ['required', 'numeric', 'min:1'], // Added numeric and min validation
                'discount_quantity' => ['required', 'numeric', 'min:0'],
            ]);
        
            $attributeName = [
                'buy_quantity' => __('announcement.buy_quantity'),
                'discount_quantity' => __('announcement.discount_quantity'),
                'discount_type' => __('announcement.discount_type'),
            ];
        
            $validator->setAttributeNames($attributeName)->validate();
        }
        
        DB::beginTransaction();

        try {
            $updateAnnouncement = Announcement::with( ['voucher'] )->find( $request->id );
    
            $updateAnnouncement->title = $request->title;
            $updateAnnouncement->discount_type = $request->discount_type;
            $updateAnnouncement->description = $request->description;
            $updateAnnouncement->promo_code = $request->promo_code;
            $updateAnnouncement->start_date = $request->start_date;
            $updateAnnouncement->expired_date = $request->expired_date;
            $updateAnnouncement->new_user_only = $request->new_user_only;
            $updateAnnouncement->view_once = $request->view_once;
            
            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $imageFiles1 = FileManager::where( 'id', $request->unclaimed_image )->first();
            $imageFiles2 = FileManager::where( 'id', $request->claiming_image )->first();
            $imageFiles3 = FileManager::where( 'id', $request->claimed_image )->first();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'announcement/' . $updateAnnouncement->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateAnnouncement->image = $target;
                   $updateAnnouncement->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $imageFiles1 ) {

                $fileName = explode( '/', $imageFiles1->file );
                $fileExtention = pathinfo($fileName[1])['extension'];

                $target = 'announcement/' . $updateAnnouncement->id . '/' . $fileName[1];
                Storage::disk( 'public' )->move( $imageFiles1->file, $target );

                $updateAnnouncement->unclaimed_image = $target;
                $updateAnnouncement->save();

                $imageFiles1->status = 10;
                $imageFiles1->save();
            }

            if ( $imageFiles2 ) {

                $fileName = explode( '/', $imageFiles2->file );
                $fileExtention = pathinfo($fileName[1])['extension'];

                $target = 'announcement/' . $updateAnnouncement->id . '/' . $fileName[1];
                Storage::disk( 'public' )->move( $imageFiles2->file, $target );

                $updateAnnouncement->claiming_image = $target;
                $updateAnnouncement->save();

                $imageFiles2->status = 10;
                $imageFiles2->save();
            }

            if ( $imageFiles3 ) {

                $fileName = explode( '/', $imageFiles3->file );
                $fileExtention = pathinfo($fileName[1])['extension'];

                $target = 'announcement/' . $updateAnnouncement->id . '/' . $fileName[1];
                Storage::disk( 'public' )->move( $imageFiles3->file, $target );

                $updateAnnouncement->claimed_image = $target;
                $updateAnnouncement->save();

                $imageFiles3->status = 10;
                $imageFiles3->save();
            }

            // update Voucher
            $updateVoucher = Voucher::find( $updateAnnouncement->voucher_id );
    
            if( $updateVoucher ) {
                $updateVoucher->title = $request->title;
                $updateVoucher->discount_type = $request->discount_type;
                $updateVoucher->type = $request->voucher_type;
                $updateVoucher->description = $request->description;
                $updateVoucher->promo_code = $request->promo_code;
                $updateVoucher->total_claimable = $request->total_claimable;
                $updateVoucher->points_required = $request->points_required;
                $updateVoucher->start_date = $request->start_date;
                $updateVoucher->expired_date = $request->expired_date;
                // $updateVoucher->usable_amount = $request->usable_amount;
                $updateVoucher->validity_days = $request->validity_days;
                $updateVoucher->buy_x_get_y_adjustment = $request->adjustment_data;
                $updateVoucher->image = $updateAnnouncement->image;
                $updateVoucher->save();
            } else if ( !$updateVoucher && $request->discount_type ) {
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
                    'usable_amount' => 1,
                    'validity_days' => $request->validity_days,
                    'claim_per_user' => $request->claim_per_user,
                ]);
    
                $voucherCreate->image = $updateAnnouncement->image;
                $voucherCreate->save();
            }

            $updateAnnouncement->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.announcements' ) ) ] ),
        ] );
    }

    public static function allAnnouncements( $request ) {

        $announcements = Announcement::with( ['voucher'] )->select( 'announcements.*');

        $filterObject = self::filter( $request, $announcements );
        $announcement = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $announcement->orderBy( 'announcements.created_at', $dir );
                    break;
                case 2:
                    $announcement->orderBy( 'announcements.title', $dir );
                    break;
                case 3:
                    $announcement->orderBy( 'announcements.description', $dir );
                    break;
            }
        }

            $announcementCount = $announcement->count();

            $limit = $request->length;
            $offset = $request->start;

            $announcements = $announcement->skip( $offset )->take( $limit )->get();

            if ( $announcements ) {
                $announcements->append( [
                    'encrypted_id',
                    'image_path',
                    'unclaimed_image_path',
                    'claiming_image_path',
                    'claimed_image_path',
                ] );
            }

            $totalRecord = Announcement::count();

            $data = [
                'announcements' => $announcements,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $announcementCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

    }

    public static function allStocksAnnouncements( $request ) {

        // Query all announcements not in vending_machine_stocks
        $announcements = Announcement::with( ['voucher'] )->select( 'announcements.*' )
            ->whereNotIn('id', function ($query) {
                $query->select('announcement_id')
                    ->from('vending_machine_stocks')
                    ->whereNotNull('announcement_id');
            });
    
        $filterObject = self::filter( $request, $announcements );
        $announcement = $filterObject['model'];
        $filter = $filterObject['filter'];
    
        // Handle sorting
        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $announcement->orderBy( 'announcements.created_at', $dir );
                    break;
                case 3:
                    $announcement->orderBy( 'announcements.title', $dir );
                    break;
                case 4:
                    $announcement->orderBy( 'announcements.description', $dir );
                    break;
            }
        }
    
        $announcementCount = $announcement->count();
    
        $limit = $request->length;
        $offset = $request->start;
    
        // Paginate results
        $announcements = $announcement->skip( $offset )->take( $limit )->get();
    
        if ( $announcements ) {
            $announcements->append( [
                'encrypted_id',
                'image_path',
                'unclaimed_image_path',
                'claiming_image_path',
                'claimed_image_path',
            ] );
        }
    
        $totalRecord = Announcement::whereNotIn('id', function ($query) {
            $query->select('announcement_id')
                ->from('vending_machine_stocks')
                ->whereNotNull('announcement_id');
        })->count();
    
        $data = [
            'announcements' => $announcements,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $announcementCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];
    
        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'announcements.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'announcements.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_announcement)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_announcement . '%');
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
            $vendingMachineAnnouncements = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'announcement_id' );
            $model->whereNotIn( 'id', $vendingMachineAnnouncements );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneAnnouncement( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $announcement = Announcement::with( ['voucher'] )->find( $request->id );

        $announcement->append( ['encrypted_id','image_path', 
        'unclaimed_image_path',
        'claiming_image_path',
        'claimed_image_path',] );

        if( $announcement->voucher ) {
            $announcement->voucher->append( ['encrypted_id','image_path','decoded_adjustment'] );
        }
        
        return response()->json( $announcement );
    }

    public static function deleteAnnouncement( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'announcement.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Announcement::with( ['voucher'] )->find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.announcements' ) ) ] ),
        ] );
    }

    public static function updateAnnouncementStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateAnnouncement = Announcement::with( ['voucher'] )->find( $request->id );
            $updateAnnouncement->status = $updateAnnouncement->status == 10 ? 20 : 10;

            $updateAnnouncement->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'announcement' => $updateAnnouncement,
                    'message_key' => 'update_announcement_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_announcement_failed',
            ], 500 );
        }
    }

    public static function removeAnnouncementGalleryImage( $request ) {

        $updateAnnouncement = Announcement::with( ['voucher'] )->find( Helper::decode($request->id) );
        
        switch ($request->scope) {
            case 'image':
                Storage::delete( 'public/' . $updateAnnouncement->image );
                $updateAnnouncement->image = null;
                if($updateAnnouncement->voucher){
                    $updateAnnouncement->voucher->image = null;
                    $updateAnnouncement->voucher->save();
                }
                break;

            case 'unclaimed_image':
                Storage::delete( 'public/' . $updateAnnouncement->unclaimed_image );
                $updateAnnouncement->unclaimed_image = null;
                break;

            case 'claiming_image':
                Storage::delete( 'public/' . $updateAnnouncement->claiming_image );
                $updateAnnouncement->claiming_image = null;
                break;

            case 'claimed_image':
                Storage::delete( 'public/' . $updateAnnouncement->claimed_image );
                $updateAnnouncement->claimed_image = null;
                break;
            
            default:
                # code...
                break;
        }

        $updateAnnouncement->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }

    public static function allAnnouncementsForVendingMachine( $request ) {

        $announcements = Announcement::with( ['voucher'] )->select( 'announcements.*');

        $filterObject = self::filter( $request, $announcements );
        $announcement = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $announcement->orderBy( 'announcements.created_at', $dir );
                    break;
                case 2:
                    $announcement->orderBy( 'announcements.title', $dir );
                    break;
                case 3:
                    $announcement->orderBy( 'announcements.description', $dir );
                    break;
            }
        }

        $announcementCount = $announcement->count();

        $limit = $request->length;
        $offset = $request->start;

        $announcements = $announcement->skip( $offset )->take( $limit )->get();

        if ( $announcements ) {

            $announcements->append( [
                'encrypted_id',
                'image_path',
            ] );
        }

        $totalRecord = Announcement::count();

        $data = [
            'announcements' => $announcements,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $announcementCount : $totalRecord,
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

    public static function getAnnouncements( $request )
    {

        $announcements = Announcement::with(['voucher'])
        ->where('status', 10)
        ->where(function ($query) {
            $query->where(function ($query) {
                $query->whereNull('start_date');
                $query->whereNull('expired_date');
            })
            ->orWhere(function ($query) {
                $query->where('start_date', '<=', now()->endOfDay());
                $query->where('expired_date', '>=', now()->startOfDay());
            });
        })
        ->when(auth()->check(), function ($query) {
            $user = auth()->user();
    
            // Exclude announcements already viewed if `view_once` is enabled
            $query->whereNotIn('id', function ($subQuery) use ($user) {
                $subQuery->select('announcement_id')
                    ->from('announcement_views') // Assuming a table tracks views
                    ->where('user_id', $user->id);
            });
    
            // Filter for new users if `new_user_only` is enabled
            if ($user->created_at->diffInDays(now()) > 7) { // Assuming "new user" means 7 days
                $query->where('new_user_only', 0);
            }
        })
        ->orderBy('created_at', 'DESC')
        ->get();

        $announcements = $announcements->map(function ($announcement) {
            $announcement->makeHidden( [ 'created_at', 'updated_at'] );
            $announcement->append([ 'image_path', 'unclaimed_image_path', 'claiming_image_path', 'claimed_image_path' ]);
            $announcement->voucher?->append(['decoded_adjustment', 'image_path','voucher_type','voucher_type_label']);
            return $announcement;
        });

        return response()->json( [
            'message' => '',
            'message_key' => 'get_announcement_success',
            'data' => $announcements,
        ] );

    }

    public static function validateAnnouncement( $request )
    {

        $validator = Validator::make( $request->all(), [
            'promo_code' => [ 'required' ],
        ] );

        $attributeName = [
            'promo_code' => __( 'announcement.promo_code' ),
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

        $announcement = Announcement::where('status', 10)
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

        if ( !$announcement ) {
            return response()->json( [
                'message_key' => 'announcement_not_available',
                'message' => __('announcement.announcement_not_available'),
                'errors' => [
                    'announcement' => __('announcement.announcement_not_available')
                ]
            ], 422 );
        }

        // user's usage
        $user = auth()->user();
        $announcementUsages = AnnouncementUsage::where( 'announcement_id', $announcement->id )->where( 'user_id', $user->id )->get();

        if ( $announcementUsages->count() > $announcement->usable_amount ) {
            return response()->json( [
                'message_key' => 'announcement_you_have_maximum_used',
                'message' => __('announcement.announcement_you_have_maximum_used'),
                'errors' => [
                    'announcement' => __('announcement.announcement_you_have_maximum_used')
                ]
            ], 422 );
        }

        // total claimable
        if ( $announcement->total_claimable <= 0 ) {
            return response()->json( [
                'message_key' => 'announcement_fully_claimed',
                'message' => __('announcement.announcement_fully_claimed'),
                'errors' => [
                    'announcement' => __('announcement.announcement_fully_claimed')
                ]
            ], 422 );
        }

        // check is user able to claim this
        $userAnnouncement = UserAnnouncement::where( 'announcement_id', $announcement->id )->where( 'user_id', $user->id )->first();
        if(!$userAnnouncement){
            $userPoints = $user->wallets->where( 'type', 2 )->first();

            if ( $userPoints->balance < $announcement->points_required ) {

                return response()->json( [
                    'message_key' => 'minimum_points_required',
                    'message' => 'Mininum of ' . $announcement->points_required . ' points is required to claim this announcement',
                    'errors' => [
                        'announcement' => 'Mininum of ' . $announcement->points_required . ' points is required to claim this announcement',
                    ]
                ], 422 );
    
            }
        }

        $cart = Cart::find( $request->cart );

        if ( $announcement->discount_type == 3 ) {

            $adjustment = json_decode( $announcement->buy_x_get_y_adjustment );
            
            $x = $cart->cartMetas->whereIn( 'product_id', $adjustment->buy_products )->count();

            if ( $x < $adjustment->buy_quantity ) {
                return response()->json( [
                   'required_amount' => $adjustment->buy_quantity,
                   'message' => __( 'announcement.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] ),
                   'message_key' => 'announcement.min_quantity_of_x_' . $adjustment->buy_products[0] . '_' .  Product::find( $adjustment->buy_products[0] )->value( 'title' ) ,
                        'errors' => [
                            'announcement' => __( 'announcement.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] )

                        ]
                ], 422 );
            }

        } else {

            $adjustment = json_decode( $announcement->buy_x_get_y_adjustment );

            if ( $cart->total_price < $adjustment->buy_quantity ) {
                return response()->json( [
                    'required_amount' => $adjustment->buy_quantity,
                    'message' => __( 'announcement.min_spend_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id', $adjustment->buy_products[0] )->value( 'title' ) ] ),
                    'message_key' => 'announcement.min_spend_of_x',
                    'errors' => [
                        'announcement' => __( 'announcement.min_spend_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id', $adjustment->buy_products[0] )->value( 'title' ) ] )
                    ]
                ], 422 );
            }

        }
    
        return response()->json( [
            'message' => 'announcement.announcement_validated',
        ] );
    }

    public static function claimAnnouncement( $request )
    {

        $validator = Validator::make( $request->all(), [
            'announcement_id' => [ 'required' ],
        ] );

        $attributeName = [
            'announcement_id' => __( 'announcement.announcement_id' ),
        ];
        
        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $announcement = Announcement::where( 'id', $request->announcement_id )
        ->orWhere( 'promo_code', $request->announcement_id )
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

        if ( !$announcement ) {
            return response()->json( [
                'message_key' => 'announcement_not_available',
                'message' => __('announcement.announcement_not_available'),
                'errors' => [
                    'announcement' => __('announcement.announcement_not_available'),
                ]
            ], 422 );
        }
        $user = auth()->user();

        $announcementUsages = AnnouncementUsage::where( 'announcement_id', $announcement->id )->where( 'user_id', $user->id )->get();

        if ( $announcementUsages->count() > $announcement->usable_amount ) {
            return response()->json( [
                'message_key' => 'announcement_fully_claimed',
                'message' => __('announcement.announcement_fully_claimed'),
                'errors' => [
                    'announcement' => __('announcement.announcement_fully_claimed'),
                ]
            ], 422 );
        }

        $announcementUserClaimed = UserAnnouncement::where( 'announcement_id', $announcement->id )->where( 'user_id', $user->id )->count();

        if ( $announcementUserClaimed >= $announcement->view_once ) {
            return response()->json( [
                'message_key' => 'announcement_you_have_maximum_claimed',
                'message' => __('announcement.announcement_you_have_maximum_claimed'),
                'errors' => [
                    'announcement' => __('announcement.announcement_you_have_maximum_claimed'),
                ]
            ], 422 );
        }
        
        $userPoints = $user->wallets->where( 'type', 2 )->first();

        if ( $userPoints->balance < $announcement->points_required ) {

            return response()->json( [
                'required_amount' => $announcement->points_required,
                'message' => 'Mininum of ' . $announcement->points_required . ' points is required to claim this announcement',
                'message_key' => 'minimum_points_is_required',
                'errors' => [
                    'announcement' => 'Mininum of ' . $announcement->points_required . ' points is required to claim this announcement',
                ]
            ], 422 );

        }        
        
        if ( $announcement->total_claimable <= 0 ) {
            return response()->json( [
                'message_key' => 'announcement_fully_claimed',
                'message' => __('announcement.announcement_fully_claimed'),
                'errors' => [
                    'announcement' => __('announcement.announcement_fully_claimed')
                ]
            ], 422 );
        }

        WalletService::transact( $userPoints, [
            'amount' => -$announcement->points_required,
            'remark' => 'Claim Announcement',
            'type' => $userPoints->type,
            'transaction_type' => 11,
        ] );

        $userAnnouncement = UserAnnouncement::create([
            'user_id' => $user->id,
            'announcement_id' => $announcement->id,
            'expired_date' => Carbon::now()->addDays($announcement->validity_days),
            'status' => 10,
            'redeem_from' => 1,
            'total_left' => 1,
            'used_at' => null,
            'secret_code' => strtoupper( \Str::random( 8 ) ),
        ]);

        $announcement->total_claimable -= 1;
        $announcement->save();
    
        // notification
        UserService::createUserNotification(
            $user->id,
            'notification.user_announcement_success',
            'notification.user_announcement_success_content',
            'announcement',
            'announcement'
        );

        self::sendNotification( $order->user, 'announcement', __( 'notification.user_announcement_success_content' )  );

        return response()->json( [
            'message' => __('announcement.announcement_claimed'),
            'message_key' => 'announcement_claimed',
            'data' => $userAnnouncement->load(['announcement'])
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