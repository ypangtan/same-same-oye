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
    WalletTransaction,
    UserNotification,
    UserNotificationSeen,
    UserNotificationUser,
    UserDevice,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class UserService
{
    public static function allUsers( $request ) {

        $user = User::select( 'users.*' )->orderBy( 'created_at', 'DESC' );

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

        $limit = $request->length;
        $offset = $request->start;

        $users = $user->skip( $offset )->take( $limit )->get();

        if ( $users ) {
            $users->append( [
                'encrypted_id',
            ] );
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

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->registered_date ) ) {
            if ( str_contains( $request->registered_date, 'to' ) ) {
                $dates = explode( ' to ', $request->registered_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'users.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->registered_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'users.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
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

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'email', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
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

        $validator = Validator::make( $request->all(), [
            'username' => [ 'nullable', 'alpha_dash', 'unique:users,username', new CheckASCIICharacter ],
            'email' => [ 'nullable', 'bail', 'unique:users,email', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'nullable' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {

                $exist = User::where( 'phone_number', $value )
                    ->first();

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
                'email' => $request->email ? strtolower( $request->email ) : null,
                'phone_number' => $request->phone_number,
                'calling_code' => '+60',
                'password' => Hash::make( $request->password ),
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'state' => $request->state,
                'city' => $request->city,
                'postcode' => $request->postcode,
                'status' => 10,
                'invitation_code' => strtoupper( \Str::random( 6 ) ),
            ];

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

        $validator = Validator::make( $request->all(), [
            'username' => [ 'nullable', 'alpha_dash', 'unique:users,username,' . $request->id, new CheckASCIICharacter ],
            'email' => [ 'nullable', 'bail', 'unique:users,email,' . $request->id, 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'nullable' ],
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
            $updateUser->email = strtolower( $request->email );
            $updateUser->phone_number = $request->phone_number;
            $updateUser->address_1 = $request->address_1 ?? $updateUser->address_1;
            $updateUser->address_2 = $request->address_2 ?? $updateUser->address_2;
            $updateUser->state = $request->state ?? $updateUser->state;
            $updateUser->city = $request->city ?? $updateUser->city;
            $updateUser->postcode = $request->postcode ?? $updateUser->postcode;
            $updateUser->calling_code = '+60';
            $updateUser->fullname = $request->fullname;

            if ( !empty( $request->password ) ) {
                $updateUser->password = Hash::make( $request->password );
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

        $validator = Validator::make( $request->all(), [
            'phone_number' => [ 'required' ],
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

            $existingUser = User::where( 'phone_number', $request->phone_number )->first();
            if ( $existingUser ) {
                $forgotPassword = Helper::requestOtp( 'forgot_password', [
                    'id' => $existingUser->id,
                    'email' => $existingUser->email,
                    'phone_number' => $existingUser->phone_number,
                    'calling_code' => $existingUser->calling_code,
                ] );
                
                DB::commit();

                // Mail::to( $existingUser->email )->send(new OtpMail( $forgotPassword ));
    
                if (Mail::failures() != 0) {
                    
                    $response = [
                        'data' => [
                            'title' => $forgotPassword ? __( 'user.otp_email_success' ) : '',
                            'note' => $forgotPassword ? __( 'user.otp_email_success_note', [ 'title' => $existingUser->email ] ) : '',
                            'identifier' => $forgotPassword['identifier'],
                            'otp_code' => '#DEBUG - ' . $forgotPassword['otp_code'],
                        ]
                    ];
    
                    return $response;
                }

                return "Oops! There was some error sending the email.";
            } else {
                return response()->json([
                    'message' => __('user.user_not_found'),
                    'message_key' => 'get_user_failed',
                    'data' => null,
                ]);
            }

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }

        return response()->json( [
            'message_key' => 'request_otp_success',
            'data' => [
                'title' => $data['title'],
                'note' => $data['note'],
                'otp_code' => $data['otp_code'],
                'identifier' => $data['identifier'],
            ],
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
            'data' => null,
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
            'calling_code' => [ 'nullable', 'exists:countries,calling_code' ],
            'phone_number' => [ 'nullable', 'digits_between:8,15', function( $attribute, $value, $fail ) {
                $exist = User::where( 'calling_code', request( 'calling_code' ) )
                ->where( 'phone_number', $value )->where( 'status', 10 )
                ->orWhere('phone_number', ltrim($value, '0'))
                ->first();
                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'required', 'confirmed', Password::min( 8 ) ],
            'invitation_code' => [ 'sometimes', 'exists:users,invitation_code' ],
        ] );

        $attributeName = [
            'email' => __( 'user.email' ),
            'fullname' => __( 'user.fullname' ),
            'password' => __( 'user.password' ),
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
                'phone_number' => $request->phone_number,
                'calling_code' => $request->calling_code,
                'password' => Hash::make( $request->password ),
                'status' => 10,
                'invitation_code' => strtoupper( \Str::random( 6 ) ),
            ];

            $referral = User::where( 'invitation_code', $request->invitation_code )->first();

            if ( $referral ) {
                $createUserObject['referral_id'] = $referral->id;
                $createUserObject['referral_structure'] = $referral->referral_structure . '|' . $referral->id;
            }

            $createUser = User::create( $createUserObject );
            // assign register bonus
            $registerBonus = Option::getRegisterBonusSettings();

            for ( $i = 1; $i <= 2; $i++ ) {
                $userWallet = Wallet::create( [
                    'user_id' => $createUser->id,
                    'type' => $i,
                    'balance' => 0,
                ] );

                if ( $registerBonus && $i == 2 ) {
                    WalletService::transact( $userWallet, [
                        'amount' => $registerBonus->option_value,
                        'remark' => 'Register Bonus',
                        'type' => $userWallet->type,
                        'transaction_type' => 20,
                    ] );
                }
            }

            // assign referral bonus
            $referralBonus = Option::getReferralBonusSettings();
            if( $referral && $registerBonus){

                $referralWallet = $referral->wallets->where('type',2)->first();

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
                self::registerOneSignal( $user->id, $request->device_type, $request->register_token );
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'data' => [ 
                'message' => __( 'user.register_success' ),
                'message_key' => 'register_success',
                'user' => $createUser,
                'token' => $createUser->createToken( 'user_token' )->plainTextToken
            ],
        ] );

    }

    public static function loginUser( $request ) {

        $request->merge( [ 'account' => 'test' ] );

        $request->validate( [
            'phone_number' => 'required',
            'password' => 'required',
            'account' => [ 'sometimes', function( $attributes, $value, $fail ) {

                $user = User::where( 'phone_number', request( 'phone_number' ) )
                ->orWhere('phone_number', ltrim(request('phone_number'), '0'))
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
            } ],
        ] );

        $user = User::where( 'phone_number', $request->phone_number )->first();

        // Register OneSignal
        if ( !empty( $request->register_token ) ) {
            self::registerOneSignal( $user->id, $request->device_type, $request->register_token );
        }

        return response()->json( [
            'data' => [ 
                'message' => __( 'user.login_success' ),
                'message_key' => 'login_success',
                'user' => $user,
                'token' => $user->createToken( 'user_token' )->plainTextToken
            ],
        ] );
    }

    private static function registerOneSignal( $user_id, $device_type, $register_token ) {

        UserDevice::updateOrCreate(
            [ 'user_id' => $user_id, 'device_type' => 1 ],
            [ 'register_token' => $register_token ]
        );
    }

    public static function getUser( $request, $filterClientCode ) {

        $user = User::with( ['wallets'] )->find( auth()->user()->id );

        if ( $user ) {
            $user->makeHidden( [
                'status',
                'updated_at',
            ] );
        }

        if($user->wallets){ 
            foreach($user->wallets as $wallet){
                $wallet->append([
                    'listing_balance',
                    'formatted_type'
                ]);
            }
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
            'email' => [ 'nullable', 'unique:users,email,' . auth()->user()->id, ],
            'date_of_birth' => ['nullable', 'date'],
        ] );

        $attributeName = [
            'username' => __( 'user.username' ),
            'date_of_birth' => __( 'user.date_of_birth' ),
            'email' => __( 'user.email' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $updateUser = User::find( auth()->user()->id );
        $updateUser->username = $request->username;
        $updateUser->date_of_birth = $request->date_of_birth;
        $updateUser->email = $request->email;
        $updateUser->save();

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
        ] );

        $attributeName = [
            'request_type' => __( 'user.request_type' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

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
    
            $expireOn = Carbon::now()->addMinutes( '10' );

            try {

                $createTmpUser = Helper::requestOtp( 'register', [
                    'calling_code' => $request->calling_code,
                    'phone_number' => $request->phone_number,
                    'email' => $request->email,
                ] );
    
                DB::commit();
                $phoneNumber  = $request->calling_code . $request->phone_number;

                // Mail::to( $request->email )->send(new OtpMail( $createTmpUser ));
                
                return response()->json( [
                    'message' => $request->calling_code . $request->phone_number,
                    'message_key' => 'request_otp_success',
                    'data' => [
                        'otp_code' => '#DEBUG - ' . $createTmpUser['otp_code'],
                        'identifier' => $createTmpUser['identifier'],
                        'title' => $createTmpUser ? __( 'user.otp_email_success' ) : '',
                        'note' => $createTmpUser ? __( 'user.otp_email_success_note', [ 'title' => $phoneNumber ] ) : ''
                    ]
                ] );
    
            } catch ( \Throwable $th ) {
    
                \DB::rollBack();
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
                        'identifier' => [
                            __( 'user.invalid_otp' ),
                        ],
                    ]
                ], 422 );
            }

            $validator = Validator::make( $request->all(), [
                'identifier' => [ 'required', function( $attribute, $value, $fail ) {
    
                    $current = TmpUser::find( $value );

                    if ( !$current ) {
                        $fail( __( 'user.invalid_request' ) );
                        return false;
                    }
                    
                    $exist = TmpUser::where( 'phone_number', $current->phone_number )->where( 'status', 1 )->count();
                    if ( $exist == 0 ) {
                        $fail( __( 'user.invalid_request' ) );
                        return false;
                    }
                } ],

            ] );

            $attributeName = [
                'identifier' => __( 'user.phone_number' ),
            ];
    
            foreach ( $attributeName as $key => $aName ) {
                $attributeName[$key] = strtolower( $aName );
            }
            $currentTmp = TmpUser::find( $request->identifier );
    
            $validator->setAttributeNames( $attributeName )->validate();
            $phoneNumber  = $request->calling_code . $request->phone_number;
            $updateTmpUser = Helper::requestOtp( 'resend', [
                'calling_code' => $request->calling_code,
                'identifier' => $request->identifier,
                'title' => __( 'user.otp_email_success' ),
                'note' =>  __( 'user.otp_email_success_note', [ 'title' => $phoneNumber ] )
            ] );

            DB::commit();

            // Mail::to( $currentTmp->email )->send(new OtpMail( $updateTmpUser ));

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

        $notifications->when( $request->is_read != '' , function( $query ) {
            if ( request( 'is_read' ) == 0 ) {
                return $query->whereNull( 'user_notification_seens.id' );
            } else {
                return $query->where( 'user_notification_seens.id', '>', 0 );
            }
        } );

        $notifications->when( $request->notification != '' , function( $query ) use( $request ) {
            return $query->where( 'user_notifications.id', $request->notification );
        } );

        $notifications->orderBy( 'user_notifications.created_at', 'DESC' );

        $notifications = $notifications->simplePaginate( empty( $request->per_page ) ? 100 : $request->per_page );

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

        return response()->json( [
            'message' => __( 'notification.notification_seen' ),
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

        Helper::sendNotification( $affiliate->user_id, $messageContent );
        
    }
}