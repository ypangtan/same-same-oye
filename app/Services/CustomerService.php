<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    Customer,
};

use Helper;

class CustomerService
{
    public static function allCustomers( $request ) {

        $customer = Customer::select( 'customers.*' );

        $filterObject = self::filter( $request, $customer );
        $customer = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $customer->orderBy( 'created_at', $dir );
                    break;
            }
        }

        $customerCount = $customer->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $customers = $customer->skip( $offset )->take( $limit )->get();

        if ( $customers ) {
            $customers->append( [
                'display_address',
                'encrypted_id',
            ] );
        }

        $totalRecord = Customer::count();

        $data = [
            'customers' => $customers,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $customerCount : $totalRecord,
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

                $model->whereBetween( 'customers.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'customers.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( 'name', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( function( $query ) use ( $request ){
                $query->where( 'name', 'LIKE', '%' . $request->custom_search . '%' );
            } );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneCustomer( $request ) {

        if ( !$request->simple_mode ) {
            $request->merge( [
                'id' => Helper::decode( $request->id ),
            ] );
        }

        $customer = Customer::find( $request->id );

        if( $customer ) {
            $customer->append( [
                'display_address',
                'encrypted_id',
            ] );
        }

        return response()->json( $customer );
    }

    public static function createCustomer( $request ) {

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'pic_name' => [ 'nullable' ],
            'phone_number' => [ 'nullable' ],
            'phone_number_2' => [ 'nullable' ],
            'email' => [ 'nullable', 'email' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
            'postcode' => [ 'nullable', 'digits:5' ],
        ] );

        $attributeName = [
            'name' => __( 'customer.name' ),
            'pic_name' => __( 'customer.pic_name' ),
            'phone_number' => __( 'customer.phone_number' ),
            'phone_number_2' => __( 'customer.phone_number_2' ),
            'email' => __( 'customer.email' ),
            'address_1' => __( 'customer.address_1' ),
            'address_2' => __( 'customer.address_2' ),
            'remarks' => __( 'customer.remarks' ),
            'postcode' => __( 'customer.postcode' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createCustomer = Customer::create( [
                'name' => $request->name,
                'pic_name' => $request->pic_name,
                'phone_number' => $request->phone_number,
                'phone_number_2' => $request->phone_number_2,
                'email' => $request->email,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'state' => $request->state,
                'postcode' => $request->postcode,
                'remarks'=> $request->remarks,

                // 'address' => json_encode( [
                //     'a1' => $request->address,
                //     'c' => $request->city,
                //     'p' => $request->postcode,
                //     's' => $request->state,
                // ] ),
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.customers' ) ) ] ),
        ] );
    }

    public static function updateCustomer( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'pic_name' => [ 'nullable' ],
            'phone_number' => [ 'nullable' ],
            'phone_number_2' => [ 'nullable' ],
            'email' => [ 'nullable', 'email' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
            'postcode' => [ 'nullable', 'digits:5' ],
        ] );

        $attributeName = [
            'name' => __( 'customer.name' ),
            'pic_name' => __( 'customer.pic_name' ),
            'phone_number' => __( 'customer.phone_number' ),
            'phone_number_2' => __( 'customer.phone_number_2' ),
            'email' => __( 'customer.email' ),
            'address_1' => __( 'customer.address_1' ),
            'address_2' => __( 'customer.address_2' ),
            'remarks' => __( 'customer.remarks' ),
            'postcode' => __( 'customer.postcode' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateCustomer = Customer::find( $request->id );
            $updateCustomer->name = $request->name;
            $updateCustomer->pic_name = $request->pic_name;
            $updateCustomer->phone_number = $request->phone_number;
            $updateCustomer->phone_number_2 = $request->phone_number_2;
            $updateCustomer->email = $request->email;
            $updateCustomer->address_1 = $request->address_1;
            $updateCustomer->address_2 = $request->address_2;
            $updateCustomer->city = $request->city;
            $updateCustomer->state = $request->state;
            $updateCustomer->postcode = $request->postcode;
            $updateCustomer->remarks = $request->remarks;
            // $updateCustomer->address = json_encode( [
            //     'a1' => $request->address,
            //     'c' => $request->city,
            //     'p' => $request->postcode,
            //     's' => $request->state,
            // ] );
            $updateCustomer->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.customers' ) ) ] ),
        ] );
    }

    public static function updateCustomerStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateCustomer = Customer::find( $request->id );
        $updateCustomer->status = $request->status;
        $updateCustomer->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.customers' ) ) ] ),
        ] );
    }
}