<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

use App\Models\{
    Administrator,
    AppVersion,
    BirthdayGiftSetting,
    Option,
    ReferralGiftSetting,
};

use Carbon\Carbon;


use Illuminate\Support\Facades\{
    DB,
    Validator,
};
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;


use PragmaRX\Google2FAQRCode\Google2FA;

class SettingService {

    public static function setupMFA( $request ) {

        $request->validate( [
            'authentication_code' => [ 'bail', 'required', 'numeric', 'digits:6', function( $attribute, $value, $fail ) {
               
                $google2fa = new Google2FA();

                $valid = $google2fa->verifyKey( request( 'mfa_secret' ), $value );
                if ( !$valid ) {
                    $fail( __( 'setting.invalid_code' ) );
                }
            } ],
            'mfa_secret' => 'required',
        ] );

        $updateAdministartor = Administrator::find( auth()->user()->id );
        $updateAdministartor->mfa_secret = \Crypt::encryptString( $request->mfa_secret );
        $updateAdministartor->save();

        return response()->json( [
            'status' => true,
        ] );
    }

    public static function settings() {

        $settings = Option::whereIn( 'option_name', [
            'contact_us_email',
        ] )->get();

        return $settings;
    }

    public static function maintenanceSettings() {

        $maintenance = Maintenance::where( 'type', 3 )->first();

        return $maintenance;
    }

    public static function updateMaintenanceSetting( $request ) {

        Maintenance::lockForUpdate()->updateOrCreate( [
            'type' => 3
        ], [
            'status' => $request->status,
        ] );

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.settings' ) ) ] ),
        ] );
    }

    
    public static function updateAppVersionSetting( $request ) {

        $validator = Validator::make( $request->all(), [
            'version_1' => [ 'required' ],
            'force_logout_1' => [ 'required', 'in:10,20' ],
            'version_2' => [ 'required' ],
            'force_logout_2' => [ 'required', 'in:10,20' ],
            // 'version_3' => [ 'required' ],
            // 'force_logout_3' => [ 'required', 'in:10,20' ],
        ] );

        $attributeName = [
            'version_1' => __( 'app_version.version' ),
            'force_logout_1' => __( 'app_version.force_logout' ),
            'version_2' => __( 'app_version.version' ),
            'force_logout_2' => __( 'app_version.force_logout' ),
            'version_3' => __( 'app_version.version' ),
            'force_logout_3' => __( 'app_version.force_logout' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateAppVersion = AppVersion::where( 'platform', 1 )->first();
            $updateAppVersion->version = $request->version_1;
            $updateAppVersion->force_logout = $request->force_logout_1;
            $updateAppVersion->save();

            $updateAppVersion = AppVersion::where( 'platform', 2 )->first();
            $updateAppVersion->version = $request->version_2;
            $updateAppVersion->force_logout = $request->force_logout_2;
            $updateAppVersion->save();

            // $updateAppVersion = AppVersion::where( 'platform', 3 )->first();
            // $updateAppVersion->version = $request->version_3;
            // $updateAppVersion->force_logout = $request->force_logout_3;
            // $updateAppVersion->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.app_versions' ) ) ] ),
        ] );
    }

    public static function getSpecialOtpSettings() {

        $option = Option::getSpecialOtpSettings();

        $settings = $option ? json_decode( $option->option_value, true ) : [
            'enabled' => false,
            'otp_code' => null,
            'expires_at' => null,
        ];

        return response()->json( [
            'data' => $settings,
        ] );
    }

    public static function updateSpecialOtpSetting( $request ) {

        $validator = Validator::make( $request->all(), [
            'enabled' => [ 'required', 'boolean' ],
        ] );

        $validator->validate();

        DB::beginTransaction();

        try {

            // Read with lock inside transaction to prevent race condition with generateSpecialOtp
            $existing = Option::lockForUpdate()->where( 'option_name', 'SPECIAL_REGISTRATION_OTP' )->first();
            $current = $existing ? json_decode( $existing->option_value, true ) : [];

            $newValue = [
                'enabled'    => (bool) $request->enabled,
                'otp_code'   => $current['otp_code'] ?? null,
                'expires_at' => $current['expires_at'] ?? null,
            ];

            if ( $existing ) {
                $existing->option_value = json_encode( $newValue );
                $existing->save();
            } else {
                Option::create( [
                    'option_name'  => 'SPECIAL_REGISTRATION_OTP',
                    'option_value' => json_encode( $newValue ),
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
            'message' => __( 'template.x_updated', [ 'title' => __( 'setting.special_otp_settings' ) ] ),
            'data' => $newValue,
        ] );
    }

    public static function generateSpecialOtp() {

        DB::beginTransaction();

        try {

            $otpCode  = (string) mt_rand( 100000, 999999 );
            $expiresAt = Carbon::now( 'Asia/Kuala_Lumpur' )->addHour()->format( 'Y-m-d H:i:s' );

            // Read with lock inside transaction to prevent race condition with updateSpecialOtpSetting
            $existing = Option::lockForUpdate()->where( 'option_name', 'SPECIAL_REGISTRATION_OTP' )->first();
            $current  = $existing ? json_decode( $existing->option_value, true ) : [];

            $newValue = [
                'enabled'    => $current['enabled'] ?? false,
                'otp_code'   => $otpCode,
                'expires_at' => $expiresAt,
            ];

            if ( $existing ) {
                $existing->option_value = json_encode( $newValue );
                $existing->save();
            } else {
                Option::create( [
                    'option_name'  => 'SPECIAL_REGISTRATION_OTP',
                    'option_value' => json_encode( $newValue ),
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
            'message' => __( 'setting.special_otp_generated' ),
            'data' => $newValue,
        ] );
    }

    public static function updateContactUsEmailSetting( $request ){

        $validator = Validator::make( $request->all(), [
            'contact_us_email' => [ 'required' ],
        ] );

        $attributeName = [
            'contact_us_email' => __( 'setting.contact_us_email' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            Option::lockForUpdate()->updateOrCreate(
                ['option_name' => 'contact_us_email'],
                ['option_value' => $request->contact_us_email]
            );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.contact_us_email' ) ) ] ),
        ] );
    }

}