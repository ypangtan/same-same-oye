<?php

use App\Http\Controllers\Api\{
    UserController,
    MailContentController,
    WalletController,
    VendingMachineController,
    MenuController,
    CartController,
    OrderController,
    VoucherController,
    CheckinController,
    ProductBundleController,
    ApiRequestController,
    BannerController,
    AnnouncementController,
    LuckyDrawRewardController,
    PointsController,
    RankController,
};

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* Start Public route */

Route::prefix( 'contact-us' )->group( function() {
    Route::post('/', [MailContentController::class, 'createEnquiryMail']);
} );

Route::post( 'otp', [ UserController::class, 'requestOtp' ] )->middleware( 'log.cart.order' );
Route::post( 'otp/resend', [ UserController::class, 'resendOtp' ] )->middleware( 'log.cart.order' );

Route::prefix( 'users' )->middleware( 'log.cart.order' )->group( function() {
    Route::post( '/', [ UserController::class, 'registerUser' ] );
    Route::post( 'login', [ UserController::class, 'loginUser' ] );
    Route::post( 'login-social', [ UserController::class, 'loginUserSocial' ] );
    Route::post( 'check-phone-number', [ UserController::class, 'checkPhoneNumber' ] );
    Route::post( 'forgot-password', [ UserController::class, 'forgotPasswordOtp' ] );
    Route::post( 'reset-password', [ UserController::class, 'resetPassword' ] );
} );

Route::prefix( 'banners' )->group( function() {
    Route::get( '/', [ BannerController::class, 'getBanners' ] );
    Route::any( 'details', [ BannerController::class, 'oneBanner' ] );
} );

Route::prefix( 'lucky-draw-rewards' )->group( function() {
    Route::get( '/', [ LuckyDrawRewardController::class, 'searchLuckyDrawRewards' ] );
} );

Route::prefix( 'ranks' )->group( function() {
    Route::get( '/', [ RankController::class, 'getAllRanks' ] );
} );

Route::prefix( 'pop_announcements' )->group( function() {
    Route::get( '/get-all-pop-announcements', [ AnnouncementController::class, 'getAllPopAnnouncements' ] );
} )

/* End Public route */

/* Start Protected route */

Route::middleware( 'auth:user' )->group( function() {

    Route::prefix( 'users' )->middleware( 'log.cart.order' )->group( function() {
        Route::get( '/', [ UserController::class, 'getUser' ] );
        Route::post( 'delete-verification', [ UserController::class, 'deleteVerification' ] );
        Route::post( 'delete-confirm', [ UserController::class, 'deleteConfirm' ] );
        Route::post( '/update', [ UserController::class, 'updateUserApi' ] );
        Route::post( '/update-password', [ UserController::class, 'updateUserPassword' ] );

        Route::get( 'notifications', [ UserController::class, 'getNotifications' ] );
        Route::post( 'notification', [ UserController::class, 'updateNotificationSeen' ] );

        Route::post( 'test-notification', [ UserController::class, 'testNotification' ] );

    } );
    Route::prefix( 'announcements' )->middleware( 'log.cart.order' )->group( function() {
        Route::get( '/', [ AnnouncementController::class, 'getAnnouncements' ] );
        Route::post( 'close', [ AnnouncementController::class, 'claim' ] );
    } );

    Route::prefix( 'vouchers' )->middleware( 'log.cart.order' )->group( function() {
        Route::get( '/', [ VoucherController::class, 'getVouchers' ] );
        Route::post( 'claim-voucher', [ VoucherController::class, 'claimVoucher' ] );
    } );

    Route::prefix( 'checkin' )->middleware( 'log.cart.order' )->group( function() {
        Route::get( '/', [ CheckinController::class, 'getCheckinHistory' ] );
        Route::post( '', [ CheckinController::class, 'checkin' ] );
        Route::get( 'rewards', [ CheckinController::class, 'getCheckinRewards' ] );
    } );

    Route::prefix( 'points' )->middleware( 'log.cart.order' )->group( function() {
        Route::get( '', [ PointsController::class, 'getPoints' ] );
        Route::post( 'redeem', [ PointsController::class, 'redeemPoints' ] );
        Route::get( 'history', [ PointsController::class, 'getPointsRedeemHistory' ] );
        Route::get( 'conversion-rate', [ PointsController::class, 'getConversionRate' ] );
    } );
    
});


