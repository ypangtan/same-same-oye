<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    Company,
};

use Helper;

class CompanyService
{
    public static function allCompanies( $request ) {

        $company = Company::select( 'companies.*' );

        $filterObject = self::filter( $request, $company );
        $company = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $company->orderBy( 'created_at', $dir );
                    break;
            }
        }

        $companyCount = $company->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $companies = $company->skip( $offset )->take( $limit )->get();

        if ( $companies ) {
            $companies->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = Company::count();

        $data = [
            'companies' => $companies,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $companyCount : $totalRecord,
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

                $model->whereBetween( 'companies.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'companies.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
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
            $model->where( function( $query ) use ( $request ) {
                $query->where( 'name', 'LIKE', '%' . $request->custom_search . '%' );
            } );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneCompany( $request ) {

        if ( !$request->simple_mode ) {
            $request->merge( [
                'id' => Helper::decode( $request->id ),
            ] );
        }

        $company = Company::find( $request->id );

        if( $company ) {
            $company->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $company );
    }

    public static function createCompany( $request ) {

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'registration_no' => [ 'nullable' ],
            'email' => [ 'nullable' ],
            'phone_number' => [ 'nullable' ],
            'address' => [ 'nullable' ],
            'bank_name' => [ 'nullable' ],
            'account_no' => [ 'nullable' ],
        ] );

        $attributeName = [
            'name' => __( 'company.name' ),
            'registration_no' => __( 'company.registration_no' ),
            'email' => __( 'company.email' ),
            'phone_number' => __( 'company.phone_number' ),
            'address' => __( 'company.address' ),
            'bank_name' => __( 'company.bank_name' ),
            'account_no' => __( 'company.account_no' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createCompany = Company::create( [
                'name' => $request->name,
                'registration_no' => $request->registration_no,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'bank_name' => $request->bank_name,
                'account_no' => $request->account_no,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.companies' ) ) ] ),
        ] );
    }

    public static function updateCompany( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'registration_no' => [ 'nullable' ],
            'email' => [ 'nullable' ],
            'phone_number' => [ 'nullable' ],
            'address' => [ 'nullable' ],
            'bank_name' => [ 'nullable' ],
            'account_no' => [ 'nullable' ],
        ] );

        $attributeName = [
            'name' => __( 'company.name' ),
            'registration_no' => __( 'company.registration_no' ),
            'email' => __( 'company.email' ),
            'phone_number' => __( 'company.phone_number' ),
            'address' => __( 'company.address' ),
            'bank_name' => __( 'company.bank_name' ),
            'account_no' => __( 'company.account_no' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateCompany = Company::find( $request->id );
            $updateCompany->name = $request->name;
            $updateCompany->registration_no = $request->registration_no;
            $updateCompany->email = $request->email;
            $updateCompany->phone_number = $request->phone_number;
            $updateCompany->address = $request->address;
            $updateCompany->bank_name = $request->bank_name;
            $updateCompany->account_no = $request->account_no;
            $updateCompany->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.companies' ) ) ] ),
        ] );
    }

    public static function updateCompanyStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateCompany = Company::find( $request->id );
        $updateCompany->status = $request->status;
        $updateCompany->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.companies' ) ) ] ),
        ] );
    }

    public static function get() {

        $companies = Company::where( 'status', 10 )->get()->toArray();

        return $companies;
    }
}