<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Crypt,
    Hash,
    Http,
    Storage
};

use App\Services\{
    UserService,
};

use App\Models\{
    User,
};

use Helper;

class UserController extends Controller {

    public function __construct() {}

    /**
     * 1. Create an user
     * 
     * <strong>device_type</strong><br>
     * 1: iOS<br>
     * 2: Android<br>
     * 
     * @sort 1
     * 
     * @group User API
     * 
     * @bodyParam identifier string required The temporary user ID during request OTP. Example: eyJpdiI...
     * @bodyParam phone_number string required The phone_number for register. Example: 0123982334
     * @bodyParam email string required The email for register. Example: abc@gmail.com
     * @bodyParam first_name string required The first_name for register. Example: tester
     * @bodyParam last_name string required The last_name for register. Example: tan
     * @bodyParam otp_code string required The otp for register. Example: 123456
     * @bodyParam password string required The password for register. Example: abcd1234
     * @bodyParam password_confirmation string required The confirmation password. Example: abcd1234
     * @bodyParam invitation_code string The invitation code of referral. Example: AASSCC
     * @bodyParam device_type integer optional The device type required with register_token. Example: 1
     * @bodyParam register_token string optional The device token to receive notification. Example: 45ab6cc6-bcaa-461e-af5d-ea402e5b93da
     * 
     */
    public function registerUser( Request $request ) {

        return UserService::registerUser( $request );
    }

    /**
     * 2. Login an user - Email
     * 
     * <strong>device_type</strong><br>
     * 1: iOS<br>
     * 2: Android<br>
     * 
     * @sort 2
     * 
     * 
     * @group User API
     * 
     * @bodyParam email string required The email for login. Example: abc@gmail.com
     * @bodyParam password string required The password for login. Example: abcd1234
     * @bodyParam device_type integer optional The device type required with register_token. Example: 1
     * @bodyParam register_token string optional The device token to receive notification. Example: 45ab6cc6-bcaa-461e-af5d-ea402e5b93da
     * 
     */
    public function loginUser( Request $request ) {

        return UserService::loginUser( $request );
    }


    /**
     * 3. Login an user - Social
     * @sort 2
     * 
     * <strong>platform</strong></br>
     * 1: Google<br>
     * 2: Facebook<br>
     * 3: Apple ID<br>
     * 
     * <strong>device_type</strong><br>
     * 1: iOS<br>
     * 2: Android<br>
     * 
     * @group User API
     * 
     * @bodyParam identifier string required The email for social login. Example: sso@mail.com
     * @bodyParam platform interger required The platform for login. Example: 1
     * @bodyParam email string optional The email for user social login. Example: sso@mail.com
     * @bodyParam device_type integer optional The device type required with register_token. Example: 1
     * @bodyParam register_token string optional The device token to receive notification. Example: 45ab6cc6-bcaa-461e-af5d-ea402e5b93da
     * 
     */
    public function loginUserSocial( Request $request ) {

        return UserService::loginUserSocial( $request );
    }

    /**
     * 4. Request an OTP
     * @sort 3
     * 
     * <strong>request_type</strong><br>
     * 1: Register<br>
     * 2: Forget Password<br>
     * 
     * @group User API
     * 
     * @bodyParam phone_number string required The phone_number for register. Example: 0123982334
     * @bodyParam email string required The email for register. Example: abc@gmail.com
     * @bodyParam first_name string required The first_name for register. Example: tester
     * @bodyParam last_name string required The last_name for register. Example: tan
     * @bodyParam age_group string required The age_group for register. Example: 10-18
     * @bodyParam password string required The password for register. Example: abcd1234
     * @bodyParam password_confirmation string required The confirmation password. Example: abcd1234
     * @bodyParam invitation_code string The invitation code of referral. Example: AASSCC
     * @bodyParam request_type integer required The request type for OTP. Example: 1
     * 
     */
    public function requestOtp( Request $request ) {

        return UserService::requestOtp( $request );
    }

    /**
     * 5. Resend an OTP
     * @sort 4
     * 
     * <strong>request_type</strong><br>
     * 2: Resend<br>
     * 
     * @group User API
     * 
     * @bodyParam identifier string required The temporary user ID during request OTP. Example: eyJpdiI...
     * @bodyParam request_type integer required The request type for OTP. Example: 2
     * 
     */
    public function resendOtp( Request $request ) {
        $request->merge( [
            'action' => 'resend'
        ] );

        return UserService::requestOtp( $request );
    }

    /**
     * 6. Resend Forgot password OTP
     * @sort 6
     * 
     * <strong>request_type</strong><br>
     * 2: resend <br>
     * 
     * @group User API
     * 
     * @bodyParam identifier string required The temporary user ID during request OTP. Example: eyJpdiI...
     * @bodyParam request_type integer required The request type for OTP. Example: 2
     * 
     */
    public function resendForgotPasswordOtp( Request $request ) {

        return UserService::forgotPasswordOtp( $request );
    }

