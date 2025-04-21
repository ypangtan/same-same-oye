<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Hash,
    Validator,
};

use Illuminate\Validation\Rules\Password;

use App\Models\{
    Administrator,
    User,
    Role as RoleModel
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class AdministratorService
{
    public static function allAdministrators( $request ) {

        $administrator = Administrator::select( 'administrators.*' );

        $filterObject = self::filter( $request, $administrator );
        $administrator = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $administrator->orderBy( 'created_at', $dir );
                    break;
                case 2:
                    $administrator->orderBy( 'name', $dir );
                    break;
                case 3:
                    $administrator->orderBy( 'email', $dir );
                    break;
                case 4:
                    $administrator->orderBy( 'role', $dir );
                    break;
            }
        }

        $administratorCount = $administrator->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $administrators = $administrator->skip( $offset )->take( $limit )->get();

        if ( $administrators ) {
            $administrators->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = Administrator::count();

        $data = [
            'administrators' => $administrators,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $administratorCount : $totalRecord,
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

                $model->whereBetween( 'administrators.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->registered_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'administrators.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->username ) ) {
            $model->where( 'name', 'LIKE', '%' . $request->username . '%' );
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

        if ( !empty( $request->role ) ) {
            $model->where( 'role', $request->role );
            $filter = true;
        }

        if (!empty($request->role_key)) {
            $model->role($request->role_key);
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'name', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneAdministrator( $request ) {

        $administrator = Administrator::find( Helper::decode( $request->id ) );

        return response()->json( $administrator );
    }

    public static function createAdministrator( $request ) {

        $validator = Validator::make( $request->all(), [
            'username' => [ 'required', 'alpha_dash', 'unique:administrators,name', new CheckASCIICharacter ],
            'email' => [ 'required', 'bail', 'unique:administrators,email', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'required' ],
            'phone_number' => [ 'required' ],
            'password' => [ 'required', Password::min( 8 ) ],
            'role' => [ 'nullable' ],
        ] );

        $attributeName = [
            'username' => __( 'administrator.username' ),
            'email' => __( 'administrator.email' ),
            'fullname' => __( 'administrator.fullname' ),
            'phone_number' => __( 'administrator.phone_number' ),
            'password' => __( 'administrator.password' ),
            'role' => __( 'administrator.role' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $basicAttribute = [
                'name' => strtolower( $request->username ),
                'email' => strtolower( $request->email ),
                'phone_number' => $request->phone_number,
                'fullname' => $request->fullname,
                'role' => $request->role ?? 3,
                'password' => Hash::make( $request->password ),
                'status' => 10,
            ];

            $createAdmin = Administrator::create( $basicAttribute );
    
            $roleModel = RoleModel::find( $request->role ?? 3 );
    
            $createAdmin->syncRoles( [ $roleModel->name ] );

            $createUserObject = [
                'name' => strtolower( $request->username ),
                'fullname' => $request->fullname,
                'email' => strtolower( $request->email ),
                'phone_number' => null,
                'password' => Hash::make( $request->password ),
                'status' => 10,
            ];

            $createUser = User::create( $createUserObject );

            $createAdmin->user_id = $createUser->id;
            $createAdmin->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.administrators' ) ) ] ),
        ] );
    }

    public static function updateAdministrator( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'username' => [ 'required', 'alpha_dash', 'unique:administrators,name,' . $request->id, new CheckASCIICharacter ],
            'email' => [ 'required', 'bail', 'unique:administrators,email,' . $request->id, 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'required' ],
            'phone_number' => [ 'required' ],
            'password' => [ 'nullable', Password::min( 8 ) ],
            'role' => [ 'required' ],
        ] );

        $attributeName = [
            'username' => __( 'administrator.username' ),
            'email' => __( 'administrator.email' ),
            'fullname' => __( 'administrator.fullname' ),
            'password' => __( 'administrator.password' ),
            'phone_number' => __( 'administrator.phone_number' ),
            'role' => __( 'administrator.role' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateAdministrator = Administrator::find( $request->id );
            $updateAdministrator->name = strtolower( $request->username );
            $updateAdministrator->email = strtolower( $request->email );
            $updateAdministrator->phone_number = $request->phone_number;
            $updateAdministrator->fullname = $request->fullname;
            $updateAdministrator->role = $request->role ?? $updateAdministrator->role;

            if ( !empty( $request->password ) ) {
                $updateAdministrator->password = Hash::make( $request->password );
            }

            $roleModel = RoleModel::find( $request->role ?? $updateAdministrator->role  );
            $updateAdministrator->syncRoles( [ $roleModel->name ] );

            $updateAdministrator->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.administrators' ) ) ] ),
        ] );
    }

    public static function logout() {

        activity()
            ->useLog( 'administrators' )
            ->withProperties( [
                'attributes' => [
                    'logout' => date( 'Y-m-d H:i:s' ),
                ]
            ] )
            ->log( 'admin logout' );
    }

    public static function verifyCode( $request ) {

        $request->validate( [
            'authentication_code' => [ 'bail', 'required', 'numeric', 'digits:6', function( $attribute, $value, $fail ) {

                $google2fa = new Google2FA();

                $secret = \Crypt::decryptString( auth()->user()->mfa_secret );
                $valid = $google2fa->verifyKey( $secret, $value );
                if ( !$valid ) {
                    $fail( __( 'setting.invalid_code' ) );
                }
            } ],
        ] );

        session( [
            'mfa-ed' => true
        ] );

        activity()
            ->useLog( 'administrators' )
            ->withProperties( [
                'attributes' => [
                    'new_login' => date( 'Y-m-d H:i:s' ),
                ]
            ] )
            ->log( 'admin login' );

        return response()->json( [
            'status' => true,
        ] );
    }

    public static function allOwners( $request ) {

        $owner = Administrator::with( [ 
            'owner'
        ] )->where( 'role', 3 )->where( 'status', 10 );

        $filterObject = self::filter( $request, $owner );
        $owner = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $owner->orderBy( 'created_at', $dir );
                    break;
                case 2:
                    $owner->orderBy( 'name', $dir );
                    break;
                case 3:
                    $owner->orderBy( 'email', $dir );
                    break;
                case 4:
                    $owner->orderBy( 'role', $dir );
                    break;
            }
        }

        $ownerCount = $owner->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $owners = $owner->skip( $offset )->take( $limit )->get();

        if ( $owners ) {
            $owners->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = Administrator::count();
        
        $data = [
            'owners' => $owners,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $ownerCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return $data;

    }

    public static function oneOwner( $request ) {
        $owner = Administrator::find( Helper::decode( $request->id ) );

        return response()->json( $owner );
    }

    public static function createOwner( $request ) {

        $validator = Validator::make( $request->all(), [
            'username' => [ 'required', 'alpha_dash', 'unique:administrators,name', new CheckASCIICharacter ],
            'email' => [ 'nullable', 'bail', 'unique:administrators,email', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'phone_number' => [ 'required', 'bail', 'unique:administrators,phone_number' ],
            'fullname' => [ 'required' ],
            'password' => [ 'required', Password::min( 8 ) ],
        ] );

        $attributeName = [
            'username' => __( 'administrator.username' ),
            'email' => __( 'administrator.email' ),
            'fullname' => __( 'administrator.fullname' ),
            'password' => __( 'administrator.password' ),
            'phone_number' => __( 'administrator.phone_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $basicAttribute = [
                'name' => strtolower( $request->username ),
                'email' => strtolower( $request->email ),
                'fullname' => $request->fullname,
                'phone_number' => $request->phone_number,
                'role' => 3,
                'password' => Hash::make( $request->password ),
                'status' => 10,
            ];

            $createOwner = Administrator::create( $basicAttribute );
    
            $roleModel = RoleModel::find( 3 );
    
            $createOwner->syncRoles( [ $roleModel->name ] );

            $createUserObject = [
                'name' => strtolower( $request->username ),
                'fullname' => $request->fullname,
                'email' => strtolower( $request->email ),
                'phone_number' => $request->phone_number,
                'password' => Hash::make( $request->password ),
                'status' => 10,
            ];

            $createUser = User::create( $createUserObject );

            $createOwner->user_id = $createUser->id;
            $createOwner->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.owners' ) ) ] ),
        ] );
    }

    public static function updateOwner( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'username' => [ 'required', 'alpha_dash', 'unique:administrators,name,' . $request->id, new CheckASCIICharacter ],
            'email' => [ 'nullable', 'bail', 'unique:administrators,email,' . $request->id, 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'phone_number' => [ 'required', 'bail', 'unique:administrators,phone_number,' .$request->id ],
            'fullname' => [ 'required' ],
            'password' => [ 'nullable', Password::min( 8 ) ],
        ] );

        $attributeName = [
            'username' => __( 'administrator.username' ),
            'email' => __( 'administrator.email' ),
            'fullname' => __( 'administrator.fullname' ),
            'phone_number' => __( 'administrator.phone_number' ),
            'password' => __( 'administrator.password' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateOwner = Administrator::find( $request->id );
            $updateOwner->name = strtolower( $request->username );
            $updateOwner->email = strtolower( $request->email );
            $updateOwner->phone_number = $request->phone_number;
            $updateOwner->fullname = $request->fullname;

            $updateOwner->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.owners' ) ) ] ),
        ] );
    }

}