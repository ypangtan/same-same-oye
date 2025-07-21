<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    UserTopup,
    Wallet,
    WalletTransaction,
    TopupRecord,
};

use Helper;

use Carbon\Carbon;

class WalletService
{
    public static function allWallets( $request ) {

        $wallet = Wallet::with( [ 'user' ] )->select( 'wallets.*' );
        $wallet->leftJoin( 'users', 'users.id', '=', 'wallets.user_id' );

        $filterObject = self::filterWallet( $request, $wallet );
        $wallet = $filterObject['model'];
        $filter = $filterObject['filter'];

        $wallet->orderBy( 'wallets.id', 'DESC' )->orderBy( 'wallets.type', 'ASC' );

        $walletCount = $wallet->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $wallets = $wallet->skip( $offset )->take( $limit )->get();

        if ( $wallets ) {
            $wallets->append( [
                'listing_balance',
                'encrypted_id',
            ] );
        }

        $totalRecord = Wallet::count();

        $data = [
            'wallets' => $wallets,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $walletCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filterWallet( $request, $model ) {

        $filter = false;

        // if ( !empty( $request->user ) ) {
        //     $model->where( 'users.phone_number', 'LIKE', "%$request->user%" );
        //     $filter = true;
        // }

        if ( !empty( $request->wallet ) ) {
            $model->where( 'wallets.type', $request->wallet );
            $filter = true;
        }
        
        if ( !empty( $request->user ) ) {
            $userInput = $request->user;
        
            $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $userInput );
        
            $model->where( function ( $query ) use ( $userInput ) {
                $query->where( 'users.email', 'LIKE', '%' . $userInput . '%' )
                      ->orWhere( 'users.first_name', 'LIKE', '%' . $userInput . '%' )
                      ->orWhere( 'users.last_name', 'LIKE', '%' . $userInput . '%' );
            } );
        
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneWallet( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $wallet = Wallet::with( [ 'user' ] )->find( $request->id );

        if ( $wallet ) {
            $wallet->append( [
                'listing_balance',
                'encrypted_id',
            ] );
        }

        return response()->json( $wallet );
    }

    public static function updateWallet( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'amount' => [ 'required', 'numeric' ],
            'remark' => [ 'required', 'string' ],
            'action' => [ 'required', 'in:topup,deduct' ],
        ] );

        $attributeName = [
            'amount' => __( 'wallet.amount' ),
            'remark' => __( 'wallet.remark' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $wallet = Wallet::lockForUpdate()->find( $request->id );
            self::transact( $wallet, [
                'amount' => $request->action == 'topup' ? $request->amount : ( $request->amount * -1 ),
                'remark' => $request->remark,
                'type' => $wallet->type,
                'transaction_type' => 3,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.wallets' ) ) ] ),
        ] );
    }

    public static function updateWalletMultiple( $request ) {

        $validator = Validator::make( $request->all(), [
            'amount' => [ 'required', 'numeric' ],
            'remark' => [ 'required', 'string' ],
            'action' => [ 'required', 'in:topup,deduct' ],
        ] );

        $attributeName = [
            'amount' => __( 'wallet.amount' ),
            'remark' => __( 'wallet.remark' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            foreach ( $request->ids as $id ) {

                $wallet = Wallet::lockForUpdate()->find( $id );
                self::transact( $wallet, [
                    'amount' => $request->action == 'topup' ? $request->amount : ( $request->amount * -1 ),
                    'remark' => $request->remark,
                    'type' => $wallet->type,
                    'transaction_type' => 3,
                ] );

                DB::commit();
            }

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.wallets' ) ) ] ),
        ] );
    }

    public static function allWalletTransactions( $request ) {

        $transaction = WalletTransaction::with( [ 'user' ] )->select( 'wallet_transactions.*' );
        $transaction->leftJoin( 'users', 'users.id', '=', 'wallet_transactions.user_id' );

        $filterObject = self::filterTransaction( $request, $transaction );
        $transaction = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $transaction->orderBy( 'created_at', $dir );
                    break;
            }
        }

        $transactionCount = $transaction->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $transactions = $transaction->skip( $offset )->take( $limit )->get();

        $subTotal = 0;

        if ( $transactions ) {
            $transactions->append( [
                'converted_remark',
                'listing_amount',
            ] );

            foreach ( $transactions as $transaction ) {
                $subTotal += $transaction->amount;
            }
        }

        $walletTransactionObject = WalletTransaction::select( DB::raw( 'COUNT(*) as total, SUM(amount) AS grand_total' ) )->first();
        $grandTotal = $walletTransactionObject->grand_total;
        $totalRecord = $walletTransactionObject->total;

        $data = [
            'transactions' => $transactions,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $transactionCount : $totalRecord,
            'recordsTotal' => $totalRecord,
            'subTotal' => [
                Helper::numberFormat( $subTotal, 2 )
            ],
            'grandTotal' => [
                Helper::numberFormat( $grandTotal, 2 )
            ],
        ];

        return response()->json( $data );
    }

    private static function filterTransaction( $request, $model ) {

        $filter = false;

        if (  !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );

                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'wallet_transactions.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'wallet_transactions.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }

            $filter = true;
        }