    /**
     * 7. Get user
     * @sort 7
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * 
     */ 
    public function getUser( Request $request ) {
        
        return UserService::getUser();
    }

    /**
     * 8. Update user
     * @sort 8
     * 
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * @bodyParam username string The username to update. Example: John
     * @bodyParam first_name string The first_name to update. Example: wick
     * @bodyParam last_name string The last_name to update. Example: John
     * @bodyParam email string The email to update. Example: john@email.com
     * @bodyParam date_of_birth string The date of birth to update. Example: 2022-01-01
     * @bodyParam to_remove integer Indicate remove photo or not. Example: 1
     * @bodyParam invitation_code string The invitation_code of upline. Example: abcdef
     * @bodyParam profile_picture file The photo to update. Will create when empty
     * 
     */
    public function updateUserApi( Request $request ) {

        return UserService::updateUserApi( $request );
    }

    /**
     * 9. Update user password
     * @sort 9
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * @bodyParam old_password string required The old password of current user. Example: 1234abcd
     * @bodyParam password string required The new password to change. Example: abcd1234
     * @bodyParam password_confirmation string required The confirm password of new password to change. Example: abcd1234
     * 
     */    
    public function updateUserPassword( Request $request ) {

        return UserService::updateUserPassword( $request );
    }

    /**
     * 10. Forgot Password (Request Otp)
     * @sort 10
     * 
     * Request an unique identifier to reset password.
     * 
     * @group User API
     * 
     * @bodyParam email string The email to update. Example: john@email.com
     * @bodyParam request_type integer required The request type for OTP. Example: 1
     * 
     */
    public function forgotPasswordOtp( Request $request ) {

        return UserService::forgotPasswordOtp( $request );
    }

    /**
     * 11. verify otp code for forgot password
     * @sort 11
     * @group User API
     * 
     * @bodyParam identifier string required The unique_identifier from forgot password. Example: WLnvrJw6YYK
     * @bodyParam otp_code string The otp code to verify password reset. Example: 123456 
     * 
     */
    public function verifyOtp( Request $request ) {

        return UserService::verifyOtp( $request );
    }

    /**
     * 12. Reset Password
     * @sort 12
     * @group User API
     * 
     * @bodyParam identifier string required The unique_identifier from forgot password. Example: WLnvrJw6YYK
     * @bodyParam otp_code string The otp code to verify password reset. Example: 123456 
     * @bodyParam password string required The new password to perform password reset. Example: abcd1234
     * @bodyParam password_confirmation string required The new password confirmation to perform password reset. Example: abcd1234
     * 
     */
    public function resetPassword( Request $request ) {

        return UserService::resetPassword( $request );
    }

    /**
     * 13. Delete Verification
     * @sort 13
     * 
     * @group User API
     * 
     * @authenticated
     * @bodyParam password string required The password to perform account delete checking. Example: abcd1234
     * 
     * 
     */ 
    public function deleteVerification( Request $request ) {
        
        return UserService::deleteVerification( $request );
    }

    /**
     * 14. Delete Confirm
     * @sort 14
     * 
     * @group User API
     * 
     * @authenticated
     * @bodyParam password string required The password to perform account delete checking. Example: abcd1234
     * 
     * 
     */ 
    public function deleteConfirm( Request $request ) {
        
        return UserService::deleteConfirm( $request );
    }

     /**
     * 15. Get notifications
     * 
     * <strong>is_read</strong><br>
     * 0: New<br>
     * 1: Read<br>
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * @queryParam is_read integer Leave empty for all. Example: 1
     * @queryParam per_page integer Show how many record in a page. Leave blank for default (100). Example: 5
     * @queryParam notification integer optional The notification ID of notification. Example: 5
     * 
     */
    public function getNotifications( Request $request ) {

        return UserService::getNotifications( $request );
    }

    /**
     * 16. Update notification seen
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * 
     * @bodyParam notification integer required The notification ID of notification. Example: 5
     * 
     */ 
    public function updateNotificationSeen( Request $request ) {

        return UserService::updateNotificationSeen( $request );
    }

    /**
     * 17. Test notification
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * @bodyParam title string optional The notification title to test. ( Default will be: test-notification ). Example: test-notification
     * @bodyParam content string optional The notification content to test. ( Default will be: test-notification-content ). Example: test-notification-content
     * @bodyParam register_token string optional The target device token. ( Default will be following current authenticated user's register token ).
     * @bodyParam app_id string optional The one signal app id. ( Default will be following current settings ).
     * @bodyParam api_key string optional The one signal api_key ( Default will be following current settings ).
     * 
     */ 
    public function testNotification( Request $request ) {

        return UserService::testNotification( $request );
    }

    /**
     * 18. sendContactUsMail
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * @bodyParam name string required The name. Example: abc
     * @bodyParam email string required The email. Example: abc@example.com
     * @bodyParam phone_number string required The phone number. Example: 1234567890
     * @bodyParam message string required The message. Example: abc
     * 
     */ 
    public function sendContactUsMail( Request $request ) {

        return UserService::sendContactUsMail( $request );
    }

}