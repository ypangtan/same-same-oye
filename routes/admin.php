<?php

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Http\Controllers\Admin\{
    AdministratorController,
    RoleController,
    AuditController,
    CoreController,
    CustomerController,
    DashboardController,
    EmployeeController,
    FileController,
    ModuleController,
    SettingController,
    UserController,
    WalletController,
    WalletTransactionController,
    PaymentController,
    VoucherController,
    UserVoucherController,
    VoucherUsageController,
    UserCheckinController,
    CheckinRewardController,
    UserBundleController,
    AnnouncementController,
    AnnouncementRewardController,
    AppVersionController,
    BannerController,
    CategoryController,
    ItemController,
    LuckyDrawController,
    ProductController,
    SalesRecordController,
    MarketingNotificationController,
    OtpLogController,
    PlaylistController,
    PopAnnouncementController,
    RankController,
};

use App\Models\{
    Order,
    OrderTransaction,
    ApiLog,
};

use App\Helpers\Helper;

use Carbon\Carbon;

Route::prefix( config( 'services.url.admin_path' ) )->group( function() {

    // Protected Route
    Route::group( [ 'middleware' => [ 'auth:admin' ] ], function() {

        Route::get( 'setup', [ SettingController::class, 'firstSetup' ] )->name( 'admin.first_setup' );
        Route::post( 'settings/setup-mfa', [ SettingController::class, 'setupMFA' ] )->name( 'admin.setupMFA' );
        Route::get( 'verify', [ AdministratorController::class, 'verify' ] )->name( 'admin.verify' );
        Route::post( 'verify-code', [ AdministratorController::class, 'verifyCode' ] )->name( 'admin.verifyCode' );

        Route::post( 'signout', [ AdministratorController::class, 'logout' ] )->name( 'admin.signout' );

        Route::group( [ 'middleware' => [ 'checkAdminIsMFA', 'checkMFA' ] ], function() {

            Route::prefix( 'core' )->group( function() {
                Route::post( 'get-notification-list', [ CoreController::class, 'getNotificationList' ] )->name( 'admin.core.getNotificationList' );
                Route::post( 'seen-notification', [ CoreController::class, 'seenNotification' ] )->name( 'admin.core.seenNotification' );
            } );

            Route::get( '/', function() {
                return redirect()->route( 'admin.dashboard' );
            } )->name( 'admin.home' );

            Route::post( 'file/upload', [ FileController::class, 'upload' ] )->withoutMiddleware( [\App\Http\Middleware\VerifyCsrfToken::class] )->name( 'admin.file.upload' );
            Route::post( 'file/cke-upload', [ FileController::class, 'ckeUpload' ] )->withoutMiddleware( [\App\Http\Middleware\VerifyCsrfToken::class] )->name( 'admin.file.ckeUpload' );
            Route::post( 'file/song-upload', [ FileController::class, 'songUpload' ] )->withoutMiddleware( [\App\Http\Middleware\VerifyCsrfToken::class] )->name( 'admin.file.songUpload' );

            Route::prefix( 'dashboard' )->group( function() {
                Route::get( '/', [ DashboardController::class, 'index' ] )->name( 'admin.dashboard' );

                Route::post( '/', [ DashboardController::class, 'getDashboardData' ] )->name( 'admin.dashboard.getDashboardData' );

            } );

            Route::prefix( 'administrators' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view administrators' ] ], function() {
                    Route::get( '/', [ AdministratorController::class, 'index' ] )->name( 'admin.module_parent.administrator.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add administrators' ] ], function() {
                    Route::get( 'add', [ AdministratorController::class, 'add' ] )->name( 'admin.administrator.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit administrators' ] ], function() {
                    Route::get( 'edit', [ AdministratorController::class, 'edit' ] )->name( 'admin.administrator.edit' );
                } );

                Route::post( 'all-administrators', [ AdministratorController::class, 'allAdministrators' ] )->name( 'admin.administrator.allAdministrators' );
                Route::post( 'one-administrator', [ AdministratorController::class, 'oneAdministrator' ] )->name( 'admin.administrator.oneAdministrator' );
                Route::post( 'create-administrator', [ AdministratorController::class, 'createAdministrator' ] )->name( 'admin.administrator.createAdministrator' );
                Route::post( 'update-administrator', [ AdministratorController::class, 'updateAdministrator' ] )->name( 'admin.administrator.updateAdministrator' );
            } );

            Route::prefix( 'roles' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view roles' ] ], function() {
                    Route::get( '/', [ RoleController::class, 'index' ] )->name( 'admin.module_parent.role.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add roles' ] ], function() {
                    Route::get( 'add', [ RoleController::class, 'add' ] )->name( 'admin.role.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit roles' ] ], function() {
                    Route::get( 'edit', [ RoleController::class, 'edit' ] )->name( 'admin.role.edit' );
                } );

                Route::post( 'all-roles', [ RoleController::class, 'allRoles' ] )->name( 'admin.role.allRoles' );
                Route::post( 'one-role', [ RoleController::class, 'oneRole' ] )->name( 'admin.role.oneRole' );
                Route::post( 'create-role', [ RoleController::class, 'createRole' ] )->name( 'admin.role.createRole' );
                Route::post( 'update-role', [ RoleController::class, 'updateRole' ] )->name( 'admin.role.updateRole' );
            } );

            Route::prefix( 'modules' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view modules' ] ], function() {
                    Route::get( '/', [ ModuleController::class, 'index' ] )->name( 'admin.module_parent.module.index' );
                } );

                Route::post( 'all-modules', [ ModuleController::class, 'allModules' ] )->name( 'admin.module.allModules' );
            } );

            Route::prefix( 'audits' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view audits' ] ], function() {
                    Route::get( '/', [ AuditController::class, 'index' ] )->name( 'admin.module_parent.audit.index' );
                } );

                Route::post( 'all-audits', [ AuditController::class, 'allAudits' ] )->name( 'admin.audit.allAudits' );
                Route::post( 'one-audit', [ AuditController::class, 'oneAudit' ] )->name( 'admin.audit.oneAudit' );
            } );

            Route::prefix( 'users' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view users' ] ], function() {
                    Route::get( '/', [ UserController::class, 'index' ] )->name( 'admin.module_parent.user.index' );
                    Route::get( '/my-friend', [ UserController::class, 'myFriend' ] )->name( 'admin.user.my_friend' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add users' ] ], function() {
                    Route::get( 'add', [ UserController::class, 'add' ] )->name( 'admin.user.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit users' ] ], function() {
                    Route::get( 'edit', [ UserController::class, 'edit' ] )->name( 'admin.user.edit' );
                } );

                Route::post( 'all-users', [ UserController::class, 'allUsers' ] )->name( 'admin.user.allUsers' );
                Route::post( 'all-user-downlines', [ UserController::class, 'oneUserDownlines' ] )->name( 'admin.user.oneUserDownlines' );
                Route::post( 'one-user', [ UserController::class, 'oneUser' ] )->name( 'admin.user.oneUser' );
                Route::post( 'create-user', [ UserController::class, 'createUser' ] )->name( 'admin.user.createUser' );
                Route::post( 'update-user', [ UserController::class, 'updateUser' ] )->name( 'admin.user.updateUser' );
                Route::post( 'update-user-status', [ UserController::class, 'updateUserStatus' ] )->name( 'admin.user.updateUserStatus' );
            } );

            Route::prefix( 'wallets' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view wallets' ] ], function() {
                    Route::get( '/', [ WalletController::class, 'index' ] )->name( 'admin.module_parent.wallet.index' );
                } );

                Route::post( 'all-wallets', [ WalletController::class, 'allWallets' ] )->name( 'admin.wallet.allWallets' );
                Route::post( 'one-wallet', [ WalletController::class, 'oneWallet' ] )->name( 'admin.wallet.oneWallet' );
                Route::post( 'update-wallet', [ WalletController::class, 'updateWallet' ] )->name( 'admin.wallet.updateWallet' );
                Route::post( 'update-wallet-multiple', [ WalletController::class, 'updateWalletMultiple' ] )->name( 'admin.wallet.updateWalletMultiple' );
            } );
            
            Route::prefix( 'wallet-transactions' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view wallet_transactions' ] ], function() {
                    Route::get( '/', [ WalletTransactionController::class, 'index' ] )->name( 'admin.module_parent.wallet_transaction.index' );
                } );

                Route::post( 'all-wallet-transactions', [ WalletTransactionController::class, 'allWalletTransactions' ] )->name( 'admin.wallet_transaction.allWalletTransactions' );
            } );
            
            // new routes ( 23/12 ) 
            Route::prefix( 'settings' )->group( function() {

                Route::group( [ 'middleware' => [ 'permission:add settings|view settings|edit settings|delete settings' ] ], function() {
                    Route::get( '/', [ SettingController::class, 'index' ] )->name( 'admin.module_parent.setting.index' );
                } );

                Route::post( 'settings', [ SettingController::class, 'settings' ] )->name( 'admin.setting.settings' );
                Route::post( 'app-version-settings', [ SettingController::class, 'lastestAppVersion' ] )->name( 'admin.setting.lastestAppVersion' );
                Route::post( 'maintenance-settings', [ SettingController::class, 'maintenanceSettings' ] )->name( 'admin.setting.maintenanceSettings' );
                Route::post( 'update-maintenance-setting', [ SettingController::class, 'updateMaintenanceSetting' ] )->name( 'admin.setting.updateMaintenanceSetting' );
                Route::post( 'update-app-version-setting', [ SettingController::class, 'updateAppVersionSetting' ] )->name( 'admin.setting.updateAppVersionSetting' );
            } );
            
            Route::prefix( 'pop-announcements' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view pop_announcements' ] ], function() {
                    Route::get( '/', [ PopAnnouncementController::class, 'index' ] )->name( 'admin.module_parent.pop_announcement.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add pop_announcements' ] ], function() {
                    Route::get( 'add', [ PopAnnouncementController::class, 'add' ] )->name( 'admin.pop_announcement.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit pop_announcements' ] ], function() {
                    Route::get( 'edit', [ PopAnnouncementController::class, 'edit' ] )->name( 'admin.pop_announcement.edit' );
                } );
    
                Route::post( 'all-pop-announcements', [ PopAnnouncementController::class, 'allPopAnnouncements' ] )->name( 'admin.pop_announcement.allPopAnnouncements' );
                Route::post( 'one-pop-announcement', [ PopAnnouncementController::class, 'onePopAnnouncement' ] )->name( 'admin.pop_announcement.onePopAnnouncement' );
                Route::post( 'create-pop-announcement', [ PopAnnouncementController::class, 'createPopAnnouncement' ] )->name( 'admin.pop_announcement.createPopAnnouncement' );
                Route::post( 'update-pop-announcement', [ PopAnnouncementController::class, 'updatePopAnnouncement' ] )->name( 'admin.pop_announcement.updatePopAnnouncement' );
                Route::post( 'update-pop-announcement-status', [ PopAnnouncementController::class, 'updatePopAnnouncementStatus' ] )->name( 'admin.pop_announcement.updatePopAnnouncementStatus' );
                Route::post( 'ckeUpload', [ PopAnnouncementController::class, 'ckeUpload' ] )->name( 'admin.pop_announcement.ckeUpload' );
                Route::post( 'image-upload', [ PopAnnouncementController::class, 'imageUpload' ] )->name( 'admin.pop_announcement.imageUpload' )->withoutMiddleware( [\App\Http\Middleware\VerifyCsrfToken::class] );
            } );

            Route::prefix( 'marketing-notifications' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view marketing_notifications' ] ], function() {
                    Route::get( '/', [ MarketingNotificationController::class, 'index' ] )->name( 'admin.module_parent.marketing_notifications.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add marketing_notifications' ] ], function() {
                    Route::get( 'add', [ MarketingNotificationController::class, 'add' ] )->name( 'admin.marketing_notifications.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit marketing_notifications' ] ], function() {
                    Route::get( 'edit/{id?}', [ MarketingNotificationController::class, 'edit' ] )->name( 'admin.marketing_notifications.edit' );
                } );

                Route::post( 'all-marketing-notifications', [ MarketingNotificationController::class, 'allMarketingNotifications' ] )->name( 'admin.marketing_notifications.allMarketingNotifications' );
                Route::post( 'one-marketing-notifications', [ MarketingNotificationController::class, 'oneMarketingNotification' ] )->name( 'admin.marketing_notifications.oneMarketingNotification' );
                Route::post( 'create-marketing-notifications', [ MarketingNotificationController::class, 'createMarketingNotification' ] )->name( 'admin.marketing_notifications.createMarketingNotification' );
                Route::post( 'update-marketing-notifications', [ MarketingNotificationController::class, 'updateMarketingNotification' ] )->name( 'admin.marketing_notifications.updateMarketingNotification' );
                Route::post( 'update-marketing-notifications-status', [ MarketingNotificationController::class, 'updateMarketingNotificationStatus' ] )->name( 'admin.marketing_notifications.updateMarketingNotificationStatus' );

                Route::post( 'cke-upload', [ MarketingNotificationController::class, 'ckeUpload' ] )->name( 'admin.marketing_notifications.ckeUpload' );
            } );

            Route::prefix( 'otp_logs' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view otp_logs' ] ], function() {
                    Route::get( '/', [ OtpLogController::class, 'index' ] )->name( 'admin.module_parent.otp_log.index' );
                } );

                Route::post( 'all-otp-logs', [ OtpLogController::class, 'allOtpLogs' ] )->name( 'admin.otp_log.allOtpLogs' );
                Route::post( 'one-otp-log', [ OtpLogController::class, 'oneOtpLog' ] )->name( 'admin.otp_log.oneOtpLog' );
            } );

            Route::prefix( 'items' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view items' ] ], function() {
                    Route::get( '/', [ ItemController::class, 'index' ] )->name( 'admin.module_parent.item.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add items' ] ], function() {
                    Route::get( 'add', [ ItemController::class, 'add' ] )->name( 'admin.item.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit items' ] ], function() {
                    Route::get( 'edit/{id?}', [ ItemController::class, 'edit' ] )->name( 'admin.item.edit' );
                } );

                Route::post( 'all-items', [ ItemController::class, 'allItems' ] )->name( 'admin.item.allItems' );
                Route::post( 'one-item', [ ItemController::class, 'oneItem' ] )->name( 'admin.item.oneItem' );
                Route::post( 'create-item', [ ItemController::class, 'createItem' ] )->name( 'admin.item.createItem' );
                Route::post( 'update-item', [ ItemController::class, 'updateItem' ] )->name( 'admin.item.updateItem' );
                Route::post( 'update-item-status', [ ItemController::class, 'updateItemStatus' ] )->name( 'admin.item.updateItemStatus' );
                Route::post( 'cke-upload', [ ItemController::class, 'ckeUpload' ] )->name( 'admin.playlist.ckeUpload' );
                Route::post( 'image-upload', [ ItemController::class, 'imageUpload' ] )->name( 'admin.playlist.imageUpload' );
                Route::post( 'song-upload', [ ItemController::class, 'songUpload' ] )->name( 'admin.item.songUpload' );
            } );

            Route::prefix( 'playlists' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view playlists' ] ], function() {
                    Route::get( '/', [ PlaylistController::class, 'index' ] )->name( 'admin.module_parent.playlist.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add playlists' ] ], function() {
                    Route::get( 'add', [ PlaylistController::class, 'add' ] )->name( 'admin.playlist.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit playlists' ] ], function() {
                    Route::get( 'edit/{id?}', [ PlaylistController::class, 'edit' ] )->name( 'admin.playlist.edit' );
                } );

                Route::post( 'all-playlists', [ PlaylistController::class, 'allPlaylists' ] )->name( 'admin.playlist.allPlaylists' );
                Route::post( 'one-playlist', [ PlaylistController::class, 'onePlaylist' ] )->name( 'admin.playlist.onePlaylist' );
                Route::post( 'create-playlist', [ PlaylistController::class, 'createPlaylist' ] )->name( 'admin.playlist.createPlaylist' );
                Route::post( 'update-playlist', [ PlaylistController::class, 'updatePlaylist' ] )->name( 'admin.playlist.updatePlaylist' );
                Route::post( 'update-playlist-status', [ PlaylistController::class, 'updatePlaylistStatus' ] )->name( 'admin.playlist.updatePlaylistStatus' );
                Route::post( 'ckeUpload', [ PlaylistController::class, 'ckeUpload' ] )->name( 'admin.playlist.ckeUpload' );
            } );

            Route::prefix( 'collections' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view collections' ] ], function() {
                    Route::get( '/', [ PlaylistController::class, 'index' ] )->name( 'admin.module_parent.collection.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add collections' ] ], function() {
                    Route::get( 'add', [ PlaylistController::class, 'add' ] )->name( 'admin.collection.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit collections' ] ], function() {
                    Route::get( 'edit/{id?}', [ PlaylistController::class, 'edit' ] )->name( 'admin.collection.edit' );
                } );

                Route::post( 'all-collections', [ PlaylistController::class, 'allCollections' ] )->name( 'admin.collection.allCollections' );
                Route::post( 'one-collection', [ PlaylistController::class, 'oneCollection' ] )->name( 'admin.collection.oneCollection' );
                Route::post( 'create-collection', [ PlaylistController::class, 'createCollection' ] )->name( 'admin.collection.createCollection' );
                Route::post( 'update-collection', [ PlaylistController::class, 'updateCollection' ] )->name( 'admin.collection.updateCollection' );
                Route::post( 'update-collection-status', [ PlaylistController::class, 'updateCollectionStatus' ] )->name( 'admin.collection.updateCollectionStatus' );
                Route::post( 'ckeUpload', [ PlaylistController::class, 'ckeUpload' ] )->name( 'admin.collection.ckeUpload' );
            } );

            Route::prefix( 'categories' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view categories' ] ], function() {
                Route::post( 'all-categories', [ CategoryController::class, 'allCategories' ] )->name( 'admin.category.allCagetories' );
            } );

        } );
        
    } );

    // Public Route
    Route::get( 'lang/{lang}', function( $lang ) {

        if ( array_key_exists( $lang, Config::get( 'languages' ) ) ) {
            Session::put( 'appLocale', $lang );
        }
        return Redirect::back();
    } )->name( 'admin.switchLanguage' );

    Route::get( 'login', [ AdministratorController::class, 'login' ] )->middleware( 'guest:admin' )->name( 'admin.signin' );

    $limiter = config( 'fortify.limiters.login' );

    Route::post( 'login', [ AuthenticatedSessionController::class, 'store' ] )->middleware( array_filter( [ 'guest:admin', $limiter ? 'throttle:'.$limiter : null ] ) )->name( 'admin.login' );

    Route::post( 'logout', [ AuthenticatedSessionController::class, 'destroy' ] )->middleware( 'auth:admin' )->name( 'admin.logout' );
} );