        if ( !empty( $request->phone_number ) ) {
            $userInput = $request->phone_number;
        
            $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $userInput );
        
            $model->where( function ( $query ) use ( $normalizedPhone, $userInput ) {
                $query->where( 'users.phone_number', 'LIKE', "%$normalizedPhone%" );
            } );
        
            $filter = true;
        }

        if ( !empty( $request->user ) ) {
            $userInput = $request->user;
            $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $userInput );
        
            $model->where(function ( $query ) use ( $normalizedPhone, $userInput ) {
                $query->where( 'users.email', 'LIKE', "%$normalizedPhone%" )
                    ->orWhereRaw( "CONCAT(users.first_name, ' ', users.last_name) LIKE ?", [ "%$userInput%" ] )
                    ->orWhere( 'users.first_name', 'LIKE', "%$userInput%" )
                    ->orWhere( 'users.last_name', 'LIKE', "%$userInput%" );
            });
        
            $filter = true;
        }
        
        if ( !empty( $request->wallet ) ) {
            $model->where( 'wallet_transactions.type', $request->wallet );
            $filter = true;
        }

        if ( !empty( $request->transaction_type ) ) {
            $model->where( 'wallet_transactions.transaction_type', $request->transaction_type );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function getWalletTransactions( $request ) {

        $walletTransactions = WalletTransaction::where( 'user_id', auth()->user()->id )
            ->orderBy( 'created_at', 'DESC' );

        if( $request->type ) {
            $walletTransactions->whereHas('wallet', function ($query) {
                $query->where('type', 2);
            });
        }else{
            $walletTransactions->whereHas('wallet', function ($query) {
                $query->where('type', 1);
            });
        }

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

    public static function topup( $request ) {

        $validator = Validator::make( $request->all(), [
            'topup_amount' => [ 'required', 'numeric', 'min:10' ],
            'payment_method' => [ 'required', 'in:1' ],
        ] );

        $attributeName = [
            'topup_amount' => __( 'wallet.topup_amount' ),
            'payment_method' => __( 'wallet.payment_method' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $user = auth()->user();
            $reference = 'TPP-' . now()->format('YmdHis');

            $topupRecord = TopupRecord::create( [
                'user_id' => $user->id,
                'wallet_transaction_id' => null,
                'reference' => $reference,
                'amount' => $request->topup_amount,
                'payment_attempt' => 1,
                'status' => 10,
            ] );

            $data = [
                'TransactionType' => 'SALE',
                'PymtMethod' => 'ANY',
                'ServiceID' => config('services.eghl.merchant_id'),
                'PaymentID' => $topupRecord->reference . '-' . $topupRecord->payment_attempt,
                'OrderNumber' => $topupRecord->reference,
                'PaymentDesc' => 'TOPUP',
                'MerchantName' => 'IFei',
                'MerchantReturnURL' => config('services.eghl.staging_callabck_url'),
                'MerchantApprovalURL' => config('services.eghl.staging_success_url'),
                'MerchantUnApprovalURL' => config('services.eghl.staging_failed_url'),
                'MerchantCallbacklURL' => config('services.eghl.staging_fallback_url'),
                'Amount' => Helper::numberFormatV2($request->topup_amount, 2),
                'CurrencyCode' => 'MYR',
                'CustIP' => request()->ip(),
                'CustName' => $user->username ?? 'IFei Guest',
                'HashValue' => '',
                'CustEmail' => $user->email ?? 'ifeiguest@gmail.com',
                'CustPhone' => $user->phone_number,
                'MerchantTermsURL' => null,
                'LanguageCode' => 'en',
                'PageTimeout' => '780',
            ];

            $data['HashValue'] = Helper::generatePaymentHash($data);
            $url2 = config('services.eghl.test_url') . '?' . http_build_query($data);
            
            $topupRecord->payment_url = $url2;
            $topupRecord->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollBack();

            abort( 500, $th->getMessage() . ' in line: ' . $th->getLine() );
        }

        return response()->json( [
            'message_key' => 'topup_initiate',
            'payment_url' => $url2,
        ] );
    }

    public static function transact( Wallet $wallet, $data ) {

        $openingBalance = $wallet->balance;

        $wallet->balance += $data['amount'];
        $wallet->save();

        $createWalletTransaction = WalletTransaction::create( [
            'user_wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'opening_balance' => $openingBalance,
            'amount' => $data['amount'],
            'closing_balance' => $wallet->balance,
            'remark' => isset( $data['remark'] ) ? $data['remark'] : null,
            'type' => $data['type'],
            'transaction_type' => $data['transaction_type'],
            'invoice_id' => isset( $data['invoice_id'] ) ? $data['invoice_id'] : null,
        ] );

        return $createWalletTransaction;
    }

    public static function getWallet( $request ) {

        $wallet = Wallet::where( 'user_id', auth()->user()->id )->get();

        if ( $wallet ) {
            $wallet->append( [
                'listing_balance',
                'formatted_type',
                'encrypted_id',
            ] );
        }

        return response()->json([
            'message' => '',
            'message_key' => 'get_wallet_success',
            'data' => $wallet,
        ]);
    }
}
