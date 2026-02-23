<?php

namespace App\Helpers;

use Hashids\Hashids;
use Carbon\Carbon;

use App\Models\{
    OtpAction,
    TmpUser,
    Option,
    Order,
    Adjustment,
    Wallet,
    UserDevice,
    User,
    PresetPermission,
    Module,
    OtpLog,
    Rank,
    Voucher,
    WalletTransaction,
};

use Illuminate\Support\Facades\{
    Crypt,
    Route,
    Schema,
};

use Spatie\Permission\Models\{
    Permission,
};

use App\Services\{
    WalletService,
};

class Helper {

    public static function websiteName() {
        return config( 'app.name' );
    }

    public static function assetVersion() {
        return '?v=1.09';
    }

    public static function displayType() {
        return [
            '1' => __( 'item.display_type_1' ),
            '2' => __( 'item.display_type_2' ),
            '3' => __( 'item.display_type_3' ),
        ];
    }

    public static function wallets() {
        return [
            '1' => __( 'wallet.wallet_1' ),
            '2' => __( 'wallet.wallet_2' ),
        ];
    }

    public static function trxTypes() {
        return [
            '1' => __( 'wallet.topup' ),
            '2' => __( 'wallet.refund' ),
            '3' => __( 'wallet.manual_adjustment' ),
        ];
    }

    public static function moduleActions() {

        return [
            'add',
            'view',
            'edit',
            'delete'
        ];
    }

    public static function additionPermission() {
        return [
            // [ 'name' => 'users', 'action' => 'adjust_3' ],
        ];
    }

    public static function unusedModule() {
        $array = [
            // 'announcements',
        ];

        return $array;
    }

    public static function unusedAction () {
        $array = [
            // [ 'name' => 'users', 'action' => 'soft_delete' ],
        ];

        return $array;
    }

    public static function needReorder( $table ) {
        return Schema::hasColumn( $table, 'priority' ) ? 1 : 2;
    }

    public static function numberFormat( $number, $decimal, $displayComma = false, $isRound = false ) {
        $formatted = '';
        if ( $isRound ) {
            $formatted = number_format( $number, $decimal );
        } else {
            $formatted = number_format( bcdiv( $number, 1, $decimal ), $decimal );
        }

        if ( $displayComma ) {
            return $formatted;
        } else {
            return str_replace( ',', '', $formatted );
        }
    }

    public static function numberFormatNoComma( $number, $decimal ) {
        return str_replace( ',', '', number_format( $number, $decimal ) );
    }

    public static function curlGet( $endpoint, $header = array(

    ) ) {

        $curl = curl_init();

        curl_setopt_array( $curl, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
        ) );

        $response = curl_exec ($curl );
        $httpCode  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error( $curl );
        
        curl_close( $curl );

