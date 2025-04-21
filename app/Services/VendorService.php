<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use App\Models\{
    FileManager,
    Vendor,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

class VendorService
{
    public static function allVendors( $request ) {

        $vendor = Vendor::select( 'vendors.*' );

        $filterObject = self::filter( $request, $vendor );
        $vendor = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $vendor->orderBy( 'created_at', $dir );
                    break;
                case 3:
                    $vendor->orderBy( 'name', $dir );
                    break;
                case 4:
                    $vendor->orderBy( 'email', $dir );
                    break;
                case 5:
                    $vendor->orderBy( 'phone_number', $dir );
                    break;
                case 6:
                    $vendor->orderBy( 'type', $dir );
                    break;
                case 7:
                    $vendor->orderBy( 'status', $dir );
                    break;
            }
        }

        $vendorCount = $vendor->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $vendors = $vendor->skip( $offset )->take( $limit )->get();

        foreach ( $vendors as $vendor ) {
            $vendor->append( [
                'path',
            ] );
        }

        if ( $vendors ) {
            $vendors->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = Vendor::count();

        $data = [
            'vendors' => $vendors,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $vendorCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vendors.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vendors.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( 'name', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->email ) ) {
            $model->where( 'email', 'LIKE', '%' . $request->email . '%' );
            $filter = true;
        }

        if ( !empty( $request->phone_number ) ) {
            $model->where( 'phone_number', 'LIKE', '%' . $request->phone_number . '%' );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'vendors.name', 'LIKE', '%' . $request->custom_search . '%' );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneVendor( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $vendor = Vendor::find( $request->id );

        if( $vendor ) {
            $vendor->append( [
                'address_object',
                'path',
                'encrypted_id',
            ] );
        }

        return response()->json( $vendor );
    }

    public static function createVendor( $request ) {

        $validator = Validator::make( $request->all(), [
            // 'photo' => [ 'required' ],
            'name' => [ 'required' ],
            'email' => [ 'required', 'bail', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'phone_number' => [ 'required', 'digits_between:8,15' ],
            'type' => [ 'required' ],
            'address_1' => [ 'required' ],
            'city' => [ 'required' ],
            'postcode' => [ 'required' ],
            'state' => [ 'required' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'name' => __( 'vendor.name' ),
            'email' => __( 'vendor.email' ),
            'phone_number' => __( 'vendor.phone_number' ),
            'website' => __( 'vendor.website' ),
            'type' => __( 'vendor.type' ),
            'address_1' => __( 'vendor.address_1' ),
            'address_2' => __( 'vendor.address_2' ),
            'city' => __( 'vendor.city' ),
            'postcode' => __( 'vendor.postcode' ),
            'state' => __( 'vendor.state' ),
            'remarks' => __( 'vendor.remarks' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            
            $createVendor = Vendor::create( [
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                // 'website' => $request->website,
                'type' => $request->type,
                'address' => json_encode( [
                    'address_1' => $request->address_1,
                    'address_2' => $request->address_2,
                    'city' => $request->city,
                    'postcode' => $request->postcode,
                    'state' => $request->state,
                ] ),
                'remarks' => $request->remarks,
                'status' => 10,
            ] );

            $file = FileManager::find( $request->photo );
            if ( $file ) {
                $fileName = explode( '/', $file->file );
                $target = 'vendors/' . $createVendor->id . '/' . $fileName[1];
                Storage::disk( 'public' )->move( $file->file, $target );

                $createVendor->photo = $target;
                $createVendor->save();

                $file->status = 10;
                $file->save();
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.vendors' ) ) ] ),
        ] );
    }

    public static function updateVendor( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            // 'photo' => [ 'required' ],
            'name' => [ 'required' ],
            'email' => [ 'required', 'bail', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'phone_number' => [ 'required', 'digits_between:8,15' ],
            'type' => [ 'required' ],
            'address_1' => [ 'required' ],
            'city' => [ 'required' ],
            'postcode' => [ 'required' ],
            'state' => [ 'required' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'name' => __( 'vendor.name' ),
            'email' => __( 'vendor.email' ),
            'phone_number' => __( 'vendor.phone_number' ),
            'website' => __( 'vendor.website' ),
            'type' => __( 'vendor.type' ),
            'address_1' => __( 'vendor.address_1' ),
            'address_2' => __( 'vendor.address_2' ),
            'city' => __( 'vendor.city' ),
            'postcode' => __( 'vendor.postcode' ),
            'state' => __( 'vendor.state' ),
            'remarks' => __( 'vendor.remarks' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateVendor = Vendor::find( $request->id );
            $updateVendor->name = $request->name;
            $updateVendor->email = $request->email;
            $updateVendor->phone_number = $request->phone_number;
            // $updateVendor->website = $request->website;
            $updateVendor->type = $request->type;
            $updateVendor->address = json_encode( [
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'postcode' => $request->postcode,
                'state' => $request->state,
            ] );
            $updateVendor->remarks = $request->remarks;
            $updateVendor->save();

            if ( $request->photo ) {
                $file = FileManager::find( $request->photo );
                if ( $file ) {

                    Storage::disk( 'public' )->delete( $updateVendor->photo );

                    $fileName = explode( '/', $file->file );
                    $target = 'vendors/' . $updateVendor->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $file->file, $target );
    
                    $updateVendor->photo = $target;
                    $updateVendor->save();
    
                    $file->status = 10;
                    $file->save();
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
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.vendors' ) ) ] ),
        ] );
    }

    public static function updateVendorStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateVendor = Vendor::find( $request->id );
        $updateVendor->status = $request->status;
        $updateVendor->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.vendors' ) ) ] ),
        ] );
    }
}