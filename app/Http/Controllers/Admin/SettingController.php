<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    AppVersionService,
    SettingService,
};

use Helper;

use PragmaRX\Google2FAQRCode\Google2FA;

class SettingController extends Controller
{
    public function firstSetup( Request $request ) {

        if ( !empty( auth()->user()->mfa_secret ) ) {
            return redirect()->route( 'admin.dashboard' );
        }

        $this->data['header']['title'] = __( 'template.first_setup' );
        
        $this->data['content'] = 'admin.setting.first_setup';

        $google2fa = new Google2FA();

        $secretKey = $google2fa->generateSecretKey();

        $qrCodeUrl = $google2fa->getQRCodeInline(
            Helper::websiteName(),
            auth()->user()->email,
            $secretKey
        );

        $this->data['data']['mfa_qr'] = $qrCodeUrl;
        $this->data['data']['mfa_secret'] = $secretKey;

        return view( 'admin.main_pre_auth' )->with( $this->data );
    }

    public function setupMFA( Request $request ) {

        return SettingService::setupMFA( $request );
    }

    public function index() {

        $this->data['header']['title'] = __( 'template.settings' );
        $this->data['content'] = 'admin.setting.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.settings' ),
            'title' => __( 'template.settings' ),
            'mobile_title' => __( 'template.settings' ),
        ];

        $this->data['data']['reward_types'] = [
            '1' => __('checkin_reward.points'),
            '2' => __('checkin_reward.voucher'),
        ];

        // $this->data['data']['settings'] = SettingService::settings();

        return view( 'admin.main' )->with( $this->data );
    }

    public function settings( Request $request ) {

        return SettingService::settings();
    }
    
    public function bonusSettings( Request $request ) {

        return SettingService::bonusSettings();
    }
    
    public function lastestAppVersion( Request $request ) {

        return AppVersionService::lastestAppVersion( $request );
    }
    
    public function giftSettings( Request $request ) {

        return SettingService::giftSettings();
    }

    public function maintenanceSettings( Request $request ) {

        return SettingService::maintenanceSettings();
    }

    public function updateBonusSetting( Request $request ) {

        return SettingService::updateBonusSetting( $request );
    }

    public function updateMaintenanceSetting( Request $request ) {

        return SettingService::updateMaintenanceSetting( $request );
    }

    public function updateBirthdayGiftSetting( Request $request ) {

        return SettingService::updateBirthdayGiftSetting( $request );
    }

    public function updateReferralGiftSetting( Request $request ) {

        return SettingService::updateReferralGiftSetting( $request );
    }

    public function updateAppVersion( Request $request ) {

        return SettingService::updateAppVersion( $request );
    }
}
