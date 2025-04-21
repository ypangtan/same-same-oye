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
    VoucherUsage,
    Booking,
    FileManager,
    VendingMachine,
    VendingMachineStock,
    Cart,
    CartMeta,
    Order,
    OrderMeta,
    Voucher,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class VoucherUsageService
{

    public static function createVoucherUsage( $request ) {

        $validator = Validator::make($request->all(), [
            'voucher' => ['required'],
            'users' => ['required'],
            'quantity' => ['nullable', 'min:1'],
        ]);
        
        $attributeName = [
            'voucher' => __('voucher_usage.title'),
            'user' => __('voucher_usage.description'),
            'expired_date' => __('voucher_usage.image'),
            'redeem_from' => __('voucher_usage.code'),
            'secret_code' => __('voucher_usage.price'),
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

            for( $x=0; $x < $request->quantity; $x++ ){

                foreach( $users as $user ){
                    $VoucherUsage = VoucherUsage::create([
                        'user_id' => $user,
                        'voucher_id' => $voucher->id,
                        'status' => 10,
                        'total_left' => 1,
                        'used_at' => null,
                        'secret_code' => null,
                        'expired_date' => Carbon::now()->addDays($voucher->validity_days),
                    ]);
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.voucher_usages' ) ) ] ),
        ] );
    }

    public static function allVoucherUsages( $request ) {
        $voucher_usages = VoucherUsage::with( ['user','voucher','order'] )->select( 'voucher_usages.*');

        $filterObject = self::filter( $request, $voucher_usages );
        $voucher = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $voucher->orderBy( 'voucher_usages.created_at', $dir );
                    break;
                case 2:
                    $voucher->orderBy( 'voucher_usages.title', $dir );
                    break;
                case 3:
                    $voucher->orderBy( 'voucher_usages.description', $dir );
                    break;
            }
        }

        $voucherCount = $voucher->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $voucher_usages = $voucher->skip( $offset )->take( $limit )->get();

        if ( $voucher_usages ) {
            $voucher_usages->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = VoucherUsage::count();

        $data = [
            'voucher_usages' => $voucher_usages,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $voucherCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );

    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->id ) ) {
            $model->where( 'voucher_usages.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->user)) {
            $model->whereHas('user', function ($query) use ($request) {
                $query->where('users.phone_number', 'LIKE', '%' . $request->user . '%');
            });
            $filter = true;
        }

        if (!empty($request->orders)) {
            $model->whereHas('order', function ($query) use ($request) {
                $query->where('orders.reference', 'LIKE', '%' . $request->orders . '%');
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

        if ( !empty( $request->vending_machine_id ) ) {
            $vendingMachineVoucherUsages = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'voucher_id' );
            $model->whereNotIn( 'id', $vendingMachineVoucherUsages );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function updateVoucherUsageStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateVoucherUsage = VoucherUsage::find( $request->id );
            $updateVoucherUsage->status = $updateVoucherUsage->status == 10 ? 20 : 10;

            $updateVoucherUsage->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'voucher' => $updateVoucherUsage,
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