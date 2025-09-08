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
    BannerController,
    LuckyDrawController,
    ProductController,
    SalesRecordController,
    MarketingNotificationController,
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

            Route::prefix( 'dashboard' )->group( function() {
                Route::get( '/', [ DashboardController::class, 'index' ] )->name( 'admin.dashboard' );

                Route::post( '/', [ DashboardController::class, 'getDashboardData' ] )->name( 'admin.dashboard.getDashboardData' );

                Route::post( 'total-revenue-statistics', [ DashboardController::class, 'totalRevenueStatistics' ] )->name( 'admin.dashboard.totalRevenueStatistics' );
                Route::post( 'total-reload-statistics', [ DashboardController::class, 'totalReloadStatistics' ] )->name( 'admin.dashboard.totalReloadStatistics' );
                Route::post( 'total-cups-statistics', [ DashboardController::class, 'totalCupsStatistics' ] )->name( 'admin.dashboard.totalCupsStatistics' );
                Route::post( 'total-user-statistics', [ DashboardController::class, 'totalUserStatistics' ] )->name( 'admin.dashboard.totalUserStatistics' );
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
                
                Route::group( [ 'middleware' => [ 'permission:view administrators' ] ], function() {
                    Route::get( 'salesmen', [ AdministratorController::class, 'indexSalesman' ] )->name( 'admin.administrator.indexSalesman' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add administrators' ] ], function() {
                    Route::get( 'salesmen/add', [ AdministratorController::class, 'addSalesman' ] )->name( 'admin.administrator.addSalesman' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit administrators' ] ], function() {
                    Route::get( 'salesmen/edit', [ AdministratorController::class, 'editSalesman' ] )->name( 'admin.administrator.editSalesman' );
                } );

                Route::post( 'all-salesmen', [ AdministratorController::class, 'allSalesmen' ] )->name( 'admin.administrator.allSalesmen' );
                Route::post( 'one-salesman', [ AdministratorController::class, 'oneSalesman' ] )->name( 'admin.administrator.oneSalesman' );
                Route::post( 'create-salesman', [ AdministratorController::class, 'createSalesman' ] )->name( 'admin.administrator.createSalesman' );
                Route::post( 'update-salesman', [ AdministratorController::class, 'updateSalesman' ] )->name( 'admin.administrator.updateSalesman' );
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
            Route::prefix( 'vouchers' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vouchers' ] ], function() {
                    Route::get( '/', [ VoucherController::class, 'index' ] )->name( 'admin.module_parent.voucher.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vouchers' ] ], function() {
                    Route::get( 'add', [ VoucherController::class, 'add' ] )->name( 'admin.voucher.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vouchers' ] ], function() {
                    Route::get( 'edit', [ VoucherController::class, 'edit' ] )->name( 'admin.voucher.edit' );
                } );
    
                Route::post( 'all-vouchers', [ VoucherController::class, 'allVouchers' ] )->name( 'admin.voucher.allVouchers' );
                Route::post( 'one-voucher', [ VoucherController::class, 'oneVoucher' ] )->name( 'admin.voucher.oneVoucher' );
                Route::post( 'create-voucher', [ VoucherController::class, 'createVoucher' ] )->name( 'admin.voucher.createVoucher' );
                Route::post( 'update-voucher', [ VoucherController::class, 'updateVoucher' ] )->name( 'admin.voucher.updateVoucher' );
                Route::post( 'update-voucher-status', [ VoucherController::class, 'updateVoucherStatus' ] )->name( 'admin.voucher.updateVoucherStatus' );
                Route::post( 'remove-voucher-gallery-image', [ VoucherController::class, 'removeVoucherGalleryImage' ] )->name( 'admin.voucher.removeVoucherGalleryImage' );
                Route::post( 'ckeUpload', [ VoucherController::class, 'ckeUpload' ] )->name( 'admin.voucher.ckeUpload' );
            } );

            Route::prefix( 'user-checkins' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view user_checkins' ] ], function() {
                    Route::get( '/', [ UserCheckinController::class, 'index' ] )->name( 'admin.module_parent.user_checkin.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add user_checkins' ] ], function() {
                    Route::get( 'add', [ UserCheckinController::class, 'add' ] )->name( 'admin.user_checkin.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit user_checkins' ] ], function() {
                    Route::get( 'edit', [ UserCheckinController::class, 'edit' ] )->name( 'admin.user_checkin.edit' );
                } );
                Route::group( [ 'middleware' => [ 'permission:view checkin_rewards' ] ], function() {
                    Route::get( 'calendar', [ UserCheckinController::class, 'calendar' ] )->name( 'admin.user_checkin.calendar' );
                } );
    
                Route::post( 'all-user-checkins', [ UserCheckinController::class, 'allUserCheckins' ] )->name( 'admin.user_checkin.allUserCheckins' );
                Route::post( 'all-user-checkin-calendars', [ UserCheckinController::class, 'allUserCheckinCalendars' ] )->name( 'admin.user_checkin.allUserCheckinCalendars' );
                Route::post( 'one-user-checkin', [ UserCheckinController::class, 'oneUserCheckin' ] )->name( 'admin.user_checkin.oneUserCheckin' );
                Route::post( 'create-user-checkin', [ UserCheckinController::class, 'createUserCheckin' ] )->name( 'admin.user_checkin.createUserCheckin' );
                Route::post( 'update-user-checkin', [ UserCheckinController::class, 'updateUserCheckin' ] )->name( 'admin.user_checkin.updateUserCheckin' );
                Route::post( 'update-user-checkin-status', [ UserCheckinController::class, 'updateUserCheckinStatus' ] )->name( 'admin.user_checkin.updateUserCheckinStatus' );
                Route::post( 'remove-user-checkin-gallery-image', [ UserCheckinController::class, 'removeUserCheckinGalleryImage' ] )->name( 'admin.user_checkin.removeUserCheckinGalleryImage' );
                Route::post( 'ckeUpload', [ UserCheckinController::class, 'ckeUpload' ] )->name( 'admin.user_checkin.ckeUpload' );
            } );

            Route::prefix( 'checkin-rewards' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view checkin_rewards' ] ], function() {
                    Route::get( '/', [ CheckinRewardController::class, 'index' ] )->name( 'admin.module_parent.checkin_reward.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add checkin_rewards' ] ], function() {
                    Route::get( 'add', [ CheckinRewardController::class, 'add' ] )->name( 'admin.checkin_reward.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit checkin_rewards' ] ], function() {
                    Route::get( 'edit', [ CheckinRewardController::class, 'edit' ] )->name( 'admin.checkin_reward.edit' );
                } );
    
                Route::post( 'all-checkin-rewards', [ CheckinRewardController::class, 'allCheckinRewards' ] )->name( 'admin.checkin_reward.allCheckinRewards' );
                Route::post( 'one-checkin-reward', [ CheckinRewardController::class, 'oneCheckinReward' ] )->name( 'admin.checkin_reward.oneCheckinReward' );
                Route::post( 'create-checkin-reward', [ CheckinRewardController::class, 'createCheckinReward' ] )->name( 'admin.checkin_reward.createCheckinReward' );
                Route::post( 'update-checkin-reward', [ CheckinRewardController::class, 'updateCheckinReward' ] )->name( 'admin.checkin_reward.updateCheckinReward' );
                Route::post( 'update-checkin-reward-status', [ CheckinRewardController::class, 'updateCheckinRewardStatus' ] )->name( 'admin.checkin_reward.updateCheckinRewardStatus' );
                Route::post( 'remove-checkin-reward-gallery-image', [ CheckinRewardController::class, 'removeCheckinRewardGalleryImage' ] )->name( 'admin.checkin_reward.removeCheckinRewardGalleryImage' );
                Route::post( 'ckeUpload', [ CheckinRewardController::class, 'ckeUpload' ] )->name( 'admin.checkin_reward.ckeUpload' );
            } );

            Route::prefix( 'settings' )->group( function() {

                Route::group( [ 'middleware' => [ 'permission:add settings|view settings|edit settings|delete settings' ] ], function() {
                    Route::get( '/', [ SettingController::class, 'index' ] )->name( 'admin.module_parent.setting.index' );
                } );

                Route::post( 'settings', [ SettingController::class, 'settings' ] )->name( 'admin.setting.settings' );
                Route::post( 'gift-settings', [ SettingController::class, 'giftSettings' ] )->name( 'admin.setting.giftSettings' );
                Route::post( 'bonus-settings', [ SettingController::class, 'bonusSettings' ] )->name( 'admin.setting.bonusSettings' );
                Route::post( 'maintenance-settings', [ SettingController::class, 'maintenanceSettings' ] )->name( 'admin.setting.maintenanceSettings' );
                Route::post( 'update-bonus-setting', [ SettingController::class, 'updateBonusSetting' ] )->name( 'admin.setting.updateBonusSetting' );
                Route::post( 'update-maintenance-setting', [ SettingController::class, 'updateMaintenanceSetting' ] )->name( 'admin.setting.updateMaintenanceSetting' );
                Route::post( 'update-birthday-gift-setting', [ SettingController::class, 'updateBirthdayGiftSetting' ] )->name( 'admin.setting.updateBirthdayGiftSetting' );
                Route::post( 'update-referral-gift-setting', [ SettingController::class, 'updateReferralGiftSetting' ] )->name( 'admin.setting.updateReferralGiftSetting' );
            } );

            Route::prefix( 'user-vouchers' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vouchers' ] ], function() {
                    Route::get( '/', [ UserVoucherController::class, 'index' ] )->name( 'admin.module_parent.user_voucher.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vouchers' ] ], function() {
                    Route::get( 'add', [ UserVoucherController::class, 'add' ] )->name( 'admin.user_voucher.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vouchers' ] ], function() {
                    Route::get( 'edit', [ UserVoucherController::class, 'edit' ] )->name( 'admin.user_voucher.edit' );
                } );
    
                Route::post( 'all-user-vouchers', [ UserVoucherController::class, 'allUserVouchers' ] )->name( 'admin.user_voucher.allUserVouchers' );
                Route::post( 'one-user-voucher', [ UserVoucherController::class, 'oneUserVoucher' ] )->name( 'admin.user_voucher.oneUserVoucher' );
                Route::post( 'create-user-voucher', [ UserVoucherController::class, 'createUserVoucher' ] )->name( 'admin.user_voucher.createUserVoucher' );
                Route::post( 'update-user-voucher', [ UserVoucherController::class, 'updateUserVoucher' ] )->name( 'admin.user_voucher.updateUserVoucher' );
                Route::post( 'update-user-user-voucher-status', [ UserVoucherController::class, 'updateUserVoucherStatus' ] )->name( 'admin.user_voucher.updateUserVoucherStatus' );
                Route::post( 'remove-user-user-voucher-gallery-image', [ UserVoucherController::class, 'removeUserVoucherGalleryImage' ] )->name( 'admin.user_voucher.removeUserVoucherGalleryImage' );
                Route::post( 'ckeUpload', [ UserVoucherController::class, 'ckeUpload' ] )->name( 'admin.user_voucher.ckeUpload' );
            } );
            
            Route::prefix( 'announcements' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view announcements' ] ], function() {
                    Route::get( '/', [ AnnouncementController::class, 'index' ] )->name( 'admin.module_parent.announcement.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add announcements' ] ], function() {
                    Route::get( 'add', [ AnnouncementController::class, 'add' ] )->name( 'admin.announcement.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit announcements' ] ], function() {
                    Route::get( 'edit', [ AnnouncementController::class, 'edit' ] )->name( 'admin.announcement.edit' );
                } );
    
                Route::post( 'all-announcements', [ AnnouncementController::class, 'allAnnouncements' ] )->name( 'admin.announcement.allAnnouncements' );
                Route::post( 'one-announcement', [ AnnouncementController::class, 'oneAnnouncement' ] )->name( 'admin.announcement.oneAnnouncement' );
                Route::post( 'create-announcement', [ AnnouncementController::class, 'createAnnouncement' ] )->name( 'admin.announcement.createAnnouncement' );
                Route::post( 'update-announcement', [ AnnouncementController::class, 'updateAnnouncement' ] )->name( 'admin.announcement.updateAnnouncement' );
                Route::post( 'update-announcement-status', [ AnnouncementController::class, 'updateAnnouncementStatus' ] )->name( 'admin.announcement.updateAnnouncementStatus' );
                Route::post( 'remove-announcement-gallery-image', [ AnnouncementController::class, 'removeAnnouncementGalleryImage' ] )->name( 'admin.announcement.removeAnnouncementGalleryImage' );
                Route::post( 'ckeUpload', [ AnnouncementController::class, 'ckeUpload' ] )->name( 'admin.announcement.ckeUpload' );
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

            Route::prefix( 'banners' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view banners' ] ], function() {
                    Route::get( '/', [ BannerController::class, 'index' ] )->name( 'admin.module_parent.banner.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add banners' ] ], function() {
                    Route::get( 'add', [ BannerController::class, 'add' ] )->name( 'admin.banner.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit banners' ] ], function() {
                    Route::get( 'edit', [ BannerController::class, 'edit' ] )->name( 'admin.banner.edit' );
                } );
    
                Route::post( 'update-order', [ BannerController::class, 'updateOrder' ] )->name( 'admin.banner.updateOrder' );
                Route::post( 'all-banners', [ BannerController::class, 'allBanners' ] )->name( 'admin.banner.allBanners' );
                Route::post( 'one-banner', [ BannerController::class, 'oneBanner' ] )->name( 'admin.banner.oneBanner' );
                Route::post( 'create-banner', [ BannerController::class, 'createBanner' ] )->name( 'admin.banner.createBanner' );
                Route::post( 'update-banner', [ BannerController::class, 'updateBanner' ] )->name( 'admin.banner.updateBanner' );
                Route::post( 'delete-banner', [ BannerController::class, 'deleteBanner' ] )->name( 'admin.banner.deleteBanner' );
                Route::post( 'update-banner-status', [ BannerController::class, 'updateBannerStatus' ] )->name( 'admin.banner.updateBannerStatus' );
                Route::post( 'remove-banner-gallery-image', [ BannerController::class, 'removeBannerGalleryImage' ] )->name( 'admin.banner.removeBannerGalleryImage' );
                Route::post( 'ckeUpload', [ BannerController::class, 'ckeUpload' ] )->name( 'admin.banner.ckeUpload' );
            } );

            Route::prefix( 'announcement-rewards' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view announcement-rewards' ] ], function() {
                    Route::get( '/', [ AnnouncementRewardController::class, 'index' ] )->name( 'admin.module_parent.announcement_reward.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add announcement-rewards' ] ], function() {
                    Route::get( 'add', [ AnnouncementRewardController::class, 'add' ] )->name( 'admin.announcement_reward.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit announcement-rewards' ] ], function() {
                    Route::get( 'edit', [ AnnouncementRewardController::class, 'edit' ] )->name( 'admin.announcement_reward.edit' );
                } );
    
                Route::post( 'all-announcement-rewards', [ AnnouncementRewardController::class, 'allAnnouncementRewards' ] )->name( 'admin.announcement_reward.allAnnouncementRewards' );
                Route::post( 'one-announcement-reward', [ AnnouncementRewardController::class, 'oneAnnouncementReward.' ] )->name( 'admin.announcement_reward.oneAnnouncementReward.' );
                Route::post( 'create-announcement-reward', [ AnnouncementRewardController::class, 'createAnnouncementReward.' ] )->name( 'admin.announcement_reward.createAnnouncementReward.' );
                Route::post( 'update-announcement-reward', [ AnnouncementRewardController::class, 'updateAnnouncementReward.' ] )->name( 'admin.announcement_reward.updateAnnouncementReward.' );
                Route::post( 'update-announcement-reward-status', [ AnnouncementRewardController::class, 'updateAnnouncementRewardStatus' ] )->name( 'admin.announcement_reward.updateAnnouncementRewardStatus' );
                Route::post( 'remove-announcement-reward-gallery-image', [ AnnouncementRewardController::class, 'removeAnnouncementRewardGalleryImage' ] )->name( 'admin.announcement_reward.removeAnnouncementRewardGalleryImage' );
                Route::post( 'ckeUpload', [ AnnouncementRewardController::class, 'ckeUpload' ] )->name( 'admin.announcement_reward.ckeUpload' );
            } );

            Route::prefix( 'sales-records' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view sales_records' ] ], function() {
                    Route::get( '/', [ SalesRecordController::class, 'index' ] )->name( 'admin.module_parent.sales_record.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add sales_records' ] ], function() {
                    Route::get( 'add', [ SalesRecordController::class, 'add' ] )->name( 'admin.sales_record.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit sales_records' ] ], function() {
                    Route::get( 'edit', [ SalesRecordController::class, 'edit' ] )->name( 'admin.sales_record.edit' );
                } );
                Route::group( [ 'middleware' => [ 'permission:import sales_records' ] ], function() {
                    Route::get( 'import', [ SalesRecordController::class, 'import' ] )->name( 'admin.sales_record.import' );
                } );
    
                Route::post( 'import-sales-records', [ SalesRecordController::class, 'importSalesRecords' ] )->name( 'admin.sales_record.importSalesRecords' );
                Route::post( 'all-sales-records', [ SalesRecordController::class, 'allSalesRecords' ] )->name( 'admin.sales_record.allSalesRecords' );
                Route::post( 'one-sales-record', [ SalesRecordController::class, 'oneSalesRecord' ] )->name( 'admin.sales_record.oneSalesRecord' );
                Route::post( 'create-sales-record', [ SalesRecordController::class, 'createSalesRecord' ] )->name( 'admin.sales_record.createSalesRecord' );
                Route::post( 'update-sales-record', [ SalesRecordController::class, 'updateSalesRecord' ] )->name( 'admin.sales_record.updateSalesRecord' );
                Route::post( 'delete-sales-record', [ SalesRecordController::class, 'deleteSalesRecord' ] )->name( 'admin.sales_record.deleteSalesRecord' );
                Route::post( 'update-sales-record-status', [ SalesRecordController::class, 'updateSalesRecordStatus' ] )->name( 'admin.sales_record.updateSalesRecordStatus' );
                Route::post( 'remove-sales-record-gallery-image', [ SalesRecordController::class, 'removeSalesRecordGalleryImage' ] )->name( 'admin.sales_record.removeSalesRecordGalleryImage' );
                Route::post( 'ckeUpload', [ SalesRecordController::class, 'ckeUpload' ] )->name( 'admin.sales_record.ckeUpload' );
            } );

            Route::prefix( 'products' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view products' ] ], function() {
                    Route::get( '/', [ ProductController::class, 'index' ] )->name( 'admin.product.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add products' ] ], function() {
                    Route::get( 'add', [ ProductController::class, 'add' ] )->name( 'admin.product.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit products' ] ], function() {
                    Route::get( 'edit', [ ProductController::class, 'edit' ] )->name( 'admin.product.edit' );
                } );
    
                Route::post( 'update-order', [ ProductController::class, 'updateOrder' ] )->name( 'admin.product.updateOrder' );
                Route::post( 'all-products', [ ProductController::class, 'allProducts' ] )->name( 'admin.product.allProducts' );
                Route::post( 'one-product', [ ProductController::class, 'oneProduct' ] )->name( 'admin.product.oneProduct' );
                Route::post( 'create-product', [ ProductController::class, 'createProduct' ] )->name( 'admin.product.createProduct' );
                Route::post( 'update-product', [ ProductController::class, 'updateProduct' ] )->name( 'admin.product.updateProduct' );
                Route::post( 'delete-product', [ ProductController::class, 'deleteProduct' ] )->name( 'admin.product.deleteProduct' );
                Route::post( 'update-product-status', [ ProductController::class, 'updateProductStatus' ] )->name( 'admin.product.updateProductStatus' );
                Route::post( 'remove-product-gallery-image', [ ProductController::class, 'removeProductGalleryImage' ] )->name( 'admin.product.removeProductGalleryImage' );
                Route::post( 'ckeUpload', [ ProductController::class, 'ckeUpload' ] )->name( 'admin.product.ckeUpload' );
            } );

            Route::prefix( 'voucher-usages' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vouchers' ] ], function() {
                    Route::get( '/', [ VoucherUsageController::class, 'index' ] )->name( 'admin.voucher_usage.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vouchers' ] ], function() {
                    Route::get( 'add', [ VoucherUsageController::class, 'add' ] )->name( 'admin.voucher_usage.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vouchers' ] ], function() {
                    Route::get( 'edit', [ VoucherUsageController::class, 'edit' ] )->name( 'admin.voucher_usage.edit' );
                } );
    
                Route::post( 'all-voucher-usages', [ VoucherUsageController::class, 'allVoucherUsages' ] )->name( 'admin.voucher_usage.allVoucherUsages' );
                Route::post( 'one-voucher-usage', [ VoucherUsageController::class, 'oneVoucherUsage' ] )->name( 'admin.voucher_usage.oneVoucherUsage' );
                Route::post( 'create-voucher-usage', [ VoucherUsageController::class, 'createVoucherUsage' ] )->name( 'admin.voucher_usage.createVoucherUsage' );
                Route::post( 'update-voucher-usage', [ VoucherUsageController::class, 'updateVoucherUsage' ] )->name( 'admin.voucher_usage.updateVoucherUsage' );
                Route::post( 'update-voucher-usage-status', [ VoucherUsageController::class, 'updateVoucherUsageStatus' ] )->name( 'admin.voucher_usage.updateVoucherUsageStatus' );
                Route::post( 'remove-voucher-usage-gallery-image', [ VoucherUsageController::class, 'removeVoucherUsageGalleryImage' ] )->name( 'admin.voucher_usage.removeVoucherUsageGalleryImage' );
                Route::post( 'ckeUpload', [ VoucherUsageController::class, 'ckeUpload' ] )->name( 'admin.voucher_usage.ckeUpload' );
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

            Route::prefix( 'lucky_draw_rewards' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view lucky_draw_rewards' ] ], function() {
                    Route::get( '/', [ LuckyDrawController::class, 'index' ] )->name( 'admin.module_parent.lucky_draw_reward.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add lucky_draw_rewards' ] ], function() {
                    Route::get( 'add', [ LuckyDrawController::class, 'add' ] )->name( 'admin.lucky_draw_reward.add' );
                    Route::get( 'import', [ LuckyDrawController::class, 'import' ] )->name( 'admin.lucky_draw_reward.import' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit lucky_draw_rewards' ] ], function() {
                    Route::get( 'edit/{id?}', [ LuckyDrawController::class, 'edit' ] )->name( 'admin.lucky_draw_reward.edit' );
                } );

                Route::post( 'all-lucky-draw-reward', [ LuckyDrawController::class, 'allLuckyDrawRewards' ] )->name( 'admin.lucky_draw_reward.allLuckyDrawRewards' );
                Route::post( 'one-lucky-draw-reward', [ LuckyDrawController::class, 'oneLuckyDrawReward' ] )->name( 'admin.lucky_draw_reward.oneLuckyDrawReward' );
                Route::post( 'create-lucky-draw-reward', [ LuckyDrawController::class, 'createLuckyDrawReward' ] )->name( 'admin.lucky_draw_reward.createLuckyDrawReward' );
                Route::post( 'update-lucky-draw-reward', [ LuckyDrawController::class, 'updateLuckyDrawReward' ] )->name( 'admin.lucky_draw_reward.updateLuckyDrawReward' );
                Route::post( 'update-lucky-draw-reward-status', [ LuckyDrawController::class, 'updateLuckyDrawRewardStatus' ] )->name( 'admin.lucky_draw_reward.updateLuckyDrawRewardStatus' );
                Route::post( 'import-lucky-draw-reward', [ LuckyDrawController::class, 'importLuckyDrawReward' ] )->name( 'admin.lucky_draw_reward.importLuckyDrawReward' );
                Route::post( 'import-lucky-draw-reward-v2', [ LuckyDrawController::class, 'importLuckyDrawRewardV2' ] )->name( 'admin.lucky_draw_reward.importLuckyDrawRewardV2' );
            } );

            Route::prefix( 'ranks' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view ranks' ] ], function() {
                    Route::get( '/', [ RankController::class, 'index' ] )->name( 'admin.module_parent.rank.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add ranks' ] ], function() {
                    Route::get( 'add', [ RankController::class, 'add' ] )->name( 'admin.rank.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit ranks' ] ], function() {
                    Route::get( 'edit/{id?}', [ RankController::class, 'edit' ] )->name( 'admin.rank.edit' );
                } );

                Route::post( 'all-ranks', [ RankController::class, 'allRanks' ] )->name( 'admin.rank.allRanks' );
                Route::post( 'one-rank', [ RankController::class, 'oneRank' ] )->name( 'admin.rank.oneRank' );
                Route::post( 'create-rank', [ RankController::class, 'createRank' ] )->name( 'admin.rank.createRank' );
                Route::post( 'update-rank', [ RankController::class, 'updateRank' ] )->name( 'admin.rank.updateRank' );
                Route::post( 'update-rank-status', [ RankController::class, 'updateRankStatus' ] )->name( 'admin.rank.updateRankStatus' );
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