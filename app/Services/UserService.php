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

use App\Models\{
    User,
    OtpAction,
    TmpUser,
    MailContent,
    Wallet,
    Option,
    OtpLog,
    Rank,
    ReferralGiftSetting,
    WalletTransaction,
    UserNotification,
    UserNotificationSeen,
    UserNotificationUser,
    UserDevice,
    UserSocial,
    UserVoucher,
    Voucher,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class UserService
{
    public static function allUsers( $request ) {

        $user = User::select( 'users.*' )
        ->with( ['socialLogins'] )
        ->orderBy( 'created_at', 'DESC' );

        $filterObject = self::filter( $request, $user );
        $user = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'user.0.column' ) != 0 ) {
            $dir = $request->input( 'user.0.dir' );
            switch ( $request->input( 'user.0.column' ) ) {
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

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $users = $user->skip( $offset )->take( $limit )->get();

        if ( $users ) {
            $users->append( [
                'encrypted_id',
                'total_accumulate_spending',
                'current_rank',
                'required_points',
            ] );

            foreach( $users as $user ){
                if( $user->socialLogins ){
                    $user->socialLogins->append( ['platform_label'] );
                }
            }
        }

        $totalRecord = User::count();

        $data = [
            'users' => $users,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $userCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    public static function oneUserDownlines( $request ) {

        if( !empty( $request->referral_id ) ) {
            $request->merge( [
                'referral_id' => \Helper::decode( $request->referral_id )
            ] );
        } 

        $user = User::select( 'users.*' )
            ->with( ['socialLogins'] )
            ->where( 'referral_id', $request->referral_id )
            ->orderBy( 'created_at', 'DESC' );

        $filterObject = self::filter( $request, $user );
        $user = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'user.0.column' ) != 0 ) {
            $dir = $request->input( 'user.0.dir' );
            switch ( $request->input( 'user.0.column' ) ) {
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

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $users = $user->skip( $offset )->take( $limit )->get();

        if ( $users ) {
            $users->append( [
                'encrypted_id',
                'total_accumulate_spending',
                'current_rank',
                'required_points',
            ] );

            foreach( $users as $user ){
                if( $user->socialLogins ){
                    $user->socialLogins->append( ['platform_label'] );
                }
            }
        }

        $totalRecord = User::where( 'referral_id', $request->referral_id )->count();

        $data = [
            'users' => $users,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $userCount : $totalRecord,
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

                $model->whereBetween( 'users.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'users.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->fullname ) ) {
            $model->where( 'fullname', 'LIKE', '%' . $request->fullname . '%' );
            $filter = true;
        }

        if ( !empty( $request->first_name ) ) {
            $model->where( 'first_name', 'LIKE', '%' . $request->first_name . '%' );
            $filter = true;
        }

        if ( !empty( $request->last_name ) ) {
            $model->where( 'last_name', 'LIKE', '%' . $request->last_name . '%' );
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
        
            $model->where( function ( $query ) use ( $userInput ) {
                $query->where( 'users.email', 'LIKE', '%' . $userInput . '%' )
                      ->orWhere( 'users.first_name', 'LIKE', '%' . $userInput . '%' )
                      ->orWhere( 'users.last_name', 'LIKE', '%' . $userInput . '%' )
                      ->orWhere( 'users.phone_number', 'LIKE', '%' . $normalizedPhone . '%' );
            } );
        
            $filter = true;
        }

        if ( !empty( $request->mixed_search ) ) {
            $userInput = $request->mixed_search;
        
            $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $userInput );
        
            $model->where( function ( $query ) use ( $userInput, $normalizedPhone ) {
                $query->where( 'users.email', 'LIKE', '%' . $userInput . '%' )
                      ->orWhere( 'users.phone_number', 'LIKE', '%' . $normalizedPhone . '%' );
            } );
        
            $filter = true;
        }

        if ( !empty( $request->title ) ) {
            $model->where( 'phone_number', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'email', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->user_social ) ) {
            $model->whereHas( 'socialLogins', function ($query) use ($request) {
                $query->where( 'platform', $request->user_social );
            });
            $filter = true;
        }

        if ( !empty( $request->rank ) ) {
            $rank = $request->rank;
            $rank = Rank::find( $rank );
            $rank->append( [
                'target_range'
            ] );

            $model->withSum([
                'walletTransactions as total_spending' => function ($q) {
                    $q->where('transaction_type', 12)
                        ->leftJoin('sales_records', 'wallet_transactions.invoice_id', '=', 'sales_records.id');
                }
            ], 'sales_records.total_price')
            ->havingRaw('COALESCE(total_spending, 0) >= ?', [$rank->target_spending])
            ->when($rank->target_range != null, function ($q) use ($rank) {
                $q->havingRaw('COALESCE(total_spending, 0) < ?', [$rank->target_range]);
            });
            $filter = true;
            
            // switch ( $request->rank ) {
        

            //     case 1: // Member
            //         $model->where(function ($query) {
            //             $query->whereHas('walletTransactions', function ($q) {
            //                 $q->selectRaw('SUM(amount) as total_points')
            //                   ->where('transaction_type', 12)
            //                   ->groupBy('user_id')
            //                   ->havingRaw('SUM(amount) < 1000');
            //             })
            //             ->orWhereDoesntHave('walletTransactions', function ($q) {
            //                 $q->where('transaction_type', 12);
            //             });
            //         });
            //         break;
        
            //     case 2: // Silver
            //         $model->whereHas('walletTransactions', function ($q) {
            //             $q->selectRaw('SUM(amount) as total_points')
            //               ->where('transaction_type', 12)
            //               ->groupBy('user_id')
            //               ->havingRaw('SUM(amount) >= 1000 AND SUM(amount) < 10000');
            //         });
            //         break;
        
            //     case 3: // Gold
            //         $model->whereHas('walletTransactions', function ($q) {
            //             $q->selectRaw('SUM(amount) as total_points')
            //               ->where('transaction_type', 12)
            //               ->groupBy('user_id')
            //               ->havingRaw('SUM(amount) >= 10000 AND SUM(amount) < 100000');
            //         });
            //         break;
        
            //     case 4: // Premium
            //         $model->whereHas('walletTransactions', function ($q) {
            //             $q->selectRaw('SUM(amount) as total_points')
            //               ->where('transaction_type', 12)
            //               ->groupBy('user_id')
            //               ->havingRaw('SUM(amount) >= 100000');
            //         });
            //         break;
        
            //     default:
            //         // No rank filter
            //         break;
            // }
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneUser( $request ) {

        $user = User::find( Helper::decode( $request->id ) );

        return response()->json( $user );
    }

    public static function createUser( $request ) {

        if( !empty( $request->referral_id ) ) {
            $request->merge( [
                'referral_id' => \Helper::decode( $request->referral_id )
            ] );
        }

        $validator = Validator::make( $request->all(), [
            'referral_id' => [ 'nullable', 'exists:users,id' ],
            'username' => [ 'nullable', 'alpha_dash', 'unique:users,username', new CheckASCIICharacter ],
            'email' => [ 'nullable', 'bail', 'unique:users,email', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'nullable' ],
            'calling_code' => [ 'nullable' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {

                $defaultCallingCode = "+60";

                $exist = User::where( 'status', 10 )
                ->where( 'calling_code', request( 'calling_code' ) ? request( 'calling_code' ) : $defaultCallingCode )
                ->where( function ( $query ) use ( $value ) {
                    $query->where( 'phone_number', request( 'phone_number' ) )
                        ->orWhere( 'phone_number', ltrim( request( 'phone_number' ), '0' ) );
                } )->first();

                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'required', Password::min( 8 ) ],
        ] );

        $attributeName = [
            'username' => __( 'user.username' ),
            'email' => __( 'user.email' ),
            'fullname' => __( 'user.fullname' ),
            'password' => __( 'user.password' ),
            'phone_number' => __( 'user.phone_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createUserObject = [
                'fullname' => $request->fullname ?? null,
                'username' => $request->username ?? null,
                'first_name' => $request->first_name ?? null,
                'last_name' => $request->last_name ?? null,
                'email' => $request->email ? strtolower( $request->email ) : null,
                'phone_number' => $request->phone_number,
                'calling_code' => $request->calling_code ? $request->calling_code : null,
                'password' => Hash::make( $request->password ),
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'date_of_birth' => $request->date_of_birth,
                'state' => $request->state,
                'city' => $request->city,
                'postcode' => $request->postcode,
                'status' => 10,
                'invitation_code' => strtoupper( \Str::random( 6 ) ),
            ];

            if( !empty( $request->referral_id ) ) {
                $upline = User::find( $request->referral_id );
                $createUserObject['referral_id'] = $upline->id;
                $createUserObject['referral_structure'] = $upline->referral_structure . '|' . $upline->id;
            }

            $createUser = User::create( $createUserObject );

            for ( $i = 1; $i <= 2; $i++ ) {
                $userWallet = Wallet::create( [
                    'user_id' => $createUser->id,
                    'type' => $i,
                    'balance' => 0,
                ] );
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.users' ) ) ] ),
        ] );
    }

    public static function updateUser( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        if( !empty( $request->referral_id ) ) {
            $request->merge( [
                'referral_id' => \Helper::decode( $request->referral_id )
            ] );
        }

        $validator = Validator::make( $request->all(), [
            'referral_id' => [ 'nullable', 'exists:users,id' ],
            'username' => [ 'nullable', 'alpha_dash', 'unique:users,username,' . $request->id, new CheckASCIICharacter ],
            'email' => [ 'nullable', 'bail', 'unique:users,email,' . $request->id, 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'nullable' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {
                
                // $exist = User::where( 'phone_number', $value )
                //     ->where( 'id', '!=', $request->id )
                //     ->first();

                $defaultCallingCode = "+60";

                $exist = User::where( 'id', '!=', $request->id )
                    ->where( 'status', 10 )
                    ->where( 'calling_code', request( 'calling_code' ) ? request( 'calling_code' ) : $defaultCallingCode )
                    ->where( function ( $query ) use ( $value ) {
                        $query->where( 'phone_number', request( 'phone_number' ) )
                            ->orWhere( 'phone_number', ltrim( request( 'phone_number' ), '0' ) );
                    } )->first();

                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'nullable', Password::min( 8 ) ],
        ] );

        $attributeName = [
            'username' => __( 'user.username' ),
            'email' => __( 'user.email' ),
            'fullname' => __( 'user.fullname' ),
            'password' => __( 'user.password' ),
            'phone_number' => __( 'user.phone_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateUser = User::find( $request->id );
            $updateUser->username = strtolower( $request->username );
            $updateUser->first_name = strtolower( $request->first_name );
            $updateUser->last_name = strtolower( $request->last_name );
            $updateUser->email = strtolower( $request->email );
            $updateUser->phone_number = $request->phone_number;
            $updateUser->calling_code = $request->calling_code ? $request->calling_code : $updateUser->calling_code;
            $updateUser->address_1 = $request->address_1 ?? $updateUser->address_1;
            $updateUser->address_2 = $request->address_2 ?? $updateUser->address_2;
            $updateUser->state = $request->state ?? $updateUser->state;
            $updateUser->city = $request->city ?? $updateUser->city;
            $updateUser->postcode = $request->postcode ?? $updateUser->postcode;
            $updateUser->date_of_birth = $request->date_of_birth;
            $updateUser->calling_code = '+60';
            $updateUser->fullname = $request->fullname;

            if ( !empty( $request->password ) ) {
                $updateUser->password = Hash::make( $request->password );
            }
            if( !empty( $request->referral_id ) ) {
                $upline = User::find( $request->referral_id );
                if( $updateUser->referral_id != $request->referral_id ) {
                    $updated_referral_structure = $upline->referral_structure . '|' . $upline->id;
                    $before_referral_structure = $updateUser->referral_structure . '|' . $updateUser->id;

                    $downlines = User::where( 'referral_structure', 'like', '|' . $before_referral_structure . '%' )->get();
                    foreach ( $downlines as $downline ) {
                        $downline->referral_structure = str_replace( $before_referral_structure, $updated_referral_structure . '|' . $updateUser->id, $downline->referral_structure );
                        $downline->save();
                    }

                    $createUserObject['referral_id'] = $upline ? $upline->id : null;
                    $createUserObject['referral_structure'] = $upline ? $upline->referral_structure . '|' . $upline->id : '-';
                }
                
            } else {
                
                if( $updateUser->referral_id != $request->referral_id ) {
                    $updated_referral_structure = '-';
                    $before_referral_structure = $updateUser->referral_structure . '|' . $updateUser->id;

                    $downlines = User::where( 'referral_structure', 'like', '|' . $before_referral_structure . '%' )->get();
                    foreach ( $downlines as $downline ) {
                        $downline->referral_structure = str_replace( $before_referral_structure, $updated_referral_structure . '|' . $updateUser->id, $downline->referral_structure );
                        $downline->save();
                    }
                    $createUserObject['referral_id'] = null;
                    $createUserObject['referral_structure'] = '-';
                }
            }
            
            $updateUser->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.users' ) ) ] ),
        ] );
    }

    public static function updateUserStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateUser = User::find( $request->id );
        $updateUser->status = $request->status;
        $updateUser->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.users' ) ) ] ),
        ] );
    }

    public static function createUserClient( $request ) {

        $validator = Validator::make( $request->all(), [
            'email' => [ 'required', 'bail', 'unique:users,email', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'required' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {

                $exist = User::where( 'phone_number', $value )
                    ->first();

                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'required', 'confirmed', Password::min( 8 ) ],
        ] );

        $attributeName = [
            'email' => __( 'user.email' ),
            'fullname' => __( 'user.fullname' ),
            'password' => __( 'user.password' ),
            'phone_number' => __( 'user.phone_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createUserObject = [
                'name' => strtolower( $request->fullname ),
                'fullname' => $request->fullname,
                'email' => strtolower( $request->email ),
                'phone_number' => $request->phone_number,
                'password' => Hash::make( $request->password ),
                'status' => 10,
            ];

            $createUser = User::create( $createUserObject );
            
            $createUser->save();
            
            $createUser = User::create( [
                'user_id' => $createUser->id,
                'fullname' => $request->fullname,
                'user_name' => $request->fullname,
                'feedback_email' => $createUser->email,
                'calling_code' => '+60',
                'phone_number' => $createUser->phone_number,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.users' ) ) ] ),
        ] );
    }

    public static function updateProfile( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            // 'name' => [ 'required', 'alpha_dash', 'unique:users,name,' . $request->id, new CheckASCIICharacter ],
            'email' => [ 'required', 'bail', 'unique:users,email,' . $request->id, 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'required' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {
                
                $exist = User::where( 'phone_number', $value )
                    ->where( 'id', '!=', $request->id )
                    ->first();

                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'nullable', Password::min( 8 ) ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
        ] );

        $attributeName = [
            'username' => __( 'user.username' ),
            'email' => __( 'user.email' ),
            'fullname' => __( 'user.fullname' ),
            'password' => __( 'user.password' ),
            'phone_number' => __( 'user.phone_number' ),
            'address_1' => __( 'user.address_1' ),
            'address_2' => __( 'user.address_2' ),
            'city' => __( 'user.city' ),
            'state' => __( 'user.state' ),
            'postcode' => __( 'user.postcode' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateUser = User::find( $request->id );
            // $updateUser->name = strtolower( $request->name );
            $updateUser->email = strtolower( $request->email );
            $updateUser->phone_number = $request->phone_number;
            $updateUser->fullname = $request->fullname;

            $updateUser = User::find( $request->id );
            $updateUser->address_1 = $request->address_1;
            $updateUser->address_2 = $request->address_2;
            $updateUser->city = $request->city;
            $updateUser->state = $request->state;
            $updateUser->postcode = $request->postcode;

            if ( !empty( $request->password ) ) {
                $updateUser->password = Hash::make( $request->password );
            }

            $updateUser->save();
            $updateUser->save();

            DB::commit();

            return redirect()->route('web.profile')->with('success', __('template.x_updated', ['title' => Str::singular(__('template.users'))]));

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.users' ) ) ] ),
        ] );
    }
    
    public static function updateUserProfile( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'user_name' => [ 'nullable' ],
            'user_fullname' => [ 'nullable' ],
            'feedback_email' => [ 'nullable' ],
            'user_phone_number' => [ 'nullable' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
        ] );

        $attributeName = [
            'user_name' => __( 'user.user_name' ),
            'user_fullname' => __( 'user.fullname' ),
            'feedback_email' => __( 'user.feedback_email' ),
            'user_phone_number' => __( 'user.phone_number' ),
            'address_1' => __( 'user.address_1' ),
            'address_2' => __( 'user.address_2' ),
            'city' => __( 'user.city' ),
            'state' => __( 'user.state' ),
            'postcode' => __( 'user.postcode' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateUser = User::find( $request->id );
            $updateUser->user->user_name = $request->user_name;
            $updateUser->user->fullname = $request->fullname;
            $updateUser->user->feedback_email = $request->feedback_email;
            $updateUser->user->phone_number = $request->user_phone_number;
            $updateUser->user->address_1 = $request->address_1;
            $updateUser->user->address_2 = $request->address_2;
            $updateUser->user->postcode = $request->postcode;
            $updateUser->user->state = $request->state;
            $updateUser->user->city = $request->city;
            $updateUser->user->save();

            DB::commit();

            return redirect()->route('web.profile')->with('success', __('template.x_updated', ['title' => Str::singular(__('template.users'))]));

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.users' ) ) ] ),
        ] );
    }

    public static function forgotPasswordOtp( $request ) {

        DB::beginTransaction();

        $request->merge( [
            'phone_number' => ltrim($request->phone_number, '0'),
        ] );

        $validator = Validator::make( $request->all(), [
            'phone_number' => [ 'required' , function( $attributes, $value, $fail ) {

                $defaultCallingCode = "+60";

                $user = User::where( 'status', 10 )
                        ->where( 'calling_code', request( 'calling_code' ) ? request( 'calling_code' ) : $defaultCallingCode )
                        ->where( function ( $query ) use ( $value ) {
                            $query->where( 'phone_number', $value )
                                ->orWhere( 'phone_number', ltrim( $value, '0' ) );
                        } )
                        ->first();

                if ( !$user ) {
                    $fail( __( 'user.user_wrong_user' ) );
                    return 0;
                }

                if( $user->status == 20 ) {
                    $fail( __( 'user.account_suspended' ) );
                    return 0;
                }
            } ],
        ] );

        $attributeName = [
            'phone_number' => __( 'user.phone_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $data['otp_code'] = '';
            $data['identifier'] = '';

            $existingUser = User::where( 'calling_code', request( 'calling_code' ) )
                ->where( 'phone_number', request( 'phone_number' ) )
                ->orWhere('phone_number', ltrim(request('phone_number'), '0'))
                ->first();

            if ( $existingUser ) {
                $forgotPassword = Helper::requestOtp( 'forgot_password', [
                    'id' => $existingUser->id,
                    'email' => $existingUser->email,
                    'phone_number' => $existingUser->phone_number,
                    'calling_code' => $existingUser->calling_code,
                ] );
                
                DB::commit();

                $phoneNumber = $existingUser->calling_code . $existingUser->phone_number;
                $result = self::sendSMS( false, $phoneNumber, $forgotPassword['otp_code'], '' );

                if( $result === 'false' ) {
                    return response()->json([
                        'message' => __('user.send_sms_fail'),
                        'message_key' => 'send_sms_failed',
                        'data' => null,
                    ], 500 );
                }
            } else {
                return response()->json([
                    'message' => __('user.user_not_found'),
                    'message_key' => 'get_user_failed',
                    'data' => null,
                ],);
            }

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }

        return response()->json( [
            'message' => 'Reset Password Otp Success',
            'message_key' => 'request_otp_success',
            'data' => $data,
        ] );
    }

    public static function checkPhoneNumber( $request ) {

        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'exists:users,phone_number'],
        ], [
            'phone_number.required' => __('The phone number field is required.'),
            'phone_number.exists' => __('The phone number does not exist in our records.'),
        ]);

        $attributeName = [
            'email' => __( 'user.email' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $existingUser = User::where( 'phone_number', $request->phone_number )->first();
            if ( $existingUser ) {
               
                $response = [
                    'data' => [
                        'message_key' => 'user_exist',
                        'message' => __('user.user_exist'),
                        'errors' => [
                            'user' => __('user.user_exist'),
                        ]
                    ]
                ];

                return $response;

            } else {
                return response()->json([
                    'message' => __('user.user_not_found'),
                    'message_key' => __('user.get_user_failed'),
                    'errors' => [
                        'user' => __('user.user_not_found'),
                    ]
                ], 422 );
            }

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }
    }

    public static function resetPassword( $request ) {

        DB::beginTransaction();

        try {
            $request->merge( [
                'identifier' => Crypt::decryptString( $request->identifier ),
            ] );
        } catch ( \Throwable $th ) {
            return response()->json( [
                'message' =>  __( 'user.invalid_otp' ),
            ], 500 );
        }

        $validator = Validator::make( $request->all(), [
            'identifier' => [ 'required', function( $attribute, $value, $fail ) use ( $request, &$currentOtpAction ) {

                $currentOtpAction = OtpAction::lockForUpdate()
                    ->find( $value );

                if ( !$currentOtpAction ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

                if ( $currentOtpAction->status != 1 ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

                if ( Carbon::parse( $currentOtpAction->expire_on )->isPast() ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

                if ( $currentOtpAction->otp_code != $request->otp_code ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

            } ],
            'password' => [ 'required', 'confirmed', Password::min( 8 ) ],
        ] );

        $attributeName = [
            'password' => __( 'user.password' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $updateUser = User::find( $currentOtpAction->user_id );
            $updateUser->password = Hash::make( $request->password );
            $updateUser->save();

            $currentOtpAction->status = 10;
            $currentOtpAction->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }

        return response()->json( [
            'message' => 'reset_success',
            'message_key' => 'reset_success',
            'data' => $updateUser,
        ] );
    }

    // Api
    public static function registerUser( $request ) {

        $request->merge( [
            'phone_number' => ltrim($request->phone_number, '0'),
        ] );

        try {
            $request->merge( [
                'identifier' => Crypt::decryptString( $request->identifier ),
            ] );
        } catch ( \Throwable $th ) {
            return response()->json( [
                'message' => __( 'validation.header_message' ),
                'errors' => [
                    'identifier' => [
                        __( 'user.invalid_otp' ),
                    ],
                ]
            ], 422 );
        }

        $validator = Validator::make( $request->all(), [
            'otp_code' => [ 'required' ],
            'identifier' => [ 'required', function( $attribute, $value, $fail ) use ( $request, &$currentTmpUser ) {

                $currentTmpUser = TmpUser::lockForUpdate()->find( $value );

                if ( !$currentTmpUser ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

                if ( $currentTmpUser->status != 1 ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

                if ( $currentTmpUser->otp_code != $request->otp_code ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

                if ( $currentTmpUser->phone_number != $request->phone_number ) {
                    $fail( __( 'user.invalid_phone_number' ) );
                    return false;
                }
            } ],
            'email' => [ 'nullable', 'bail', 'unique:users,email', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'nullable' ],
            'first_name' => [ 'nullable' ],
            'last_name' => [ 'nullable' ],
            'calling_code' => [ 'nullable', 'exists:countries,calling_code' ],
            'phone_number' => [ 'nullable', 'digits_between:8,15', function( $attribute, $value, $fail ) {

                $defaultCallingCode = "+60";

                $exist = User::where( 'status', 10 )
                ->where( 'calling_code', request( 'calling_code' ) ? request( 'calling_code' ) : $defaultCallingCode )
                ->where( function ( $query ) use ( $value ) {
                    $query->where( 'phone_number', request( 'phone_number' ) )
                        ->orWhere( 'phone_number', ltrim( request( 'phone_number' ), '0' ) );
                } )
                ->first();
                
                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'required', 'confirmed', Password::min( 8 ) ],
            'invitation_code' => [ 'sometimes', 'nullable', 'exists:users,invitation_code' ],
            'register_token' => [ 'nullable' ],
            'device_type' => [ 'required_with:register_token', 'in:1,2' ],
        ] );

        $attributeName = [
            'email' => __( 'user.email' ),
            'fullname' => __( 'user.fullname' ),
            'password' => __( 'user.password' ),
            'invitation_code' => __( 'user.invitation_code' ),
            'phone_number' => __( 'user.phone_number' ),
            'calling_code' => __( 'user.calling_code' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createUserObject = [
                'fullname' => $request->fullname ? strtolower( $request->fullname ) : null,
                'username' => $request->email ? strtolower( $request->email ) : null,
                'email' => $request->email ? strtolower( $request->email ) : null,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'calling_code' => $request->calling_code ? $request->calling_code : "+60",
                'password' => Hash::make( $request->password ),
                'status' => 10,
                'invitation_code' => strtoupper( \Str::random( 6 ) ),
            ];

            $referral = User::where( 'invitation_code', $request->invitation_code )->first();

            if ( $referral ) {
                $createUserObject['referral_id'] = $referral->id;
                $createUserObject['referral_structure'] = $referral->referral_structure . '|' . $referral->id;
                self::giveUplineVoucher( $referral->id );
            }

            $createUser = User::create( $createUserObject );
            
            // assign register bonus
            $registerBonus = Option::getRegisterBonusSettings();

            $userWallet = Wallet::create( [
                'user_id' => $createUser->id,
                'type' => 1,
                'balance' => 0,
            ] );

            if ( $registerBonus ) {
                WalletService::transact( $userWallet, [
                    'amount' => $registerBonus->option_value,
                    'remark' => 'Register Bonus',
                    'type' => $userWallet->type,
                    'transaction_type' => 20,
                ] );
            }

            // assign referral bonus
            $referralBonus = Option::getReferralBonusSettings();
            if( $referral && $registerBonus){

                $referralWallet = $referral->wallets->where('type',1)->first();

                if( $referralWallet ) {
                    WalletService::transact( $referralWallet, [
                        'amount' => $referralBonus->option_value,
                        'remark' => 'Register Bonus',
                        'type' => $referralWallet->type,
                        'transaction_type' => 22,
                    ] );
                }
            }

            $currentTmpUser = TmpUser::find( $request->identifier );
            $currentTmpUser->status = 10;
            $currentTmpUser->save();

            self::createUserNotification(
                $createUser->id,
                'notification.register_success',
                'notification.register_success_content',
                'register',
                'home'
            );

            // Register OneSignal
            if ( !empty( $request->register_token ) ) {
                self::registerOneSignal( $createUser->id, $request->device_type, $request->register_token );
            }

            $token = $createUser->createToken( 'user_token' )->plainTextToken;
            $createUser->token = $token;

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'user.register_success' ),
            'message_key' => 'register_success',
            'data' => $createUser,
            'token' => $token,
        ] );

    }

    public static function loginUser( $request ) {

        $request->merge( [ 'account' => 'test' ] );

        $request->validate( [
            'phone_number' => 'required',
            'password' => 'required',
            'account' => [ 'sometimes', function( $attributes, $value, $fail ) {

                $defaultCallingCode = "+60";

                $user = User::where('status', 10)
                    ->where(function ($query) use ($defaultCallingCode) {
                        $query->where('calling_code', request('calling_code') ?? $defaultCallingCode)
                            ->orWhereNull('calling_code');
                    })
                    ->where(function ($query) {
                        $query->where('phone_number', request('phone_number'))
                            ->orWhere('phone_number', ltrim(request('phone_number'), '0'));
                    })
                    ->first();
            
                if ( !$user ) {
                    $fail( __( 'user.user_wrong_user' ) );
                    return 0;
                }

                if ( !Hash::check( request( 'password' ), $user->password ) ) {
                    $fail( __( 'user.user_wrong_user_password' ) );
                    return 0;
                }

                if( $user->status == 20 ) {
                    $fail( __( 'user.account_suspended' ) );
                    return 0;
                }

                if( $user->is_social_account == 1 ) {
                    $fail( __( 'user.registered_social' ) );
                    return 0;
                }


            } ],
            'register_token' => [ 'nullable' ],
            'device_type' => [ 'required_with:register_token', 'in:1,2' ],
        ] );

        $defaultCallingCode = "+60";

        $user = User::where( 'status', 10 )
        ->where( 'calling_code', $request->calling_code ? $request->calling_code : $defaultCallingCode )
        ->where( function ( $query ) use ( $request ) {
            $query->where( 'phone_number', $request->phone_number )
                ->orWhere( 'phone_number', ltrim( $request->phone_number, '0' ) );
        } )
        ->first();

        // Register OneSignal
        if ( !empty( $request->register_token ) ) {
            self::registerOneSignal( $user->id, $request->device_type, $request->register_token );
        }

        $token = $user->createToken( 'user_token' )->plainTextToken;
        $user->token = $token;

        return response()->json( [
            'message' => __( 'user.login_success' ),
            'message_key' => 'login_success',
            'data' => $user,
            'token' => $token
        ] );
    }

    private static function registerOneSignal( $user_id, $device_type, $register_token ) {

        UserDevice::updateOrCreate(
            [ 'user_id' => $user_id, 'device_type' => $device_type ? $device_type : 1 ],
            [ 'register_token' => $register_token ]
        );
    }

    public static function loginUserSocial( $request ) {

        $request->validate( [
            'identifier' => [ 'required', function( $attributes, $value, $fail ) {
                $user = User::where( 'email', $value )->where( 'is_social_account', 0 )->first();
                if ( $user ) {
                    $fail( __( 'Email has been Registered' ) );
                }
                $userSocial = UserSocial::where( 'identifier', $value )->first();
                if ( $userSocial ) {
                    if ( $userSocial->platform != request( 'platform' ) ) {
                        $fail( __( 'Email has been registered in other platform' ) );
                    }
                }
            } ],
            'email' => [ 'sometimes', function( $attributes, $value, $fail ) {
                $user = User::where( 'email', $value )->where( 'is_social_account', 0 )->first();
                if ( $user ) {
                    $fail( __( 'Email has been Registered' ) );
                }
            } ],
            'platform' => 'required|in:1,2,3',
            'register_token' => [ 'nullable' ],
            'device_type' => [ 'required_with:register_token', 'in:1,2' ],
        ] );

        $userSocial = UserSocial::where( 'identifier', $request->identifier )->firstOr( function() use ( $request )  {

            \DB::beginTransaction();

            try {
                $createUser = User::create( [
                    'username' => null,
                    'email' => $request->email,
                    'country_id' => 136,
                    'phone_number' => null,
                    'is_social_account' => 1,
                    'invitation_code' => strtoupper( \Str::random( 6 ) ),
                    'referral_id' => null,
                    'referral_structure' => '-',
                    'password' => Hash::make( $request->identifier ),
                ] );

                $createUserSocial = UserSocial::create( [
                    'platform' => request( 'platform' ),
                    'identifier' => request( 'identifier' ),
                    'uuid' => $createUser->id,
                    'user_id' => $createUser->id,
                ] );

                $userWallet = Wallet::create( [
                    'user_id' => $createUser->id,
                    'type' => 1,
                    'balance' => 0,
                ] );

                $registerBonus = Option::getRegisterBonusSettings();

                if ( $registerBonus ) {
                    WalletService::transact( $userWallet, [
                        'amount' => $registerBonus->option_value,
                        'remark' => 'Register Bonus',
                        'type' => $userWallet->type,
                        'transaction_type' => 20,
                    ] );
                }
    
                // assign referral bonus
                $referralBonus = Option::getReferralBonusSettings();
                $referral = User::where( 'invitation_code', $request->invitation_code )->first();

                if( $referral && $registerBonus){
    
                    $referralWallet = $referral->wallets->where('type',1)->first();
    
                    if( $referralWallet ) {
                        WalletService::transact( $referralWallet, [
                            'amount' => $referralBonus->option_value,
                            'remark' => 'Register Bonus',
                            'type' => $referralWallet->type,
                            'transaction_type' => 22,
                        ] );
                    }
                }

                self::createUserNotification(
                    $createUser->id,
                    'notification.register_success',
                    'notification.register_success_content',
                    'register',
                    'home'
                );
    
                // Register OneSignal
                if ( !empty( $request->register_token ) ) {
                    self::registerOneSignal( $createUser->id, $request->device_type, $request->register_token );
                }
    
                return $createUserSocial;
    
            } catch ( \Throwable $th ) {
    
                \DB::rollBack();
                abort( 500, $th->getMessage() . ' in line: ' . $th->getLine() );
            }
        } );

        \DB::commit();

        $user = User::find( $userSocial->user_id );

        // Register OneSignal
        if ( !empty( $request->register_token ) ) {
            self::registerOneSignal( $user->id, $request->device_type, $request->register_token );
        }

        return response()->json( [ 'data' => $user, 'token' => $user->createToken( 'x_api' )->plainTextToken ] );
    }

    public static function getUser( $request, $filterClientCode ) {

        $user = User::with( 'referral' )->find( auth()->user()->id );

        if ( $user ) {
            $user->makeHidden( [
                'status',
                'updated_at',
            ] );

            $user->append( ['total_accumulate_spending','current_rank','required_points', 'referral_code', 'need_birthday_pop_announcement' ] );

            $user->profile_picture_path = $user->profile_picture_path_new;
            $user->profile_picture = $user->profile_picture_path_new;

            $user->points = $user->wallets->first()->balance;
            unset($user->wallets);
        }
    
        // If user not found, return early with error response
        if (!$user) {
            return response()->json([
                'message' => __('user.user_not_found'),
                'message_key' => 'get_user_failed',
                'data' => null,
            ]);
        }
    
        // Success response
        return response()->json([
            'message' => '',
            'message_key' => 'get_user_success',
            'data' => $user,
        ]);
    }

    public static function updateUserApi( $request ) {

        $validator = Validator::make( $request->all(), [
            'username' => [ 'nullable', 'unique:users,username,' . auth()->user()->id, ],
            'first_name' => [ 'nullable' ],
            'last_name' => [ 'nullable' ],
            'email' => [ 'nullable', 'unique:users,email,' . auth()->user()->id, ],
            'phone_number' => [ 'nullable', 'unique:users,phone_number,' . auth()->user()->id, ],
            'date_of_birth' => ['nullable', 'date'],
            'to_remove' => ['nullable', 'in:1,2'],
            'profile_picture' => [ 'nullable', 'file', 'max:30720', 'mimes:jpg,jpeg,png,heic' ],
            'invitation_code' => [ 'sometimes', 'exists:users,invitation_code' ],
        ] );

        $attributeName = [
            'username' => __( 'user.username' ),
            'date_of_birth' => __( 'user.date_of_birth' ),
            'email' => __( 'user.email' ),
            'first_name' => __( 'user.first_name' ),
            'last_name' => __( 'user.last_name' ),
            'phone_number' => __( 'user.phone_number' ),
            'invitation_code' => __( 'user.invitation_code' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        // custom validation
        $user = auth()->user(); // or the model you're updating
        $input = $request->all();

        $rules = [
            'email' => [ 'nullable', 'email', Rule::unique('users')->ignore($user->id) ],
            'phone_number' => [ 'nullable' ],
            'date_of_birth' => [ 'nullable', 'date'],
        ];

        // CASE 1: Has phone, no email
        if ( $user->phone_number && !$user->email ) {
            // Disallow phone change
            $rules['phone_number'][] = function( $attribute, $value, $fail ) use ( $user ) {
                if ( $value !== $user->phone_number ) {
                    $fail( __( 'Please contact admin for phone number update.' ) );
                }
            };
        }

        if ( $user->date_of_birth ) {
            // Disallow phone change
            $rules['date_of_birth'][] = function( $attribute, $value, $fail ) use ( $user ) {
                if ( $value !== $user->date_of_birth ) {
                    $fail( __( 'Please contact admin for Birthday update.' ) );
                }
            };
        }

        // CASE 2: Has email, no phone
        if ( !$user->phone_number && $user->email ) {
            // Allow phone to be added once only, must be unique
            $rules['phone_number'][] = 'required';
            $rules['phone_number'][] = Rule::unique('users');
        }

        $validated = Validator::make($input, $rules)->validate();

        $updateUser = User::find( auth()->user()->id );
        $updateUser->username = $request->username;
        $updateUser->first_name = $request->first_name;
        $updateUser->last_name = $request->last_name;
        $updateUser->phone_number = $request->phone_number;
        $updateUser->date_of_birth = $request->date_of_birth;
        $updateUser->email = $request->email;

        if ( $request->to_remove == 1 && $updateUser->profile_picture ) {
            Storage::disk( 'public' )->delete( $updateUser->profile_picture );
            $updateUser->profile_picture = null;
        }

        if( $request->file( 'profile_picture' ) ) {
            
            if( $updateUser->profile_picture  ) {
                Storage::disk( 'public' )->delete( $updateUser->profile_picture );
            }

            $updateUser->profile_picture = $request->file( 'profile_picture' )->store( 'users/' . $updateUser->id, [ 'disk' => 'public' ] );
        }

        if( !empty( $request->invitation_code ) ) {
            $upline = User::where( 'invitation_code', $request->invitation_code )->first();
            if( $updateUser->referral_id == null ) {
                self::giveUplineVoucher( $upline->id );
            }

            $updateUser->referral_id = $upline->id;
            $updateUser->referral_structure = $upline->referral_structure . '|' . $upline->id;
        }

        $updateUser->save();

        $updateUser->profile_picture_path = $updateUser->profile_picture_path_new;
        $updateUser->profile_picture = $updateUser->profile_picture_path_new;

        return response()->json( [
            'message' => __( 'user.user_updated' ),
            'message_key' => 'update_user_success',
            'data' => $updateUser
        ] );
    }

    public static function updateUserPassword( $request ) {

        $validator = Validator::make( $request->all(), [
            'old_password' => [ 'required', Password::min( 8 ), function( $attribute, $value, $fail ) {
                if ( !Hash::check( $value, auth()->user()->password ) ) {
                    $fail( __( 'user.old_password_not_match' ) );
                }
            } ],
            'password' => [ 'required', Password::min( 8 ), 'confirmed' ],
        ] );

        $attributeName = [
            'old_password' => __( 'user.old_password' ),
            'password' => __( 'user.password' ),
            'password_confirmation' => __( 'user.password_confirmation' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $updateUser = User::find( auth()->user()->id );
        $updateUser->password = Hash::make( $request->password );
        $updateUser->save();

        return response()->json( [
            'message' => __( 'user.user_password_updated' ),
            'message_key' => 'update_user_password_success',
        ] );
    }

    public static function requestOtp( $request ) {

        $validator = Validator::make( $request->all(), [
            'request_type' => [ 'required', 'in:1,2' ],
            'identifier' => [
                Rule::requiredIf(function () use ($request) {
                    return !empty($request->action) && str_contains($request->action, 'resend');
                }),
            ],
            'calling_code' => [ 'nullable', 'string', 'regex:/^\+\d{1,4}$/' ], // Basic international format
        ] );
    
        $attributeName = [
            'request_type' => __( 'user.request_type' ),
            'calling_code' => __( 'user.calling_code' ),
        ];
    
        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
    
        $validator->setAttributeNames( $attributeName )->validate();
    
        DB::beginTransaction();

        $callingCode = $request->calling_code ?? '+60';
    
        if ( $request->request_type == 1 ) {
    
            $validator = Validator::make( $request->all(), [
                'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {

                    if ( mb_substr( $value, 0, 1 ) == 0 ) {
                        $value = mb_substr( $value, 1 );
                    }

                    $user = User::where( 'phone_number', $value )
                        ->orWhere('phone_number', ltrim($value, '0'))
                        ->first();

                    if ( $user ) {
                        $fail( __( 'validation.unique' ) );
                    }
                } ],
                'request_type' => [ 'required', 'in:1' ],
            ] );
    
            $attributeName = [
                'phone_number' => __( 'user.phone_number' ),
                'request_type' => __( 'user.request_type' ),
            ];
    
            foreach ( $attributeName as $key => $aName ) {
                $attributeName[$key] = strtolower( $aName );
            }
    
            $validator->setAttributeNames( $attributeName )->validate();
    
            try {
                $action = 'register';
                if( $request->identifier ){
                    $request->merge( [
                        'identifier' => Crypt::decryptString( $request->identifier ),
                    ] );
                    $action = 'resend';
                }

                $createTmpUser = Helper::requestOtp( $action, [
                    'calling_code' => $request->calling_code,
                    'phone_number' => $request->phone_number,
                    'email' => $request->email,
                    'identifier' => $request->identifier ? $request->identifier : null,
                ] );
    
                DB::commit();
                $phoneNumber  = $request->calling_code . $request->phone_number;
                $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $request->phone_number );

                // Mail::to( $request->email )->send(new OtpMail( $createTmpUser ));
                $result = self::sendSMS( false, $phoneNumber, $createTmpUser['otp_code'], '' );

                if( $result === 'false' ) {
                    return response()->json([
                        'message' => __('user.send_sms_fail'),
                        'message_key' => 'send_sms_failed',
                        'data' => null,
                    ], 500 );
                }
                
                return response()->json( [
                    'message' => $request->calling_code . $request->phone_number . ' request otp success',
                    'message_key' => 'request_otp_success',
                    'data' => [
                        'otp_code' => '#DEBUG - ' . $createTmpUser['otp_code'],
                        'identifier' => $createTmpUser['identifier'],
                        'title' => $createTmpUser ? __( 'user.otp_email_success' ) : '',
                        'note' => $createTmpUser ? __( 'user.otp_email_success_note', [ 'title' => $phoneNumber ] ) : '',
                        // 'result' => json_encode( $result ),
                    ]
                ] );
    
            } catch ( \Throwable $th ) {
                DB::rollBack();
                abort( 500, $th->getMessage() . ' in line: ' . $th->getLine() );
            }
    
        } else { // Resend

            try {
                $request->merge( [
                    'identifier' => Crypt::decryptString( $request->identifier ),
                ] );
            } catch ( \Throwable $th ) {
                return response()->json( [
                    'message' => __( 'validation.header_message' ),
                    'errors' => [
                        'identifier' => [ __( 'user.invalid_otp' ) ],
                    ]
                ], 422 );
            }
    
            $validator = Validator::make( $request->all(), [
                'identifier' => [
                    'required',
                    function( $attribute, $value, $fail ) {
                        $current = TmpUser::find( $value );
                        
                        if ( !$current ) {
                            $fail( __( 'user.invalid_request' ) );
                            return false;
                        }
    
                        $exist = TmpUser::where( 'phone_number', $current->phone_number )
                                        ->where( 'status', 1 )
                                        ->exists();

                        if ( !$exist ) {
                            $fail( __( 'user.invalid_request' ) );
                        }
                    },
                ],
            ] );
    
            $attributeName = [
                'identifier' => __( 'user.phone_number' ),
            ];
    
            foreach ( $attributeName as $key => $aName ) {
                $attributeName[$key] = strtolower( $aName );
            }
    
            $validator->setAttributeNames( $attributeName )->validate();
    
            $currentTmp = TmpUser::find( $request->identifier );
            $phoneNumber = $callingCode . $currentTmp->phone_number;
    
            $updateTmpUser = Helper::requestOtp( 'resend', [
                'calling_code' => $callingCode,
                'phone_number' => $phoneNumber,
                'identifier' => $request->identifier,
                'title' => __( 'user.otp_email_success' ),
                'note' => __( 'user.otp_email_success_note', [ 'title' => $phoneNumber ] ),
            ] );
    
            DB::commit();
            $result = self::sendSMS( false, $phoneNumber, $updateTmpUser['otp_code'], '' );

            if( $result === 'false' ) {
                return response()->json([
                    'message' => __('user.send_sms_fail'),
                    'message_key' => 'send_sms_failed',
                    'data' => null,
                ], 500 );
            }
    
            return response()->json( [
                'message' => 'resend_otp_success',
                'message_key' => 'resend_otp_success',
                'data' => [
                    'otp_code' => '#DEBUG - ' . $updateTmpUser['otp_code'],
                    'identifier' => $updateTmpUser['identifier'],
                ]
            ] );
        }
    }
    

    public static function createEnquiryMail( $request ) {

        $validator = Validator::make( $request->all(), [
            'fullname' => [ 'nullable' ],
            'email' => [ 'required' ],
            'phone_number' => [ 'required' ],
            'message' => [ 'nullable' ],
        ] );

        $attributeName = [
            'fullname' => __( 'user.fullname' ),
            'email' => __( 'user.email' ),
            'phone_number' => __( 'user.phone_number' ),
            'message' => __( 'user.message' ),
        ];
        
        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $mailContent = MailContent::create( [
                'fullname' => $request->fullname,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'remarks' =>$request->message,
            ] );
            
            DB::commit();

            // Mail::to( config( 'services.mail.receiver' ) )->send(new EnquiryEmail( $mailContent ));
            
            return response()->json( [
                'data' => [
                    'message_key' => 'Enquiry Received!',
                    'message_key' => 'enquiry_received',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_enquiry_failed',
            ], 500 );
        }
    }

    public static function deleteVerification($request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required'],
        ], [
            'password.required' => __('The password field is required.'),
        ]);
    
        $attributeName = [
            'password' => __('user.password'),
        ];
    
        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }
    
        $validator->setAttributeNames($attributeName)->validate();
    
        try {
            // Assume the authenticated user is making this request
            $currentUser = auth()->user();
    
            if (!$currentUser) {
                return response()->json([
                    'message' => __('user.not_authenticated'),
                    'message_key' => 'user_not_authenticated',
                    'data' => null,
                ], 401);
            }
    
            // Verify password
            if (!Hash::check($request->password, $currentUser->password)) {
                return response()->json([
                    'message' => __('user.invalid_password'),
                    'message_key' => 'invalid_password',
                    'errors' => [
                        'user' => __('user.invalid_password'),
                    ]
                ], 422);
            }
    
            return response()->json([
                'message' => __('user.password_verified'),
                'message_key' => 'account_deleted',
                'data' => null,
            ]);
    
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
    }

    public static function deleteConfirm( $request ) {

        $validator = Validator::make($request->all(), [
            'password' => ['required'],
        ], [
            'password.required' => __('The password field is required.'),
        ]);
    
        $attributeName = [
            'password' => __('user.password'),
        ];
    
        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }
    
        $validator->setAttributeNames($attributeName)->validate();
    
        try {
            // Assume the authenticated user is making this request
            $currentUser = auth()->user();
    
            if (!$currentUser) {
                return response()->json([
                    'message' => __('user.not_authenticated'),
                    'message_key' => 'user_not_authenticated',
                    'data' => null,
                ], 401);
            }
    
            // Verify password
            if (!Hash::check($request->password, $currentUser->password)) {
                return response()->json([
                    'message' => __('user.invalid_password'),
                    'message_key' => 'invalid_password',
                    'errors' => [
                        'user' => __('user.invalid_password'),
                    ]
                ], 422);
            }
    
            DB::beginTransaction();
    
            $currentUser->status = 20;
            $currentUser->save();
            DB::commit();
    
            return response()->json([
                'message' => __('user.account_deleted'),
                'message_key' => 'account_deleted',
                'data' => null,
            ]);
    
        } catch (\Throwable $th) {
            DB::rollBack();
    
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
    }

    public static function getNotifications( $request ) {

        $notifications = UserNotification::select(
            'user_notifications.*',
            // DB::raw( '( SELECT COUNT(*) FROM user_notification_seens AS a WHERE a.user_notification_id = user_notifications.id AND a.user_id = ' .request()->user()->id. ' ) as is_read' )
            DB::raw( 'CASE WHEN user_notification_seens.id > 0 THEN 1 ELSE 0 END as is_read' )
        )->where( function( $query ) {
            $query->where( 'user_notifications.status', 10 );
            $query->where( 'user_notifications.is_broadcast', 10 );
            $query->orWhere( 'user_notification_users.user_id', auth()->user()->id );
        } );

        $notifications->leftJoin( 'user_notification_users', function( $query ) {
            $query->on( 'user_notification_users.user_notification_id', '=', 'user_notifications.id' );
            // $query->on( 'user_notification_users.user_id', '=', DB::raw( auth()->user()->id ) );
        } );

        // $notifications->leftJoin( 'user_notification_seens', 'user_notification_seens.user_notification_id', '=', 'user_notifications.id' );
        $notifications->leftJoin( 'user_notification_seens', function( $query ) {
            $query->on( 'user_notification_seens.user_notification_id', '=', 'user_notifications.id' );
            $query->on( 'user_notification_seens.user_id', '=', DB::raw( auth()->user()->id ) );
        } );

        $notifications->when( !empty( $request->type ), function( $query ) {
            return $query->where( 'user_notifications.type', request( 'type' ) );
        } );

        $notifications->when( $request->has( 'is_read' ), function( $query ) use ( $request ) {
            if ( ( int ) $request->is_read === 1 ) {
                return $query->whereNotNull( 'user_notification_seens.id' );
            }
        });

        $notifications->when( $request->notification != '' , function( $query ) use( $request ) {
            return $query->where( 'user_notifications.id', $request->notification );
        } );

        $notifications->where( 'user_notifications.status', 10 );

        $notifications->orderBy( 'user_notifications.created_at', 'DESC' );

        $notifications = $notifications->simplePaginate( empty( $request->per_page ) ? 100 : $request->per_page );

        $notifications->getCollection()->transform(function ($item) {
            $item->image_path = $item->image 
                ? asset('storage/notifications/' . $item->image) 
                : asset('storage/notifications/default.png');
            return $item;
        });
        
        return response()->json( $notifications );
    }

    public static function getNotification( $request ) {

        $notification = UserNotification::find( $request->notification );

        return response()->json( [
            'data' => $notification,
        ] );
    }

    public static function updateNotificationSeen( $request ) {

        $notification = UserNotification::find( $request->notification );
        if ( !$notification ) {
            return response()->json( [
                'message' => '',
            ] );
        }

        UserNotificationSeen::firstOrCreate( [
            'user_notification_id' => $request->notification,
            'user_id' => auth()->user()->id,
        ], [
            'user_notification_id' => $request->notification,
            'user_id' => auth()->user()->id,
        ] );

        $notification->append( [ 'image_path' ] );

        return response()->json( [
            'message' => __( 'notification.notification_seen' ),
            'data' => [$notification]
        ] );
    }

    public static function createUserNotification( $user, $title = null, $content = null, $slug = null, $key = null ){

        $createNotification = UserNotification::create( [
            'type' => 2,
            'title' => $title,
            'content' => $content,
            'url_slug' => $slug ? \Str::slug( $slug ) : null,
            'system_title' => NULL,
            'system_content' => NULL,
            'system_data' => NULL,
            'meta_data' => NULL,
            'key' => $key,
        ] );

        $createUserNotificationUser = UserNotificationUser::create( [
            'user_notification_id' => $createNotification->id,
            'user_id' => $user,
        ] );

    }

    private static function sendNotification( $user, $key, $message ) {

        $messageContent = array();

        $messageContent['key'] = $key;
        $messageContent['id'] = $user->id;
        $messageContent['message'] = $message;

        Helper::sendNotification( $user->user_id, $messageContent );
        
    }

    private static function sendSMS( $customMessage = false, $mobile, $otp, $message = '' ) {

        $url = config( 'services.sms.sms_url' );
        $builtMessage = $customMessage ? $message : 'Your One Time Password (OTP) is '.$otp.'. This OTP expires in 10 minutes.';
        $encodedMessage = rawurlencode($builtMessage);

        $request = array(
            'un' => config( 'services.sms.username' ),
            'pwd' => config( 'services.sms.password' ),
            'dstno' => $mobile,
            'msg' => $encodedMessage,
            'type' => 1,
            'agreedterm'=> 'YES',
        );

        $sendSMS = \Helper::curlGet( $url . '?' . http_build_query( $request ) );

        $log = OtpLog::create( [
            'url' => $url . '?' . http_build_query( $request ),
            'method' => 'GET',
            'phone_number' => $mobile,
            'otp_code' => $otp,
            'status' => isset( $sendSMS['status'] ) ? ( $sendSMS['status'] == 200 ? 10 : 20 ) : 20,
            'raw_response' => json_encode( $sendSMS ),
        ] );
        
        return $sendSMS['status'] == 200 ? 'true' : 'false';
    }

    public static function testNotification( $request ) {
        try {
            // Defaults
            $title = $request->input( 'title', 'test-notification' );
            $content = $request->input( 'content', 'test-notification-content' );
    
            // Allow override for app_id and api_key
            $appId = $request->input( 'app_id', config( 'services.os.app_id' ) );
            $apiKey = $request->input( 'api_key', config( 'services.os.api_key' ) );
    
            // Get user (from token or fallback to authenticated)
            $user = auth()->user();
    
            if ( ! $user ) {
                return response()->json([ 'message' => 'User not found.' ], 500);
            }
    
            // Get register token (from request or fallback)
            $devices = UserDevice::where( 'user_id', $user->id )->get();
    
            if ( $request->input( 'register_token' ) ) {
                // Send to a specific register token
                $registerToken = $request->input( 'register_token' );
    
                $header = [
                    'Content-Type: application/json; charset=utf-8',
                    'Authorization: BASIC ' . $apiKey,
                ];
    
                $payload = [
                    'app_id' => $appId,
                    'contents' => [
                        'en' => strip_tags( $content ),
                        'zh' => strip_tags( $content ),
                    ],
                    'headings' => [
                        'en' => $title,
                        'zh' => $title,
                    ],
                    'include_player_ids' => [ $registerToken ],
                    'data' => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',
                        'status' => 'done',
                        'key' => 'test',
                        'id' => uniqid( 'test_' ),
                    ]
                ];
    
                $send = Helper::curlPost( 'https://onesignal.com/api/v1/notifications', json_encode( $payload ), $header );
    
                return response()->json([
                    'message' => 'Test notification sent.',
                    'response' => $send
                ]);
            }
    
            // Send to all user devices
            if ( $devices->count() > 0 ) {
                foreach ( $devices as $device ) {
                    try {
                        $header = [
                            'Content-Type: application/json; charset=utf-8',
                            'Authorization: BASIC ' . $apiKey,
                        ];
    
                        $payload = [
                            'app_id' => $appId,
                            'contents' => [
                                'en' => strip_tags( $content ),
                                'zh' => strip_tags( $content ),
                            ],
                            'headings' => [
                                'en' => $title,
                                'zh' => $title,
                            ],
                            'include_player_ids' => [ $device->register_token ],
                            'data' => [
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                'sound' => 'default',
                                'status' => 'done',
                                'key' => 'test',
                                'id' => uniqid( 'test_' ),
                            ]
                        ];
    
                        $send = Helper::curlPost( 'https://onesignal.com/api/v1/notifications', json_encode( $payload ), $header );
    
                    } catch ( \Exception $e ) {
                        // Continue to next device if one fails
                        continue;
                    }
                }
            }
    
            return response()->json([
                'message' => 'Test notification sent.',
                'response' => true
            ]);
        } catch ( \Exception $e ) {
            return response()->json([
                'message' => 'Failed to send test notification.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public static function giveUplineVoucher( $upline_id ) {
        $upline = User::find( $upline_id );
        if ( $upline ) {
            $gift = ReferralGiftSetting::where( 'status', 10 )->first();
            if( $gift ) {
                if( $gift->reward_type == 2 ) {
                    $voucher = Voucher::find( $gift->voucher_id );
                    if( $voucher ) {
                        $createUserVoucher = UserVoucher::create( [
                            'user_id' => $upline->id, 
                            'voucher_id' => $voucher->id,
                            'expired_date' => Carbon::now()->timezone( 'Asia/Kuala_Lumpur' )->subDays( $gift->expiry_day ),
                            'total_left' => 1,
                            'type' => 3,
                            'secret_code' => strtoupper( \Str::random( 8 ) ),
                        ] );
                    }
                } else {
                    //  give point
                    WalletService::transact( $upline->wallets->where('type', 1)->first(), [
                        'amount' => $gift->reward_value,
                        'remark' => 'referral Rewards',
                        'type' => 2,
                        'transaction_type' => 27,
                    ] );
                }
            }
        }
    }
    
}