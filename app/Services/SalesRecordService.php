<?php

namespace App\Services;

use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Hash,
    Validator,
    Mail,
    Crypt,
    Storage,
};

use App\Mail\EnquiryEmail;
use App\Mail\OtpMail;

use Illuminate\Validation\Rules\Password;
use Rap2hpoutre\FastExcel\FastExcel;

use App\Models\{
    SalesRecord,
    User,
    Wallet,
    WalletTransaction,
    Option,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class SalesRecordService
{
    public static function allSalesRecords( $request ) {

        $user = SalesRecord::select( 'sales_records.*' )->orderBy( 'created_at', 'DESC' );

        $filterObject = self::filter( $request, $user );
        $user = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'sales_record.0.column' ) != 0 ) {
            $dir = $request->input( 'sales_record.0.dir' );
            switch ( $request->input( 'sales_record.0.column' ) ) {
                case 1:
                    $user->orderBy( 'created_at', $dir );
                    break;
                case 2:
                    $user->orderBy( 'username', $dir );
                    break;
                case 3:
                    $user->orderBy( 'email', $dir );
                    break;
            }
        }

        $userCount = $user->count();

        $limit = $request->length;
        $offset = $request->start;

        $salesRecords = $user->skip( $offset )->take( $limit )->get();

        if ( $salesRecords ) {
            $salesRecords->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = SalesRecord::count();

        $data = [
            'sales_records' => $salesRecords,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $userCount : $totalRecord,
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

                $model->whereBetween( 'sales_records.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->registered_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'sales_records.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->username ) ) {
            $model->where( 'username', 'LIKE', '%' . $request->username . '%' );
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

        if ( !empty( $request->title ) ) {
            $model->where( 'phone_number', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->customer_name ) ) {
            $model->where( 'customer_name', 'LIKE', '%' . $request->customer_name . '%' );
            $filter = true;
        }

        if ( !empty( $request->reference ) ) {
            $model->where( 'reference', 'LIKE', '%' . $request->reference . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'email', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneSalesRecord( $request ) {

        $user = SalesRecord::find( Helper::decode( $request->id ) );

        return response()->json( $user );
    }

    public static function createSalesRecord( $request ) {

        $validator = Validator::make( $request->all(), [
            'fullname' => [ 'nullable' ],
        ] );

        $attributeName = [
            'username' => __( 'sales_record.username' ),
            'email' => __( 'sales_record.email' ),
            'fullname' => __( 'sales_record.fullname' ),
            'password' => __( 'sales_record.password' ),
            'phone_number' => __( 'sales_record.phone_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createSalesRecordObject = [
                'order_id' => $request->order_id ?? null,
                'customer_name' => $request->customer_name ?? null,
                'facebook_name' => $request->facebook_name ?? null,
                'facebook_url' => $request->facebook_url ?? null,
                'live_id' => $request->live_id ?? null,
                'product_metas' => $request->product_metas ?? null,
                'total_price' => $request->total_price ?? null,
                'payment_method' => $request->payment_method ?? null,
                'handler' => $request->handler ?? null,
                'remarks' => $request->remarks ?? null,
                'reference' => $request->reference ?? null,
                'remarks' => $request->remarks ?? null,
                'status' => 10,
            ];

            $createSalesRecord = SalesRecord::create( $createSalesRecordObject );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.sales_records' ) ) ] ),
        ] );
    }

    public static function updateSalesRecord( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'fullname' => [ 'nullable' ],
        ] );

        $attributeName = [
            'username' => __( 'sales_record.username' ),
            'email' => __( 'sales_record.email' ),
            'fullname' => __( 'sales_record.fullname' ),
            'password' => __( 'sales_record.password' ),
            'phone_number' => __( 'sales_record.phone_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateSalesRecord = SalesRecord::find( $request->id );
            $updateSalesRecord->order_id = $request->order_id;
            $updateSalesRecord->customer_name = $request->customer_name;
            $updateSalesRecord->facebook_name = $request->facebook_name;
            $updateSalesRecord->facebook_url = $request->facebook_url;
            $updateSalesRecord->live_id = $request->live_id;
            $updateSalesRecord->product_metas = $request->product_metas;
            $updateSalesRecord->total_price = $request->total_price;
            $updateSalesRecord->payment_method = $request->payment_method;
            $updateSalesRecord->handler = $request->handler;
            $updateSalesRecord->remarks = $request->remarks;
            $updateSalesRecord->reference = $request->reference;
            $updateSalesRecord->remarks = $request->remarks;

            if ( !empty( $request->password ) ) {
                $updateSalesRecord->password = Hash::make( $request->password );
            }

            $updateSalesRecord->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.sales_records' ) ) ] ),
        ] );
    }

    public static function updateSalesRecordStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateSalesRecord = SalesRecord::find( $request->id );
        $updateSalesRecord->status = $request->status;
        $updateSalesRecord->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.sales_records' ) ) ] ),
        ] );
    }
    
    public static function importSalesRecords( $request ) {

        $validator = Validator::make( $request->all(), [
            'file' => [ 'required', 'mimes:xlsx,xlsm' ],
        ] );

        $attributeName = [
            'file' => __( 'phone_number.file' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $file = $request->file( 'file' );
        $path = $file->store( 'imports', [ 'disk' => 'public' ] );

        $newPath = $path;
        if ( $file->getClientOriginalExtension() == 'xlsm' ) {
            $newPath = str_replace( '.xlsx', '.xlsm', $path );
            Storage::disk( 'public' )->move( $path, $newPath );
        }

        ( new FastExcel )->import( $file, function ( $line ) {
            SalesRecord::create([
                'customer_name' => isset( $line['customer_name'] ) ? $line['customer_name'] : null ,
                'reference' => isset( $line['reference'] ) ? $line['reference'] : null ,
                'total_price' => isset( $line['total_price'] ) ? $line['total_price'] : null ,
            ]);
        });

        $errors = [];

        if ( empty( $errors ) ) {
         
            return response()->json( [
                'status' => 200,
                'message' => __( 'template.x_imported', [ 'title' => Str::singular( __( 'template.phone_numbers' ) ) ] ),
            ] );
        } else {

            return response()->json( [
                'message' => __( 'template.x_partial_imported', [ 'title' => Str::singular( __( 'template.phone_numbers' ) ) ] ),
                'errors' => $errors,
            ] );
        }
    }

    public static function getPoints( $request ) {
        
        $wallet = Wallet::where( 'user_id', auth()->user()->id )->first();

        if ( $wallet ) {
            $wallet->append( [
                'listing_balance',
                'formatted_type',
                'encrypted_id',
            ] );
        }

        return response()->json([
            'message' => '',
            'message_key' => 'get_points_success',
            'points' => $wallet,
        ]);
    }

    public static function redeemPoints( $request ) {
        
        $user = auth()->user();

        $validator = Validator::make( $request->all(), [
            'reference' => [
                    'required',
                    function ( $attribute, $value, $fail ) use ( $request ) {
                        $exists = SalesRecord::where( 'reference', $value )
                            ->where( 'total_price', $request->amount )
                            ->where( 'status', 10 )
                            ->where( 'customer_name', auth()->user()->fullname )
                            ->orWhere( 'facebook_name', auth()->user()->fullname )
                            ->exists();

                        if ( ! $exists ) {
                            $fail( __( 'Sorry, we cant found your order.' ) );
                        }
                    },
            ],
            'customer_name' => [ 'nullable', 'string' ],
            'amount' => [ 'required', 'numeric', 'min:1' ],
        ] );

        $attributeName = [
            'reference' => __( 'sales_record.reference' ),
            'customer_name' => __( 'sales_record.customer_name' ),
            'amount' => __( 'sales_record.amount' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $user = auth()->user();

            $salesRecord = SalesRecord::where( 'reference', $request->reference )
            ->where( 'total_price', $request->amount )
            ->where( 'status', 10 )
            ->where( 'customer_name', $user->fullname )
            ->orWhere( 'facebook_name', $user->fullname )
            ->first();

            $wallet = Wallet::lockForUpdate()->where( 'user_id', $user->id )->first();
            $conversionRate = Option::where( 'option_name', 'CONVERTION_RATE' )->first();
            $conversionRate = ( $conversionRate && (float) $conversionRate->option_value > 0 )
                ? (float) $conversionRate->option_value
                : 1;

            WalletService::transact( $wallet, [
                'amount' => $salesRecord->total_price * $conversionRate,
                'remark' => 'Points Redeemed',
                'type' => $wallet->type,
                'transaction_type' => 12,
            ] );

            $salesRecord->status = 21;
            $salesRecord->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();
            
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'oops_error_encountered',
                'points' => $wallet,
            ], 500 );
        }

        return response()->json([
            'message' => __( 'template.x_redeemed', [ 'title' =>__( 'template.wallets' ) ] ),
            'message_key' => 'ponits_redeemed',
            'points' => $wallet,
        ]);
       
    }

    public static function getPointsRedeemHistory( $request ) {

        $walletTransactions = WalletTransaction::where( 'user_id', auth()->user()->id )
            ->orderBy( 'created_at', 'DESC' )->where('type', 1);

        $walletTransactions = $walletTransactions->paginate( empty( $request->per_page ) ? 10 : $request->per_page );

        foreach ( $walletTransactions->items() as $wt ) {

            $wt->makeHidden( [
                'type',
                'opening_balance',
                'closing_balance',
                'updated_at',
            ] );

            $wt->append( [
                'converted_remark',
                'display_transaction_type',
            ] );
        }

        return response()->json( $walletTransactions );
    }

    public static function getConversionRate( $request ) {

        $conversionRate = Option::where( 'option_name', 'CONVERTION_RATE' )->first();
        // $conversionRate = ( $conversionRate && (float) $conversionRate->option_value > 0 )
        //     ? (float) $conversionRate->option_value
        //     : 1;

        $conversionRate->rate = $conversionRate->option_value;

        return response()->json([
            'message' => 'Get Conversion Rate Success',
            'message_key' => 'get_conversion_rate_success',
            'conversion_rate' => $conversionRate,
        ]);
    }
}