<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

use App\Models\{
    Administrator,
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
            'DBD_BANK',
            'DBD_ACCOUNT_HOLDER',
            'DBD_ACCOUNT_NO',
            'WD_SERVICE_CHARGE_TYPE',
            'WD_SERVICE_CHARGE_RATE',
        ] )->get();

        return $settings;
    }

    public static function giftSettings() {
        $birthday = BirthdayGiftSetting::with( 'voucher' )->first();
        $referral = ReferralGiftSetting::with( 'voucher' )->first();

        $data['birthday'] = $birthday ?? null;
        $data['referral'] = $referral ?? null;

        return $data;
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

    public static function updateBirthdayGiftSetting( $request ) {

        DB::beginTransaction();

        $validator = Validator::make( $request->all(), [
            'reward_type' => [ 'required', 'in:1,2' ],
            'voucher' => [ $request->reward_type == 1 ? 'nullable' : 'required', 'exists:vouchers,id' ],
            'reward_value' => [ $request->reward_type == 1 ? 'required' : 'nullable', 'numeric', 'gte:0' ],
            'enable' => [ 'required', 'in:10,20' ],
        ] );

        $attributeName = [
            'reward_type' => __( 'setting.reward_type' ),
            'voucher' => __( 'setting.voucher' ),
            'reward_value' => __( 'setting.reward_value' ),
            'enable' => __( 'setting.enable' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $gift = BirthdayGiftSetting::first();
            if( $gift ) {
                $gift->reward_type = $request->reward_type;
                $gift->voucher_id = $request->voucher;
                $gift->reward_value = $request->reward_value;
                $gift->status = $request->enable;
                $gift->save();
            } else {
                $create = BirthdayGiftSetting::create( [
                    'reward_type' => $request->reward_type,
                    'voucher_id' => $request->voucher,
                    'reward_value' => $request->reward_value,
                    'status' => $request->enable,
                ] );
            }

            DB::commit();
            return response()->json( [
                'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'setting.birthday_gift_settings' ) ) ] ),
            ] );

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }
    }

    public static function updateReferralGiftSetting( $request ) {

        DB::beginTransaction();

        $validator = Validator::make( $request->all(), [
            'reward_type' => [ 'required', 'in:1,2' ],
            'voucher' => [ $request->reward_type == 1 ? 'nullable' : 'required', 'exists:vouchers,id' ],
            'expiry_day' => [ 'required', 'numeric', 'gte:0' ],
            'reward_value' => [ $request->reward_type == 1 ? 'required' : 'nullable', 'numeric', 'gte:0' ],
            'enable' => [ 'required', 'in:10,20' ],
        ] );

        $attributeName = [
            'reward_type' => __( 'setting.reward_type' ),
            'voucher' => __( 'setting.voucher' ),
            'expiry_day' => __( 'setting.expiry_day' ),
            'reward_value' => __( 'setting.reward_value' ),
            'enable' => __( 'setting.enable' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $gift = ReferralGiftSetting::first();
            if( $gift ) {
                $gift->reward_type = $request->reward_type;
                $gift->voucher_id = $request->voucher;
                $gift->expiry_day = $request->expiry_day;
                $gift->reward_value = $request->reward_value;
                $gift->status = $request->enable;
                $gift->save();
            } else {
                $create = ReferralGiftSetting::create( [
                    'reward_type' => $request->reward_type,
                    'voucher_id' => $request->voucher,
                    'expiry_day' => $request->expiry_day,
                    'reward_value' => $request->reward_value,
                    'status' => $request->enable,
                ] );
            }

            DB::commit();
            return response()->json( [
                'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'setting.referral_gift_settings' ) ) ] ),
            ] );

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }
    }

}