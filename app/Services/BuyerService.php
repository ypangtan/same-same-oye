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
    Buyer,s
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

class BuyerService
{
    public static function allBuyers( $request ) {

        $buyer = Buyer::select( 'buyers.*' );

        $filterObject = self::filter( $request, $buyer );
        $buyer = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $buyer->orderBy( 'created_at', $dir );
                    break;
                case 3:
                    $buyer->orderBy( 'name', $dir );
                    break;
                case 4:
                    $buyer->orderBy( 'phone_number', $dir );
                    break;
                case 9:
                    $vendor->orderBy( 'status', $dir );
                    break;
            }
        }

        $BuyerCount = $buyer->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $buyers = $buyer->skip( $offset )->take( $limit )->get();

        if ( $buyers ) {
            $buyers->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = Buyer::count();

        $data = [
            'buyers' => $buyers,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $BuyerCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->registered_date ) ) {
            if ( str_contains( $request->registered_date, 'to' ) ) {
                $dates = explode( ' to ', $request->registered_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'buyers.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->registered_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'buyers.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( 'name', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->phone_number ) ) {
            $model->where( 'phone_number', 'LIKE', '%' . $request->phone_number . '%' );
            $filter = true;
        }

        if ( !empty( $request->email ) ) {
            $model->where( 'email', 'LIKE', '%' . $request->email .'%' );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( function( $query ) {
                $query->where( 'name', 'LIKE', '%' . request( 'custom_search' ) . '%' );
            } );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneBuyer( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $buyer = Buyer::find( $request->id );

        if( $buyer ) {
            $buyer->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $buyer );
    }

    public static function createBuyer( $request ) {

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'email' => [ 'nullable', 'bail', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'phone_number' => [ 'nullable', 'digits_between:8,15' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
        ] );

        $attributeName = [
            'name' => __( 'buyer.name' ),
            'email' => __( 'buyer.email' ),
            'phone_number' => __( 'buyer.phone_number' ),
            'address_1' => __( 'buyer.address_1' ),
            'address_2' => __( 'buyer.address_2' ),
            'city' => __( 'buyer.city' ),
            'state' => __( 'buyer.state' ),
            'postcode' => __( 'buyer.postcode' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createBuyer = Buyer::create( [
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'state' => $request->state,
                'postcode' => $request->postcode,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.buyers' ) ) ] ),
        ] );
    }

    public static function updateBuyer( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'email' => [ 'nullable', 'bail', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'phone_number' => [ 'nullable', 'digits_between:8,15' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
        ] );

        $attributeName = [
            'name' => __( 'buyer.name' ),
            'email' => __( 'buyer.email' ),
            'phone_number' => __( 'buyer.phone_number' ),
            'address_1' => __( 'buyer.address_1' ),
            'address_2' => __( 'buyer.address_2' ),
            'city' => __( 'buyer.city' ),
            'state' => __( 'buyer.state' ),
            'postcode' => __( 'buyer.postcode' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateBuyer = Buyer::find( $request->id );
            $updateBuyer->name = $request->name;
            $updateBuyer->email = $request->email;
            $updateBuyer->phone_number = $request->phone_number;
            $updateBuyer->address_1 = $request->address_1;
            $updateBuyer->address_2 = $request->address_2;
            $updateBuyer->city = $request->city;
            $updateBuyer->state = $request->state;
            $updateBuyer->postcode = $request->postcode;
            $updateBuyer->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.buyers' ) ) ] ),
        ] );
    }

    public static function updateBuyerStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateBuyer = Buyer::find( $request->id );
        $updateBuyer->status = $request->status;
        $updateBuyer->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.buyers' ) ) ] ),
        ] );
    }

    public static function calculateBirthday( $request ) {

        return response()->json( [
            'age' => Carbon::parse( $request->date_of_birth )->age,
        ] );
    }
}