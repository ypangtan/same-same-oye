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
    LuckyDrawImportHistory,
    LuckyDrawReward,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;

class LuckyDrawRewardService
{
    public static function allLuckyDrawRewards( $request ) {

        $luckyDrawReward = LuckyDrawReward::select( 'lucky_draw_rewards.*' );

        $filterObject = self::filter( $request, $luckyDrawReward );
        $luckyDrawReward = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $luckyDrawReward->orderBy( 'created_at', $dir );
                    break;
                case 3:
                    $luckyDrawReward->orderBy( 'customer_member_id', $dir );
                    break;
                case 4:
                    $luckyDrawReward->orderBy( 'name', $dir );
                    break;
                case 5:
                    $luckyDrawReward->orderBy( 'quantity', $dir );
                    break;
            }
        }

        $luckyDrawRewardCount = $luckyDrawReward->count();

        $limit = $request->length;
        $offset = $request->start;

        $luckyDrawRewards = $luckyDrawReward->skip( $offset )->take( $limit )->get();

        if ( $luckyDrawRewards ) {
            $luckyDrawRewards->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = LuckyDrawReward::count();

        $data = [
            'lucky_draw_rewards' => $luckyDrawRewards,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $luckyDrawRewardCount : $totalRecord,
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

                $model->whereBetween( 'lucky_draw_rewards.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'lucky_draw_rewards.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->customer_member_id ) ) {
            $model->where( 'customer_member_id', 'LIKE', '%' . $request->customer_member_id . '%' );
            $filter = true;
        }

        if ( !empty( $request->reward_type ) ) {
            $model->where( 'reward_type', $request->reward_type );
            $filter = true;
        }

        if ( !empty( $request->voucher_id ) ) {
            $model->where( 'voucher_id', $request->voucher_id );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneLuckyDrawReward( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $lucky_draw_reward = LuckyDrawReward::find( $request->id );

        if( $lucky_draw_reward ) {
            $lucky_draw_reward->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $lucky_draw_reward );
    }

    public static function createLuckyDrawReward( $request ) {

        $validator = Validator::make( $request->all(), [
            'customer_member_id' => [ 'required', 'unique:lucky_draw_rewards,customer_member_id' ],
            'name' => [ 'required' ],
            'quantity' => [ 'required' ],
            'reference_id' => [ 'required' ],
        ] );

        $attributeName = [
            'customer_member_id' => __( 'lucky_draw_reward.customer_member_id' ),
            'name' => __( 'lucky_draw_reward.name' ),
            'quantity' => __( 'lucky_draw_reward.quantity' ),
            'reference_id' => __( 'lucky_draw_reward.reference_id' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $reference = '';

            $decode = json_decode( $request->reference_id );
            $reference = implode( ',', array_map( 'trim', $decode ) );

            $createluckyDrawReward = LuckyDrawReward::create( [
                'customer_member_id' => $request->customer_member_id,
                'name' => $request->name,
                'quantity' => count( $decode ),
                'reference_id' => $reference,
            ] );
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.lucky_draw_rewards' ) ) ] ),
        ] );
    }

    public static function updateLuckyDrawReward( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'customer_member_id' => [ 'required', 'unique:lucky_draw_rewards,customer_member_id,' . $request->id ],
            'name' => [ 'required' ],
            'quantity' => [ 'required' ],
            'reference_id' => [ 'required' ],
        ] );

        $attributeName = [
            'customer_member_id' => __( 'lucky_draw_reward.customer_member_id' ),
            'name' => __( 'lucky_draw_reward.name' ),
            'quantity' => __( 'lucky_draw_reward.quantity' ),
            'reference_id' => __( 'lucky_draw_reward.reference_id' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $reference = '';

            $decode = json_decode( $request->reference_id );

            $reference = implode( ',', array_map( 'trim', $decode ) );

            $updatelucky_draw_reward = LuckyDrawReward::find( $request->id );
            $updatelucky_draw_reward->customer_member_id = $request->customer_member_id;
            $updatelucky_draw_reward->name = $request->name;
            $updatelucky_draw_reward->quantity = count( $decode );
            $updatelucky_draw_reward->reference_id = $reference;
            $updatelucky_draw_reward->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.lucky_draw_rewards' ) ) ] ),
        ] );
    }

    public static function updateLuckyDrawRewardStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updatelucky_draw_reward = LuckyDrawReward::find( $request->id );
        $updatelucky_draw_reward->status = $request->status;
        $updatelucky_draw_reward->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.lucky_draw_rewards' ) ) ] ),
        ] );
    }

    public static function importLuckyDrawReward($request) {

        $validator = Validator::make($request->all(), [
            'file' => ['required', 'mimes:xlsx,xlsm'],
        ]);

        $attributeName = [
            'file' => __('lucky_draw_reward.file'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        $file = $request->file('file');
        $path = $file->store('imports/v1', ['disk' => 'public']);

        $newPath = $path;
        if ($file->getClientOriginalExtension() == 'xlsm') {
            $newPath = str_replace('.xlsx', '.xlsm', $path);
            Storage::disk('public')->move($path, $newPath);
        }

        LuckyDrawImportHistory::create([
            'uploaded_by' => auth()->user()->id,
            'name' => $file->getClientOriginalName(),
            'hash_name' => basename($newPath),
            'file' => $newPath,
        ]);

        $collection = (new FastExcel)->sheet(1)->withoutHeaders()->import(storage_path('app/public/' . $newPath));

        $errors = [];
        $data = [];
        $previousCustomerMemberId = null;

        DB::beginTransaction();
        
        foreach ($collection as $key => $row) {

            if ( $key <= 0 ) {
                continue;
            }

            if ($row[0] != '') {
                $previousCustomerMemberId = $row[0];
                $data[ $row[0] ] = [
                    'customer_member_id' => $row[0],
                    'name' => $row[1],
                    'quantity' => $row[2],
                    'reference' => [
                        $row[3],
                    ],
                ];
            } else {
                $data[ $previousCustomerMemberId ]['reference'][] = $row[3];
            }
        }

        foreach ( $data as $key => $v ) {
            $existingLuckyDrawReward = LuckyDrawReward::where( 'customer_member_id', $v['customer_member_id'] )->first();

            if( $existingLuckyDrawReward ) {
                $errors[] = $v['customer_member_id'];
                continue;
            }

            $reference = implode( ',', array_map( 'trim', $v['reference'] ) );

            $createluckyDrawReward = LuckyDrawReward::create( [
                'customer_member_id' => $v['customer_member_id'],
                'name' => $v['name'],
                'quantity' => count( $v['reference'] ),
                'reference_id' => $reference,
            ] );

        }

        if (empty($errors)) {

            DB::commit();

            return response()->json([
                'message' => __('template.x_imported', ['title' => Str::singular(__('template.lucky_draw_rewards'))]),
            ]);
        } else {

            DB::rollBack();

            $error = implode( ',', array_map( 'trim', $errors ) );
            return response()->json([
                'message' => __('template.x_error_imported', ['error' => $error ]),
                'errors' => $errors,
            ]);
        }
    }

    public static function importLuckyDrawRewardV2($request) {

        try{
            $validator = Validator::make($request->all(), [
                'file' => ['required', 'mimes:xlsx,xlsm'],
            ]);

            $attributeName = [
                'file' => __('lucky_draw_reward.file'),
            ];

            foreach ($attributeName as $key => $aName) {
                $attributeName[$key] = strtolower($aName);
            }

            $validator->setAttributeNames($attributeName)->validate();

            $file = $request->file('file');
            $path = $file->store('imports/v2', ['disk' => 'public']);

            $newPath = $path;
            if ($file->getClientOriginalExtension() == 'xlsm') {
                $newPath = str_replace('.xlsx', '.xlsm', $path);
                Storage::disk('public')->move($path, $newPath);
            }

            LuckyDrawImportHistory::create([
                'uploaded_by' => auth()->user()->id,
                'name' => $file->getClientOriginalName(),
                'hash_name' => basename($newPath),
                'file' => $newPath,
            ]);

            $collection = (new FastExcel)->sheet(1)->withoutHeaders()->import(storage_path('app/public/' . $newPath));

            $errors = [];
            $data = [];
            $previousCustomerMemberId = [];

            DB::beginTransaction();
            
            foreach ($collection as $key => $row) {
                if ( $key <= 0 ) {
                    continue;
                }

                if ($row[0] != '') {
                    $previousCustomerMemberId = $row[0];
                    $data[ $row[0] ] = [
                        'customer_member_id' => $row[0],
                        'name' => $row[1],
                        'quantity' => $row[2],
                        'reference' => [
                            $row[3],
                        ],
                    ];
                } else {
                    $data[ $previousCustomerMemberId ]['reference'][] = $row[3];
                }
            }

            foreach ( $data as $key => $row ) {
                $reference = implode( ',', array_map( 'trim', $row['reference'] ) );
                $existingLuckyDrawReward = LuckyDrawReward::where( 'customer_member_id', $row['customer_member_id'] )->first();

                if( $existingLuckyDrawReward ) {
                    $existingLuckyDrawReward->name = $row['name'];
                    $existingLuckyDrawReward->quantity = count( $row['reference'] );
                    $existingLuckyDrawReward->reference_id = $reference;
                } else {
                    $createluckyDrawReward = LuckyDrawReward::create( [
                        'customer_member_id' => $row['customer_member_id'],
                        'name' => $row['name'],
                        'quantity' => count( $row['reference'] ),
                        'reference_id' => $reference,
                    ] );
                }
            }

            if (empty($errors)) {

                DB::commit();

                return response()->json([
                    'message' => __('template.x_imported', ['title' => Str::singular(__('template.lucky_draw_rewards'))]),
                ]);
            } else {
                return response()->json([
                    'message' => __('template.x_partial_imported', ['title' => Str::singular(__('template.lucky_draw_rewards'))]),
                    'errors' => $errors,
                ]);
            }
        } catch( \Throwable $th ) {
            
            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }
    }

    public static function searchLuckyDrawRewards( $request ) {

        $lucky_draw_reward = LuckyDrawReward::where( 'status', '10' )
            ->where( 'customer_member_id', $request->customer_member_id )
            ->get();

        return response()->json( [
            'data' => $lucky_draw_reward,
        ] );
    }
}