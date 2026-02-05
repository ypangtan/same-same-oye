<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Hash,
    Storage,
    Validator,
};

use Illuminate\Validation\Rules\Password;

use App\Models\{
    FileManager,
    Country,
    User,
    Role as RoleModel
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class CountryService
{
    public static function allCountries( $request ) {

        $country = Country::select( 'countries.*' );

        $filterObject = self::filter( $request, $country );
        $country = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            switch ( $request->input( 'order.0.column' ) ) {
                default:
                    $country->orderBy( 'created_at', $dir );
                    break;
            }
        }

        $countryCount = $country->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $countries = $country->skip( $offset )->take( $limit )->get();

        if ( $countries ) {
            $countries->append( [
                'encrypted_id',
            ] );
        }

        if( !empty( $request->type ) ) {
            $totalRecord = Country::where( 'type_id', $request->type )->count();
        } else {
            $totalRecord = Country::count();
        }

        $data = [
            'countries' => $countries,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $countryCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_at ) ) {
            if ( str_contains( $request->created_at, 'to' ) ) {
                $dates = explode( ' to ', $request->created_at );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'countries.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_at );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'countries.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->country_name ) ) {
            $model->where( function( $q ) use ( $request ) {
                $q->where( 'country_name', 'LIKE', '%' . $request->country_name . '%' );
            } );
            $filter = true;
        }

        if ( !empty( $request->nationality ) ) {
            $model->where( function( $q ) use ( $request ) {
                $q->where( 'nationality', 'LIKE', '%' . $request->nationality . '%' );
            } );
            $filter = true;
        }

        if( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneCountry( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $country = Country::find( $request->id );

        if( $country ) {
            $country->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $country );
    }

    // api
    public static function getCountries( $request ) {

        $per_page = $request->input( 'per_page', 10 );

        $countries = Country::orderBy( 'created_at', 'DESC' )
            ->paginate( $per_page );

        if ( $countries ) {
            $countries->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $countries );
    }
}