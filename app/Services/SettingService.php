<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

use App\Models\{
    Administrator,
    Option,    
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
            'DBD_BANK',
            'DBD_ACCOUNT_HOLDER',
            'DBD_ACCOUNT_NO',
            'WD_SERVICE_CHARGE_TYPE',
            'WD_SERVICE_CHARGE_RATE',
        ] )->get();

        return $settings;
    }

    public static function bonusSettings() {

        $settings = Option::whereIn( 'option_name', 
            ['CONVERTION_RATE',
            'REFERRAL_REGISTER',
            'REFERRAL_SPENDING',
            'REGISTER_BONUS',
            'TAXES'])->get();

        return $settings;
    }

    public static function maintenanceSettings() {

        $maintenance = Maintenance::where( 'type', 3 )->first();

        return $maintenance;
    }

    public static function updateBonusSetting( $request ) {

        DB::beginTransaction();

        $validator = Validator::make( $request->all(), [
            'convertion_rate' => [ 'required', 'numeric', 'gte:0' ],
            'referral_register_bonus_points' => [ 'nullable', 'numeric', 'gte:0' ],
            'referral_spending_bonus_points' => [ 'nullable', 'numeric', 'gte:0' ],
            'register_bonus' => [ 'nullable', 'numeric', 'gte:0' ],
            'taxes' => [ 'nullable', 'numeric', 'gte:0' ],
        ] );

        $attributeName = [
            'convertion_rate' => __( 'setting.convertion_rate' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $options = [
                'CONVERTION_RATE' => $request->convertion_rate,
                'REFERRAL_REGISTER' => $request->referral_register_bonus_points ?? 0,
                'REFERRAL_SPENDING' => $request->referral_spending_bonus_points ?? 0,
                'REGISTER_BONUS' => $request->register_bonus ?? 0,
                'TAXES' => $request->taxes ?? 0,
            ];
            
            foreach ($options as $option_name => $option_value) {
                Option::lockForUpdate()->updateOrCreate(
                    ['option_name' => $option_name],
                    ['option_value' => $option_value]
                );
            }

            DB::commit();
            return response()->json( [
                'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'setting.bonus_settings' ) ) ] ),
            ] );

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.settings' ) ) ] ),
        ] );
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

}