        if( $error ) {
            return [
                'status' => $httpCode,
                'data' => false
            ];
        } else {
            return [
                'status' => $httpCode,
                'data' => $response
            ];
        }
    }

    public static function curlPost( $endpoint, $data, $header = array(
        "accept: */*",
        "accept-language: en-US,en;q=0.8",
        "content-type: application/json",
    ) ) {

        $curl = curl_init();
        
        curl_setopt_array( $curl, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $header
        ) );
        $response = curl_exec ($curl );

        $error = curl_error( $curl );

        curl_close( $curl );
        
        if( $error ) {
            return false;
        } else {
            return $response;
        }
    }

    public static function exportReport( $html, $model ) {

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
        $spreadsheet = $reader->loadFromString( $html );

        foreach( $spreadsheet->getActiveSheet()->getColumnIterator() as $column ) {
            $spreadsheet->getActiveSheet()->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }

        $filename = $model . '_' . date( 'ymd_His' ) . '.xlsx';

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter( $spreadsheet, 'Xlsx' );
        $writer->save( 'storage/'.$filename );

        $content = file_get_contents( 'storage/'.$filename );

        header( "Content-Disposition: attachment; filename=".$filename );
        header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
        unlink( 'storage/'.$filename );
        exit( $content );
    }

    public static function columnIndex( $object, $search ) {
        foreach ( $object as $key => $o ) {
            if ( $o['id'] == $search ) {
                return $key;
            }
        }
    }

    public static function encode( $id ) {

        $hashids = new Hashids( config( 'app.key' ) );

        return $hashids->encode( $id );
    }

    public static function decode( $id ) {

        $hashids = new Hashids( config( 'app.key' ) );

        return $hashids->decode( $id )[0];
    }

    public static function getDisplayTimeUnit( $createdAt ) {

        $created = Carbon::createFromFormat( 'Y-m-d H:i:s', $createdAt, 'UTC' )->timezone( 'Asia/Kuala_Lumpur' );
        $now = Carbon::now()->timezone( 'Asia/Kuala_Lumpur' );

        if ( $created->format( 'd' ) != $now->format( 'd' ) ) {

            $difference = $created->clone()->startOfDay()->diff( $now->startOfDay() )->days;
            if ( $difference == 1 ) {
                return __( 'template.yesterday' ) . ' ' . $created->format( 'H:i' );
            } else {
                return $created->format( 'd-m-Y H:i' );
            }

        } else {
            return $created->format( 'H:i' );
        }
    }   

    public static function requestOtp( $action, $data = [] ) {

        $expireOn = Carbon::now()->addMinutes( '10' );
        $type = 1;

        if ( $action == 'register' ) {

            $callingCode = $data['calling_code'];
            $phoneNumber = $data['phone_number'];
            $email = $data['email'];
            $type = 1;

            $createOtp = TmpUser::create( [
                'calling_code' => $data['calling_code'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'otp_code' => mt_rand( 100000, 999999 ),
                'status' => 1,
                'expire_on' => $expireOn,
            ] );

            $otp = $createOtp->otp_code;
            $body = 'Your OTP for SSO ' . $action . ' is ' . $createOtp->otp_code;

        } 
        else if ( $action == 'resend' ) {

            $callingCode = $data['calling_code'];
            $tmpUser = $data['identifier'];
            $type = 1;

            $createOtp = TmpUser::find( $tmpUser );
            $createOtp->otp_code = mt_rand( 100000, 999999 );
            $createOtp->expire_on = $expireOn;
            $createOtp->save();

            $phoneNumber = $createOtp->phone_number;
            $email = $createOtp->email;

            $otp = $createOtp->otp_code;
            $body = 'Your OTP for SSO ' . $action . ' is ' . $createOtp->otp_code;

        } 
        else if ( $action == 'forgot_password' ) {

            $callingCode = $data['calling_code'];
            $phoneNumber = $data['phone_number'];
            $email = $data['email'];      
            $type = 2;

            // set previous to status 10
            $resetOtps = OtpAction::where( 'user_id', $data['id'] )->where( 'status', 1 )->update(['status' => 10]);
            
            $createOtp = OtpAction::create( [
                'user_id' => $data['id'],
                'action' => $action,
                'otp_code' => mt_rand( 100000, 999999 ),
                'expire_on' => $expireOn,
            ] );

            $otp = $createOtp->otp_code;
            $body = 'Your OTP for SSO forgot password is ' . $createOtp->otp_code;

        }
        
        else if ( $action == 'resend_forget_password' ) {

            // $callingCode = $data['calling_code'];
            $tmpUser = $data['identifier'];
            $type = 2;

            $createOtp = OtpAction::with( 'user' )->find( $tmpUser );
            $createOtp->otp_code = mt_rand( 100000, 999999 );
            $createOtp->expire_on = $expireOn;
            $createOtp->save();

            $email = $createOtp->user->email;      

            $otp = $createOtp->otp_code;
            $body = 'Your OTP for SSO ' . $action . ' is ' . $createOtp->otp_code;

        } 
        
        else if ( $action == 'update_account' ) {

            $callingCode = $data['calling_code'];
            $phoneNumber = $data['phone_number'];
            $email = $data['email'];      
            $type = 3;
            
            $createOtp = OtpAction::create( [
                'user_id' => $data['id'],
                'action' => $action,
                'otp_code' => mt_rand( 100000, 999999 ),
                'expire_on' => $expireOn,
            ] );

            $otp = $createOtp->otp_code;
            $body = 'Your OTP for SSO update account is ' . $createOtp->otp_code;

        }else {

            $currentUser = auth()->user();

            $callingCode = $currentUser->calling_code;
            $phoneNumber = $currentUser->phone_number;
            $email = $data['email'];      
            $type = 1;

            $createOtp = OtpAction::create( [
                'user_id' => $currentUser->id,
                'action' => $action,
                'otp_code' => mt_rand( 100000, 999999 ),
                'expire_on' => $expireOn,
            ] );
            
            $otp = $createOtp->otp_code;
            $body = 'Your OTP for SSO ' . $action . ' is ' . $createOtp->otp_code;
        }

        // $mobile = $callingCode . $phoneNumber;
        // self::sendSMS( $mobile, $otp, $body );

        return [
            'action' => $action,
            'identifier' => Crypt::encryptString( $createOtp->id ),
            'otp_code' => $createOtp->otp_code,
            'type' => $type,
            'email' => $email
        ];
    }

    public static function sendSMS( $mobile, $otp, $message = '' ) {

        // $url = "http://cloudsms.trio-mobile.com/index.php/api/bulk_mt?";
        $url = config( 'services.sms.sms_url' ) ?? '';

        if( empty( $url ) ) {
            return 0;
        }
        
        $encodedMessage = rawurlencode($message);

        $request = array(
            'un' => config( 'services.sms.username' ),
            'pwd' => config( 'services.sms.password' ),
            'dstno' => $mobile,
            'msg' => $encodedMessage,
            'type' => 1,
            'agreedterm'=> 'YES',
        );

        $sendSMS = \Helper::curlGet( $url . '?' . http_build_query( $request ) );
                
        OtpLog::create( [
            'url' => $url . '?' . http_build_query( $request ),
            'method' => 'GET',
            'phone_number' => $mobile,
            'otp_code' => $otp,
            'raw_response' => json_encode( $sendSMS ),
        ] );

        return 0;
    }

    public static function sendNotification( $user, $message ){

        $devices = UserDevice::where( 'user_id', $user )->get();

        if( $devices ) {

            foreach( $devices as $device ){
                $header = [
                    'Content-Type: application/json; charset=utf-8',
                    'Authorization: BASIC ' . config( 'services.os.api_key' ),
                ];
    
                $json = [
                    'app_id' => config( 'services.os.app_id' ),
                    'contents' => [
                        'en' => ( is_array( $message ) && isset( $message['message_content'] ) )
                            ? strip_tags( $message['message_content']['en'] ?? '' )
                            : ( is_array( $message ) && isset( $message['message'] )
                                ? strip_tags( $message['message']['en'] ?? '' )
                                : strip_tags( (string) $message['message'] )
                            ),

                        'zh' => ( is_array( $message ) && isset( $message['message_content'] ) )
                            ? strip_tags( $message['message_content']['zh'] ?? '' )
                            : ( is_array( $message ) && isset( $message['message'] )
                                ? strip_tags( $message['message']['zh'] ?? '' )
                                : strip_tags( (string) $message['message'] )
                            ),
                    ],

                    'headings' => [
                        'en' => ( is_array( $message ) && isset( $message['message'] ) )
                            ? ( is_array( $message['message'] ) && isset( $message['message'] ) )
                            ? $message['message']['en']
                            : $message['message']
                            : 'SSO',

                        'zh' => ( is_array( $message ) && isset( $message['message'] ) )
                            ? ( is_array( $message['message'] ) && isset( $message['message'] ) )
                            ? $message['message']['zh']
                            : $message['message']
                            : 'SSO',
                    ],
                    'include_player_ids' => [
                        $device->register_token
                    ],
                    'data' => [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',
                        'status' => 'done',
                        'key' => $message['key'],
                        'id' => $message['id'],
                    ]
                ];
    
                \Log::info( '[OneSignal] Sending notification - Payload: ' . json_encode($json) );
                \Log::info( '[OneSignal] Headers: ' . json_encode($header) );

                $sendNotification = Helper::curlPost( 'https://onesignal.com/api/v1/notifications', json_encode( $json ), $header );

                \Log::info( 'oneSignal log : ' . $sendNotification );
            }
        }    

    }

    public static function sendMultiNotification( $user, $message ){

        $devices = UserDevice::whereIn( 'user_id', $user )->pluck('register_token')->toArray();
        if( $devices ) {
            $devices = array_values( array_unique( $devices ) );

            $header = [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: BASIC ' . config( 'services.os.api_key' ),
            ];

            $json = [
                'app_id' => config( 'services.os.app_id' ),
                'contents' => [
                    'en' => ( is_array( $message ) && isset( $message['message_content'] ) )
                        ? strip_tags( $message['message_content']['en'] ?? '' )
                        : ( is_array( $message ) && isset( $message['message'] )
                            ? strip_tags( $message['message']['en'] ?? '' )
                            : strip_tags( (string) $message['message'] )
                        ),

                    'zh' => ( is_array( $message ) && isset( $message['message_content'] ) )
                        ? strip_tags( $message['message_content']['zh'] ?? '' )
                        : ( is_array( $message ) && isset( $message['message'] )
                            ? strip_tags( $message['message']['zh'] ?? '' )
                            : strip_tags( (string) $message['message'] )
                        ),
                ],

                'headings' => [
                    'en' => ( is_array( $message ) && isset( $message['message'] ) )
                        ? ( is_array( $message['message'] ) && isset( $message['message'] ) )
                        ? $message['message']['en']
                        : $message['message']
                        : 'SSO',

                    'zh' => ( is_array( $message ) && isset( $message['message'] ) )
                        ? ( is_array( $message['message'] ) && isset( $message['message'] ) )
                        ? $message['message']['zh']
                        : $message['message']
                        : 'SSO',
                ],
                'include_player_ids' => $devices,
                'data' => [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound' => 'default',
                    'status' => 'done',
                    'key' => $message['key'],
                    'id' => $message['id'],
                ]
            ];

            $sendNotification = Helper::curlPost( 'https://onesignal.com/api/v1/notifications', json_encode( $json ), $header );
        }
    }

    public static function generateAdjustmentNumber()
    {
        return now()->format('YmdHis');
    }

    public static function generateOrderReference()
    {
        return 'ODR-' . now()->format('YmdHis');
    }

    public static function generateBundleReference()
    {
        return 'BDL-' . now()->format('YmdHis');
    }
    
    public static function generateCartSessionKey()
    {
        return 'CART-' . now()->format('YmdHis');
    }

    public static function generatePaymentHash( $data ){

        $password = config( 'services.eghl.merchant_password' );
        $serviceId = config( 'services.eghl.merchant_id' );

        $hashCombine = $password . $serviceId . $data['PaymentID'] . $data['MerchantReturnURL']
        .  $data['MerchantCallbacklURL'] . $data['MerchantApprovalURL'] . $data['MerchantUnApprovalURL']. $data['Amount'] . $data['CurrencyCode'] . $data['CustIP']
        . $data['PageTimeout'];

        return hash('sha256', $hashCombine);
    }

    public static function generateResponseHash( $data ){

        $password = config( 'services.eghl.merchant_password' );
        $serviceId = config( 'services.eghl.merchant_id' );

        $hashCombine = $password . $data['TxnID'] . $serviceId . $data['PaymentID'] . $data['TxnStatus']
        . $data['Amount'] . $data['CurrencyCode'] . $data['AuthCode']
        . $data['OrderNumber'];

        return hash('sha256', $hashCombine);
    }

    public static function initiatePermissions() {

        foreach ( Route::getRoutes() as $route ) {
            
            $routeName = $route->getName();
            if ( str_contains( $route->getName(), 'admin.module_parent.' ) ) {
                $routeName = str_replace( 'admin.module_parent.', '', $routeName );
                $routeName = str_replace( '.index', '', $routeName );
                $moduleName = \Str::plural( $routeName );

                $module = Module::firstOrCreate( [
                    'name' => $moduleName,
                    'guard_name' => 'admin',
                ] );

                if ( $module ) {

                    foreach ( Helper::moduleActions() as $action ) {
                        PresetPermission::firstOrCreate( [
                            'module_id' => $module->id,
                            'action' => $action,
                        ] );
                    }
                }
            }
        }

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
    
}
