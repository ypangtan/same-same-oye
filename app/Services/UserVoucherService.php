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
    UserVoucher,
    Booking,
    FileManager,
    VendingMachine,
    VendingMachineStock,
    Cart,
    CartMeta,
    Order,
    OrderMeta,
    Voucher,
    VoucherUsage,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class UserVoucherService
{

    public static function createUserVoucher( $request ) {

        $validator = Validator::make($request->all(), [
            'voucher' => ['required'],
            'users' => ['required'],
            'quantity' => ['nullable', 'min:1'],
        ]);
        
        $attributeName = [
            'voucher' => __('user_voucher.title'),
            'user' => __('user_voucher.description'),
            'expired_date' => __('user_voucher.image'),
            'redeem_from' => __('user_voucher.code'),
            'secret_code' => __('user_voucher.price'),
        ];
        
        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }
        
        $validator->after(function ($validator) use ($request) {
            $voucher = Voucher::where('id', $request->voucher)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNull('start_date')
                            ->orWhere('start_date', '<=', Carbon::now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('expired_date')
                            ->orWhere('expired_date', '>=', Carbon::now());
                    });
                })
                ->where('status', 10)
                ->first();
        
            if (!$voucher) {
                $validator->errors()->add('voucher', __('The selected voucher is invalid or expired.'));
            }else {

                $voucherUsages = VoucherUsage::where( 'voucher_id', $voucher->id )->get();

                // if ( $voucherUsages->count() > $voucher->usable_amount ) {
                //     $validator->errors()->add('voucher', __('Voucher usage has reached its limit'));
                // }

                if ( $voucher->total_claimable <= 0 ) {
                    $validator->errors()->add('voucher', __('Voucher has fully claimed'));
                }

                if ( $voucher->total_claimable <= $request->quantity + count( explode( ',', $request->users ) ) ) {
                    $validator->errors()->add('voucher', __('Please replenish voucher quantity'));
                }
            }
        });
        
        // Set attribute names for validation messages
        $validator->setAttributeNames($attributeName);
        
        // Perform validation
        $validator->validate();

        DB::beginTransaction();
        
        try {

            $users = explode( ',', $request->users );

            $voucher = Voucher::find( $request->voucher );
            $deductQuantity = 0;

            for( $x=0; $x < $request->quantity; $x++ ){

                foreach( $users as $user ){
                    $userVoucher = UserVoucher::create([
                        'user_id' => $user,
                        'voucher_id' => $voucher->id,
                        'status' => 10,
                        'total_left' => 1,
                        'used_at' => null,
                        'secret_code' => strtoupper( \Str::random( 8 ) ),
                        'expired_date' => Carbon::now()->addDays($voucher->validity_days),
                    ]);

                    $deductQuantity ++;
                }

            }

            $voucher->total_claimable -= $deductQuantity;
            $voucher->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.user_vouchers' ) ) ] ),
        ] );
    }

    public static function allUserVouchers( $request ) {

        $user_vouchers = UserVoucher::with( ['user','voucher'] )->select( 'user_vouchers.*');
        $user_vouchers->leftJoin( 'users', 'users.id', '=', 'user_vouchers.user_id' );

        $filterObject = self::filter( $request, $user_vouchers );
        $voucher = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $voucher->orderBy( 'user_vouchers.created_at', $dir );
                    break;
                case 2:
                    $voucher->orderBy( 'user_vouchers.title', $dir );
                    break;
                case 3:
                    $voucher->orderBy( 'user_vouchers.description', $dir );
                    break;
            }
        }

            $voucherCount = $voucher->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $user_vouchers = $voucher->skip( $offset )->take( $limit )->get();

            if ( $user_vouchers ) {
                $user_vouchers->append( [
                    'encrypted_id',
                ] );

                foreach ( $user_vouchers as $key => $user_voucher) {
                    $user_voucher->voucher->append( [
                        'encrypted_id',
                    ] );
                }

            }

            $totalRecord = UserVoucher::count();

            $data = [
                'user_vouchers' => $user_vouchers,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $voucherCount : $totalRecord,
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

                $model->whereBetween( 'user_vouchers.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'user_vouchers.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'user_vouchers.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if ( !empty( $request->user ) ) {
            $userInput = $request->user;
            $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $userInput );
        
            $model->where(function ( $query ) use ( $normalizedPhone, $userInput ) {
                $query->where( 'users.phone_number', 'LIKE', "%$normalizedPhone%" )
                    ->orWhereRaw( "CONCAT(users.first_name, ' ', users.last_name) LIKE ?", [ "%$userInput%" ] )
                    ->orWhere( 'users.first_name', 'LIKE', "%$userInput%" )
                    ->orWhere( 'users.last_name', 'LIKE', "%$userInput%" );
            });
        
            $filter = true;
        }

        if (!empty($request->title)) {
            $model->whereHas('voucher', function ($query) use ($request) {
                $query->where('vouchers.title', 'LIKE', '%' . $request->title . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->voucher_type ) ) {
            $model->whereHas('voucher', function ($query) use ($request) {
                $query->where( 'type', $request->voucher_type );
            });
            $filter = true;
        }

        if ( !empty( $request->discount_type ) ) {
            $model->whereHas('voucher', function ($query) use ($request) {
                $query->where( 'discount_type', $request->discount_type );
            });
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

        if ( !empty( $request->seceret_code ) ) {
            $model->where( 'user_vouchers.seceret_code', 'LIKE', '%' . $request->seceret_code . '%' );
            $filter = true;
        }

        if ( !empty( $request->vending_machine_id ) ) {
            $vendingMachineUserVouchers = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'voucher_id' );
            $model->whereNotIn( 'id', $vendingMachineUserVouchers );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function updateUserVoucherStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateUserVoucher = UserVoucher::find( $request->id );
            $updateUserVoucher->status = $updateUserVoucher->status == 10 ? 20 : 10;

            if( $updateUserVoucher->status == 20 ){
                $updateUserVoucher->used_at = now();
            }

            $updateUserVoucher->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'voucher' => $updateUserVoucher,
                    'message_key' => 'update_voucher_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_voucher_failed',
            ], 500 );
        }
    }

}