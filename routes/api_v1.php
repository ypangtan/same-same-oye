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

Route::post( 'otp', [ UserController::class, 'requestOtp' ] );
Route::post( 'otp/resend', [ UserController::class, 'resendOtp' ] );

Route::prefix( 'users' )->group( function() {
    Route::post( '/', [ UserController::class, 'registerUser' ] );
    Route::post( 'login', [ UserController::class, 'loginUser' ] );
    Route::post( 'check-phone-number', [ UserController::class, 'checkPhoneNumber' ] );
    Route::post( 'forgot-password', [ UserController::class, 'forgotPasswordOtp' ] );
    Route::post( 'reset-password', [ UserController::class, 'resetPassword' ] );
} );

/* End Public route */

/* Start Protected route */

Route::middleware( 'auth:user' )->group( function() {

    Route::prefix( 'users' )->group( function() {
        Route::get( '/', [ UserController::class, 'getUser' ] );
        Route::post( 'delete-verification', [ UserController::class, 'deleteVerification' ] );
        Route::post( 'delete-confirm', [ UserController::class, 'deleteConfirm' ] );
        Route::post( '/update', [ UserController::class, 'updateUserApi' ] );

        Route::get( 'notifications', [ UserController::class, 'getNotifications' ] );
        Route::post( 'notification', [ UserController::class, 'updateNotificationSeen' ] );

    } );
    
    Route::prefix( 'wallets' )->group( function() {
        Route::get( '', [ WalletController::class, 'getWallet' ] );
        Route::get( 'transactions', [ WalletController::class, 'getWalletTransactions' ] );
        Route::post( 'topup', [ WalletController::class, 'topup' ] );
    } );

    Route::prefix( 'points' )->group( function() {
        Route::get( 'histories', [ WalletController::class, 'getPointsHistories' ] );
    } );
    
    // New API routes
    Route::prefix( 'vending-machines' )->group( function() {
        Route::get( '/', [ VendingMachineController::class, 'getVendingMachines' ] );
    } );
    
    Route::prefix( 'menus' )->group( function() {
        Route::get( '/', [ MenuController::class, 'getMenus' ] );
        Route::get( 'get-selections', [ MenuController::class, 'getSelections' ] );
        Route::get( 'get-froyos', [ MenuController::class, 'getFroyos' ] );
        Route::get( 'get-syrups', [ MenuController::class, 'getSyrups' ] );
        Route::get( 'get-toppings', [ MenuController::class, 'getToppings' ] );
    } );
    
    Route::prefix( 'carts' )->group( function() {
        Route::post( 'add', [ CartController::class, 'addToCart' ] );
        Route::post( 'update', [ CartController::class, 'updateCart' ] );
        Route::get( '/', [ CartController::class, 'getCart' ] );
        Route::post( 'delete', [ CartController::class, 'deleteCart' ] );
        Route::post( 'delete-cart-item', [ CartController::class, 'deleteCartItem' ] );
    } );
    
    Route::prefix( 'orders' )->group( function() {
        Route::get( '/', [ OrderController::class, 'getOrder' ] );
        Route::post( 'checkout', [ OrderController::class, 'checkout' ] );
        Route::post( 'retry-payment', [ OrderController::class, 'retryPayment' ] );
    } );

    Route::prefix( 'banners' )->group( function() {
        Route::get( '/', [ BannerController::class, 'getBanners' ] );
        Route::any( 'details', [ BannerController::class, 'oneBanner' ] );
    } );

    Route::prefix( 'announcements' )->group( function() {
        Route::get( '/', [ AnnouncementController::class, 'getAnnouncements' ] );
        Route::post( 'close', [ AnnouncementController::class, 'claim' ] );
    } );

    Route::prefix( 'vouchers' )->group( function() {
        Route::get( '/', [ VoucherController::class, 'getVouchers' ] );
        Route::post( 'claim-voucher', [ VoucherController::class, 'claimVoucher' ] );
        Route::post( '/validate', [ VoucherController::class, 'validateVoucher' ] );
    } );

    Route::prefix( 'promo-codes' )->group( function() {
        Route::get( '/', [ VoucherController::class, 'getPromoCode' ] );
    } );

    Route::prefix( 'checkin' )->group( function() {
        Route::get( '/', [ CheckinController::class, 'getCheckinHistory' ] );
        Route::post( '', [ CheckinController::class, 'checkin' ] );
        Route::get( 'rewards', [ CheckinController::class, 'getCheckinRewards' ] );
    } );

    Route::prefix( 'bundles' )->group( function() {
        Route::get( '/', [ ProductBundleController::class, 'getBundles' ] );
        Route::post( 'buy', [ ProductBundleController::class, 'buyBundle' ] );
        Route::post( 'retry-payment', [ ProductBundleController::class, 'retryPayment' ] );

        Route::post( 'get-added-cup', [ ProductBundleController::class, 'getAddedCup' ] );
        Route::post( 'add-cup', [ ProductBundleController::class, 'addCup' ] );
        Route::post( 'edit-cup', [ ProductBundleController::class, 'editCup' ] );
        Route::post( 'checkout', [ ProductBundleController::class, 'checkout' ] );
    } );
    
});

// Start Vending Machine Route
Route::middleware( 'vending.auth' )->group( function() {

    Route::prefix( 'vending-machine-operation' )->group( function() {
        Route::any( 'get-status', [ VendingMachineController::class, 'getVendingMachineStatus' ] );
        Route::post( 'update-status', [ VendingMachineController::class, 'updateVendingMachineStatus' ] );
        Route::post( 'alert-stock', [ VendingMachineController::class, 'alertStock' ] );
        Route::post( 'deduct-stock', [ VendingMachineController::class, 'deductVendingMachineStock' ] );
        Route::post( 'update-stock', [ VendingMachineController::class, 'updateVendingMachineStock' ] );
    } );

    Route::prefix( 'order-operation' )->group( function() {
        Route::post( 'update-order-status', [ OrderController::class, 'updateOrderStatus' ] );
        Route::post( 'update-sales-data', [ OrderController::class, 'updateSalesData' ] );
    } );

    Route::prefix( 'menus-operation' )->group( function() {
        Route::get( 'get-menus', [ MenuController::class, 'getMenus' ] );
        Route::get( 'get-bundles', [ MenuController::class, 'getBundles' ] );
        Route::get( 'get-selections', [ MenuController::class, 'getSelections' ] );
        Route::get( 'get-froyos', [ MenuController::class, 'getFroyos' ] );
        Route::get( 'get-syrups', [ MenuController::class, 'getSyrups' ] );
        Route::get( 'get-toppings', [ MenuController::class, 'getToppings' ] );
    } );

    Route::prefix( 'api-request-operation' )->group( function() {
        Route::post( 'create', [ ApiRequestController::class, 'createApiRequest' ] );
    } );

    Route::prefix( 'banners-operation' )->group( function() {
        Route::any( '/', [ BannerController::class, 'getBanners' ] );
    } );

});


