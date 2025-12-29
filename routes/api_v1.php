<?php

use App\Http\Controllers\Api\{
    AdController,
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
    AppVersionController,
    CategoryController,
    CollectionController,
    ItemController,
    LuckyDrawRewardController,
    MusicRequestController,
    PlaylistController,
    PointsController,
    RankController,
    TypeController,
    UserPlaylistController,
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
    Route::post( 'forgot-password', [ UserController::class, 'forgotPasswordOtp' ] );
    Route::post( 'resend-forgot-password', [ UserController::class, 'resendForgotPasswordOtp' ] );
    Route::post( 'verify-otp', [ UserController::class, 'verifyOtp' ] );
    Route::post( 'reset-password', [ UserController::class, 'resetPassword' ] );
} );

Route::prefix( 'app_versions' )->group( function() {
    Route::get( '/', [ AppVersionController::class, 'lastestAppVersion' ] );
} );

Route::prefix( 'categories' )->group( function() {
    Route::post( '/', [ CategoryController::class, 'getCategories' ] );
} );

Route::prefix( 'types' )->group( function() {
    Route::post( '/', [ TypeController::class, 'getTypes' ] );
} );

Route::prefix( 'pop_announcements' )->group( function() {
    Route::post( '/', [ AnnouncementController::class, 'getAllPopAnnouncements' ] );
} );

Route::prefix( 'collections' )->group( function() {
    Route::post( '/get-all-collections', [ CollectionController::class, 'getCollections' ] );
    Route::post( '/get-one-collection', [ CollectionController::class, 'getCollection' ] );
} );

Route::prefix( 'playlists' )->group( function() {
    Route::post( '/get-all-playlists', [ PlaylistController::class, 'getPlaylists' ] );
    Route::post( '/get-one-playlist', [ PlaylistController::class, 'getPlaylist' ] );
} );

Route::prefix( 'items' )->group( function() {
    Route::post( '/get-all-items', [ ItemController::class, 'getItems' ] );
    Route::post( '/get-one-item', [ ItemController::class, 'getItem' ] );
} );

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

    Route::prefix( 'banners' )->group( function() {
        Route::post( '/get-all-banners', [ BannerController::class, 'getBanners' ] );
        Route::post( '/get-one-banner', [ BannerController::class, 'getBanner' ] );
    } );

    Route::prefix( 'user-playlist' )->group( function() {
        Route::post( '/get-user-playlists', [ UserPlaylistController::class, 'getUserPlaylists' ] );
        Route::post( '/get-user-playlist', [ UserPlaylistController::class, 'getUserPlaylist' ] );
        Route::post( '/create-user-playList', [ UserPlaylistController::class, 'createUserPlayList' ] );
        Route::post( '/update-user-playList', [ UserPlaylistController::class, 'updateUserPlayList' ] );
        Route::post( '/delete-user-playList', [ UserPlaylistController::class, 'deleteUserPlayList' ] );
        Route::post( '/add-song-to-user-playList', [ UserPlaylistController::class, 'addSongToUserPlayList' ] );
        Route::post( '/remove-song-to-user-playList', [ UserPlaylistController::class, 'removeSongToUserPlayList' ] );
        Route::post( '/add-playlist-to-user-playList', [ UserPlaylistController::class, 'addPlaylistToUserPlayList' ] );
    } );

    Route::prefix( 'ads' )->group( function() {
        Route::post( '/get-all-ads', [ AdController::class, 'getAds' ] );
        Route::post( '/get-one-ad', [ AdController::class, 'getAd' ] );
    } );

    Route::prefix( 'music-requests' )->group( function() {
        Route::post( '/create-music-requests', [ MusicRequestController::class, 'createMusicRequest' ] );
    } );
    
});


