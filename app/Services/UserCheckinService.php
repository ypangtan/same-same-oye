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
    UserCheckin,
    User,
    CheckinReward,
    Booking,
    FileManager,
    VendingMachine,
    VendingMachineStock,
    UserVoucher,
    Voucher,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class UserCheckinService
{

    public static function createUserCheckin( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'user' => [ 'required', 'exists:users,id' ],
                'checkin_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    $alreadyCheckedIn = UserCheckin::where('user_id', $request->user)
                        ->whereDate('checkin_date', $value)
                        ->exists();

                    if ($alreadyCheckedIn) {
                        $fail(__('The user has already checked in on this date.'));
                    }
                },
            ],
        ] );

        $attributeName = [
            'title' => __( 'user_checkin.title' ),
            'description' => __( 'user_checkin.description' ),
            'image' => __( 'user_checkin.image' ),
            'code' => __( 'user_checkin.code' ),
            'ingredients' => __( 'user_checkin.ingredients' ),
            'nutritional_values' => __( 'user_checkin.nutritional_values' ),
            'price' => __( 'user_checkin.price' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {

            $user = User::find( $request->user );
            $user->total_check_in += 1;
            $user->save();

            $userCheckinCreate =  UserCheckin::create([
                'user_id' => $user->id,
                'checkin_date' => now(),
                'status' => 10,
            ]);

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.user_checkins' ) ) ] ),
        ] );
    }
    
    public static function updateUserCheckin( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'user' => [ 'required', 'exists:users,id' ],
                'checkin_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    $alreadyCheckedIn = UserCheckin::where('user_id', $request->user)
                        ->whereDate('checkin_date', $value)
                        ->exists();

                    if ($alreadyCheckedIn) {
                        $fail(__('The user has already checked in on this date.'));
                    }
                },
            ],
        ] );

        $attributeName = [
            'title' => __( 'user_checkin.title' ),
            'description' => __( 'user_checkin.description' ),
            'image' => __( 'user_checkin.image' ),
            'code' => __( 'user_checkin.code' ),
            'ingredients' => __( 'user_checkin.ingredients' ),
            'nutritional_values' => __( 'user_checkin.nutritional_values' ),
            'price' => __( 'user_checkin.price' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateUserCheckin = UserCheckin::find( $request->id );
            $updateUserCheckin->checkin_date = $request->checkin_date;
            $updateUserCheckin->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.user_checkins' ) ) ] ),
        ] );
    }

    public static function allUserCheckins( $request ) {

        $userCheckins = UserCheckin::with( ['user'] )->select( 'user_checkins.*');
        $userCheckins->leftJoin( 'users', 'users.id', '=', 'user_checkins.user_id' );

        $filterObject = self::filter( $request, $userCheckins );
        $userCheckin = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $userCheckin->orderBy( 'user_checkins.created_at', $dir );
                    break;
                case 2:
                    $userCheckin->orderBy( 'user_checkins.title', $dir );
                    break;
                case 3:
                    $userCheckin->orderBy( 'user_checkins.description', $dir );
                    break;
            }
        }

        $userCheckinCount = $userCheckin->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $userCheckins = $userCheckin->skip( $offset )->take( $limit )->get();

        if ( $userCheckins ) {
            $userCheckins->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = UserCheckin::count();

        $data = [
            'user_checkins' => $userCheckins,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $userCheckinCount : $totalRecord,
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

                $model->whereBetween( 'orders.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'user_checkins.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->checkin_date ) ) {
            if ( str_contains( $request->checkin_date, 'to' ) ) {
                $dates = explode( ' to ', $request->checkin_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'user_checkins.checkin_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'user_checkins.checkin_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
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
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneUserCheckin( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $userCheckin = UserCheckin::with(['user'])->find( $request->id );

        $userCheckin->append( ['encrypted_id'] );
        
        return response()->json( $userCheckin );
    }

    public static function deleteUserCheckin( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'user_checkin.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            UserCheckin::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.user_checkins' ) ) ] ),
        ] );
    }

    public static function updateUserCheckinStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {
            $updateUserCheckin = UserCheckin::find( $request->id );

            if( $updateUserCheckin->status == 10 ){
                $updateUserCheckin->user->total_check_in -= 1;
            }else{
                $updateUserCheckin->user->total_check_in += 1;
            }
            $updateUserCheckin->user->save();

            $updateUserCheckin->status = $updateUserCheckin->status == 10 ? 20 : 10;

            $updateUserCheckin->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'user_checkin' => $updateUserCheckin,
                    'message_key' => 'update_usercheckin_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_usercheckin_failed',
            ], 500 );
        }
    }

    // Calendar
    public static function allUserCheckinCalendars( $request ) {

        $userCheckins = UserCheckin::with( ['user'] )->where( 'status', 10 )->get();

        $groupedCheckins = $userCheckins->groupBy(function ($checkin) {
            return Carbon::parse($checkin->checkin_date)->toDateString(); // Extract date
        })->map(function ($checkins) {
            return $checkins->map(function ($checkin) {
                return [
                    'username' => $checkin->user->name ?? '-',
                    'phone_number' => $checkin->user->phone_number,
                    'total_checkin' => $checkin->user->total_check_in,
                ];
            });
        });
    
        return response()->json(['data' => $groupedCheckins]);

    }

    // API
    public static function getCheckinHistory( $request )
    {
        $user = auth()->user();

        $checkinHistories = UserCheckin::where('status', 10)
            ->where( 'user_id', $user->id )
            ->orderBy( 'created_at', 'DESC' );

        $perPage = empty( $request->per_page) ? 10 : $request->per_page;
        $checkinHistories = $checkinHistories->paginate( $perPage );

        // Convert to array and add your message
        $data = $checkinHistories->toArray();
        $data[ 'message' ] = 'Check-in history retrieved successfully.';
        $data[ 'message_key' ] = 'get_checkin_history_success';

        return response()->json( $data );
    }

    public static function getCheckinRewards( $request )
    {
        $user = auth()->user();

        $checkinRewards = CheckinReward::with( [ 'voucher' ] )
            ->where('status', 10)
            ->orderBy( 'created_at', 'DESC' );

        $perPage = empty( $request->per_page) ? 10 : $request->per_page;
        $checkinRewards = $checkinRewards->get();

        $checkinRewards = $checkinRewards->map(function ($checkinReward) {
            $checkinReward->append(['reward_type_label']);
            if( $checkinReward->voucher ){
                $checkinReward->voucher->append(['image_path']);
            }
            return $checkinReward;
        });

        $currentPage = request()->get('page', 1);
        $paginatedCheckinRewards = new \Illuminate\Pagination\LengthAwarePaginator(
            $checkinRewards->forPage($currentPage, $perPage),
            $checkinRewards->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return response()->json( $paginatedCheckinRewards );
    }

    public static function checkin($request)
    {
        DB::beginTransaction();

        $user = auth()->user();
        $currentDate = now()->format('Y-m-d');
    
        $user = User::find($user->id);
    
        if (!$user) {
            return response()->json( [
                'message' => 'User not found',
                'message_key' => 'user_not_found',
                'errors' => [
                    'user' => 'User not found',
                ]
            ], 422 );
        }
        $originalStreak = true;

        if ( $user->check_in_streak == 7 ) {
            $reward = self::giveReward($user);
            $originalStreak = false;
            $user->check_in_streak = 0;
        }

        $currentDateMYT = now()->timezone( 'Asia/Kuala_Lumpur' )->toDateString();

        $existingCheckin = UserCheckin::where( 'user_id', $user->id )
            ->whereDate( 'checkin_date', $currentDateMYT )
            ->first();

        if ($existingCheckin) {
            return response()->json( [
                'message' => 'User has already checked in today',
                'message_key' => 'user_already_checked_in',
                'errors' => [
                    'user' => 'User has already checked in today',
                ]
            ], 422 );
        }
    
        $lastCheckin = UserCheckin::where('user_id', $user->id)
            ->latest('checkin_date')
            ->first();

        if ($lastCheckin && Carbon::parse($lastCheckin->checkin_date)->diffInDays(now()) > 1) {
            $user->check_in_streak = 0;
        }
    
        $userCheckin = UserCheckin::create([
            'user_id' => $user->id,
            'checkin_date' => now()->timezone( 'Asia/Kuala_Lumpur' ),
            'status' => 10,
        ]);
    
        $user->total_check_in += 1;
        $user->check_in_streak += 1;
        if ($user->check_in_streak != 0 || $originalStreak) {
            $reward = self::giveReward($user);
        }
    
        $user->save();

        // notification
        UserService::createUserNotification(
            $user->id,
            __('notification.user_checkin_success'),
            __('notification.user_checkin_success_content'),
            'user_checkin',
            'user_checkin'
        );

        self::sendNotification( $user, 'checkin', __( 'notification.user_checkin_success_content' )  );

        DB::commit();
    
        return response()->json([
            'message' => 'checkin_success',
            'message_key' => 'Check-in successful',
            'data' => $reward,
        ]);
    }
    
    private static function giveReward($user)
    {

        $reward = CheckinReward::where('consecutive_days', $user->check_in_streak)->first();
        if( $reward ) {
            $reward->append( ['reward_type_label'] );

            switch ($reward->reward_type) {
                case 1:
 
                    WalletService::transact( $user->wallets->where('type', 1)->first(), [
                        'amount' => $reward->reward_value,
                        'remark' => 'Check-in Rewards',
                        'type' => 2,
                        'transaction_type' => 23,
                    ] );
                    break;
                
                default:
                    # code...
                    $voucher = Voucher::find( $reward->voucher_id );

                    UserVoucher::create([
                        'user_id' => $user->id,
                        'voucher_id' => $reward->voucher_id,
                        'expired_date' => Carbon::now()->addDays($voucher->validity_days),
                        'status' => 10,
                        'redeem_from' => 1,
                        'total_left' => $reward->reward_value,
                        'used_at' => null,
                        'secret_code' => strtoupper( \Str::random( 8 ) ),
                    ]);

                    $voucher->total_claimable -= 1;
                    $voucher->save();

                    break;
            }
            return $reward;
        }
    
        return null;
    }
    
    private static function sendNotification( $user, $key, $message ) {

        $messageContent = array();

        $messageContent['key'] = $key;
        $messageContent['id'] = $user->id;
        $messageContent['message'] = $message;

        Helper::sendNotification( $user->user_id, $messageContent );
        
    }

}