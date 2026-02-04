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
            'version' => [ 'required' ],
            'force_logout' => [ 'required', 'in:10,20' ],
        ] );

        $attributeName = [
            'version' => __( 'app_version.version' ),
            'force_logout' => __( 'app_version.force_logout' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateAppVersions = AppVersion::get();
            foreach( $updateAppVersions as $updateAppVersion ) {
                $updateAppVersion->version = $request->version;
                $updateAppVersion->force_logout = $request->force_logout;
                $updateAppVersion->save();
            }

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