<?php

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Http\Controllers\Admin\{
    AdministratorController,
    RoleController,
    AuditController,
    BookingController,
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
    OutletController,
    VendingMachineController,
    FroyoController,
    SyrupController,
    ToppingController,
    ProductController,
    VendingMachineStockController,
    VendingMachineProductController,
    OrderController,
    PaymentController,
    VoucherController,
    UserVoucherController,
    VoucherUsageController,
    UserCheckinController,
    CheckinRewardController,
    ProductBundleController,
    UserBundleController,
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
                    Route::get( 'salesmen', [ AdministratorController::class, 'indexSalesman' ] )->name( 'admin.module_parent.administrator.indexSalesman' );
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
                } );
                Route::group( [ 'middleware' => [ 'permission:add users' ] ], function() {
                    Route::get( 'add', [ UserController::class, 'add' ] )->name( 'admin.user.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit users' ] ], function() {
                    Route::get( 'edit', [ UserController::class, 'edit' ] )->name( 'admin.user.edit' );
                } );

                Route::post( 'all-users', [ UserController::class, 'allUsers' ] )->name( 'admin.user.allUsers' );
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
            Route::prefix( 'outlets' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view outlets' ] ], function() {
                    Route::get( '/', [ OutletController::class, 'index' ] )->name( 'admin.module_parent.outlet.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add outlets' ] ], function() {
                    Route::get( 'add', [ OutletController::class, 'add' ] )->name( 'admin.outlet.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit outlets' ] ], function() {
                    Route::get( 'edit', [ OutletController::class, 'edit' ] )->name( 'admin.outlet.edit' );
                } );

                Route::post( 'all-outlets', [ OutletController::class, 'allOutlets' ] )->name( 'admin.outlet.allOutlets' );
                Route::post( 'one-outlet', [ OutletController::class, 'oneOutlet' ] )->name( 'admin.outlet.oneOutlet' );
                Route::post( 'create-outlet', [ OutletController::class, 'createOutlet' ] )->name( 'admin.outlet.createOutlet' );
                Route::post( 'update-outlet', [ OutletController::class, 'updateOutlet' ] )->name( 'admin.outlet.updateOutlet' );
                Route::post( 'update-outlet-status', [ OutletController::class, 'updateOutletStatus' ] )->name( 'admin.outlet.updateOutletStatus' );
                Route::post( 'remove-outlet-gallery-image', [ OutletController::class, 'removeOutletGalleryImage' ] )->name( 'admin.outlet.removeOutletGalleryImage' );
            } );

            Route::prefix( 'vending-machines' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vendingmachines' ] ], function() {
                    Route::get( '/', [ VendingMachineController::class, 'index' ] )->name( 'admin.module_parent.vending_machine.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vendingmachines' ] ], function() {
                    Route::get( 'add', [ VendingMachineController::class, 'add' ] )->name( 'admin.vending_machine.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vendingmachines' ] ], function() {
                    Route::get( 'edit', [ VendingMachineController::class, 'edit' ] )->name( 'admin.vending_machine.edit' );
                } );

                Route::post( 'all-vending-machines', [ VendingMachineController::class, 'allVendingMachines' ] )->name( 'admin.vending_machine.allVendingMachines' );
                Route::post( 'one-vending-machine', [ VendingMachineController::class, 'oneVendingMachine' ] )->name( 'admin.vending_machine.oneVendingMachine' );
                Route::post( 'create-vending-machine', [ VendingMachineController::class, 'createVendingMachine' ] )->name( 'admin.vending_machine.createVendingMachine' );
                Route::post( 'update-vending-machine', [ VendingMachineController::class, 'updateVendingMachine' ] )->name( 'admin.vending_machine.updateVendingMachine' );
                Route::post( 'update-vending-machine-status', [ VendingMachineController::class, 'updateVendingMachineStatus' ] )->name( 'admin.vending_machine.updateVendingMachineStatus' );
                Route::post( 'remove-vending-machine-gallery-image', [ VendingMachineController::class, 'removeVendingMachineGalleryImage' ] )->name( 'admin.vending_machine.removeVendingMachineGalleryImage' );
            } );

            Route::prefix( 'vending-machine-stocks' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vendingmachinestocks' ] ], function() {
                    Route::get( '/', [ VendingMachineStockController::class, 'index' ] )->name( 'admin.module_parent.vending_machine_stock.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vendingmachinestocks' ] ], function() {
                    Route::get( 'add', [ VendingMachineStockController::class, 'add' ] )->name( 'admin.vending_machine_stock.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vendingmachinestocks' ] ], function() {
                    Route::get( 'edit', [ VendingMachineStockController::class, 'edit' ] )->name( 'admin.vending_machine_stock.edit' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vendingmachinestocks' ] ], function() {
                    Route::get( 'history', [ VendingMachineStockController::class, 'history' ] )->name( 'admin.vending_machine_stock.history' );
                } );

                Route::post( 'all-vending-machine-stocks', [ VendingMachineStockController::class, 'allVendingMachineStocks' ] )->name( 'admin.vending_machine_stock.allVendingMachineStocks' );
                Route::post( 'one-vending-machine-stock', [ VendingMachineStockController::class, 'oneVendingMachineStock' ] )->name( 'admin.vending_machine_stock.oneVendingMachineStock' );
                Route::post( 'create-vending-machine-stock', [ VendingMachineStockController::class, 'createVendingMachineStock' ] )->name( 'admin.vending_machine_stock.createVendingMachineStock' );
                Route::post( 'update-vending-machine-stock', [ VendingMachineStockController::class, 'updateVendingMachineStock' ] )->name( 'admin.vending_machine_stock.updateVendingMachineStock' );
                Route::post( 'update-vending-machine-stock-status', [ VendingMachineStockController::class, 'updateVendingMachineStockStatus' ] )->name( 'admin.vending_machine_stock.updateVendingMachineStockStatus' );
                Route::post( 'remove-vending-machine-stock-gallery-image', [ VendingMachineStockController::class, 'removeVendingMachineStockGalleryImage' ] )->name( 'admin.vending_machine_stock.removeVendingMachineStockGalleryImage' );
            } );

            Route::prefix( 'vending-machine-products' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vendingmachineproducts' ] ], function() {
                    Route::get( '/', [ VendingMachineProductController::class, 'index' ] )->name( 'admin.module_parent.vending_machine_product.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vendingmachineproducts' ] ], function() {
                    Route::get( 'add', [ VendingMachineProductController::class, 'add' ] )->name( 'admin.vending_machine_product.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vendingmachineproducts' ] ], function() {
                    Route::get( 'edit', [ VendingMachineProductController::class, 'edit' ] )->name( 'admin.vending_machine_product.edit' );
                } );

                Route::post( 'all-vending-machine-products', [ VendingMachineProductController::class, 'allVendingMachineProducts' ] )->name( 'admin.vending_machine_product.allVendingMachineProducts' );
                Route::post( 'one-vending-machine-product', [ VendingMachineProductController::class, 'oneVendingMachineProduct' ] )->name( 'admin.vending_machine_product.oneVendingMachineProduct' );
                Route::post( 'create-vending-machine-product', [ VendingMachineProductController::class, 'createVendingMachineProduct' ] )->name( 'admin.vending_machine_product.createVendingMachineProduct' );
                Route::post( 'update-vending-machine-product', [ VendingMachineProductController::class, 'updateVendingMachineProduct' ] )->name( 'admin.vending_machine_product.updateVendingMachineProduct' );
                Route::post( 'update-vending-machine-product-status', [ VendingMachineProductController::class, 'updateVendingMachineProductStatus' ] )->name( 'admin.vending_machine_product.updateVendingMachineProductStatus' );
                Route::post( 'remove-vending-machine-product-gallery-image', [ VendingMachineProductController::class, 'removeVendingMachineProductGalleryImage' ] )->name( 'admin.vending_machine_product.removeVendingMachineProductGalleryImage' );
            } );

            Route::prefix( 'froyos' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view froyos' ] ], function() {
                    Route::get( '/', [ FroyoController::class, 'index' ] )->name( 'admin.module_parent.froyo.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add froyos' ] ], function() {
                    Route::get( 'add', [ FroyoController::class, 'add' ] )->name( 'admin.froyo.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit froyos' ] ], function() {
                    Route::get( 'edit', [ FroyoController::class, 'edit' ] )->name( 'admin.froyo.edit' );
                } );

                Route::post( 'all-froyos', [ FroyoController::class, 'allFroyos' ] )->name( 'admin.froyo.allFroyos' );
                Route::post( 'all-stock-froyos', [ FroyoController::class, 'allStocksFroyos' ] )->name( 'admin.froyo.allStocksFroyos' );
                Route::post( 'all-stock-froyos-for-vending-machine', [ FroyoController::class, 'allFroyosForVendingMachine' ] )->name( 'admin.froyo.allFroyosForVendingMachine' );
                Route::post( 'get-froyo-stock', [ FroyoController::class, 'getFroyoStock' ] )->name( 'admin.froyo.getFroyoStock' );
                Route::post( 'one-froyo', [ FroyoController::class, 'oneFroyo' ] )->name( 'admin.froyo.oneFroyo' );
                Route::post( 'create-froyo', [ FroyoController::class, 'createFroyo' ] )->name( 'admin.froyo.createFroyo' );
                Route::post( 'update-froyo', [ FroyoController::class, 'updateFroyo' ] )->name( 'admin.froyo.updateFroyo' );
                Route::post( 'update-froyo-status', [ FroyoController::class, 'updateFroyoStatus' ] )->name( 'admin.froyo.updateFroyoStatus' );
                Route::post( 'remove-froyo-gallery-image', [ FroyoController::class, 'removeFroyoGalleryImage' ] )->name( 'admin.froyo.removeFroyoGalleryImage' );
            } );

            Route::prefix( 'syrups' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view syrups' ] ], function() {
                    Route::get( '/', [ SyrupController::class, 'index' ] )->name( 'admin.module_parent.syrup.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add syrups' ] ], function() {
                    Route::get( 'add', [ SyrupController::class, 'add' ] )->name( 'admin.syrup.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit syrups' ] ], function() {
                    Route::get( 'edit', [ SyrupController::class, 'edit' ] )->name( 'admin.syrup.edit' );
                } );

                Route::post( 'all-syrups', [ SyrupController::class, 'allSyrups' ] )->name( 'admin.syrup.allSyrups' );
                Route::post( 'all-stock-syrups', [ SyrupController::class, 'allStocksSyrups' ] )->name( 'admin.froyo.allStocksSyrups' );
                Route::post( 'one-syrup', [ SyrupController::class, 'oneSyrup' ] )->name( 'admin.syrup.oneSyrup' );
                Route::post( 'create-syrup', [ SyrupController::class, 'createSyrup' ] )->name( 'admin.syrup.createSyrup' );
                Route::post( 'update-syrup', [ SyrupController::class, 'updateSyrup' ] )->name( 'admin.syrup.updateSyrup' );
                Route::post( 'update-syrup-status', [ SyrupController::class, 'updateSyrupStatus' ] )->name( 'admin.syrup.updateSyrupStatus' );
                Route::post( 'remove-syrup-gallery-image', [ SyrupController::class, 'removeSyrupGalleryImage' ] )->name( 'admin.syrup.removeSyrupGalleryImage' );
            } );

            Route::prefix( 'toppings' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view toppings' ] ], function() {
                    Route::get( '/', [ ToppingController::class, 'index' ] )->name( 'admin.module_parent.topping.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add toppings' ] ], function() {
                    Route::get( 'add', [ ToppingController::class, 'add' ] )->name( 'admin.topping.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit toppings' ] ], function() {
                    Route::get( 'edit', [ ToppingController::class, 'edit' ] )->name( 'admin.topping.edit' );
                } );

                Route::post( 'all-toppings', [ ToppingController::class, 'allToppings' ] )->name( 'admin.topping.allToppings' );
                Route::post( 'all-stock-toppings', [ ToppingController::class, 'allStocksToppings' ] )->name( 'admin.froyo.allStocksToppings' );
                Route::post( 'one-topping', [ ToppingController::class, 'oneTopping' ] )->name( 'admin.topping.oneTopping' );
                Route::post( 'create-topping', [ ToppingController::class, 'createTopping' ] )->name( 'admin.topping.createTopping' );
                Route::post( 'update-topping', [ ToppingController::class, 'updateTopping' ] )->name( 'admin.topping.updateTopping' );
                Route::post( 'update-topping-status', [ ToppingController::class, 'updateToppingStatus' ] )->name( 'admin.topping.updateToppingStatus' );
                Route::post( 'remove-topping-gallery-image', [ ToppingController::class, 'removeToppingGalleryImage' ] )->name( 'admin.topping.removeToppingGalleryImage' );
            } );

            Route::prefix( 'products' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view products' ] ], function() {
                    Route::get( '/', [ ProductController::class, 'index' ] )->name( 'admin.module_parent.product.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add products' ] ], function() {
                    Route::get( 'add', [ ProductController::class, 'add' ] )->name( 'admin.product.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit products' ] ], function() {
                    Route::get( 'edit', [ ProductController::class, 'edit' ] )->name( 'admin.product.edit' );
                } );
    
                Route::post( 'all-products', [ ProductController::class, 'allProducts' ] )->name( 'admin.product.allProducts' );
                Route::post( 'one-product', [ ProductController::class, 'oneProduct' ] )->name( 'admin.product.oneProduct' );
                Route::post( 'create-product', [ ProductController::class, 'createProduct' ] )->name( 'admin.product.createProduct' );
                Route::post( 'update-product', [ ProductController::class, 'updateProduct' ] )->name( 'admin.product.updateProduct' );
                Route::post( 'update-product-status', [ ProductController::class, 'updateProductStatus' ] )->name( 'admin.product.updateProductStatus' );
                Route::post( 'remove-product-gallery-image', [ ProductController::class, 'removeProductGalleryImage' ] )->name( 'admin.product.removeProductGalleryImage' );
    
            } );

            Route::prefix( 'orders' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view orders' ] ], function() {
                    Route::get( '/', [ OrderController::class, 'index' ] )->name( 'admin.module_parent.order.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add orders' ] ], function() {
                    Route::get( 'add', [ OrderController::class, 'add' ] )->name( 'admin.order.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit orders' ] ], function() {
                    Route::get( 'edit', [ OrderController::class, 'edit' ] )->name( 'admin.order.edit' );
                } );
    
                Route::post( 'all-orders', [ OrderController::class, 'allOrders' ] )->name( 'admin.order.allOrders' );
                Route::post( 'one-order', [ OrderController::class, 'oneOrder' ] )->name( 'admin.order.oneOrder' );
                Route::post( 'create-order', [ OrderController::class, 'createOrder' ] )->name( 'admin.order.createOrder' );
                Route::post( 'update-order', [ OrderController::class, 'updateOrder' ] )->name( 'admin.order.updateOrder' );
                Route::post( 'update-order-status', [ OrderController::class, 'updateOrderStatus' ] )->name( 'admin.order.updateOrderStatus' );
                Route::post( 'update-order-status-view', [ OrderController::class, 'updateOrderStatusView' ] )->name( 'admin.order.updateOrderStatusView' );

                Route::get( 'scanner', [ OrderController::class, 'scanner' ] )->name( 'admin.order.scanner' );
                Route::post( 'scanned-order', [ OrderController::class, 'scannedOrder' ] )->name( 'admin.order.scannedOrder' );

            } );

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
                Route::post( 'bonus-settings', [ SettingController::class, 'bonusSettings' ] )->name( 'admin.setting.bonusSettings' );
                Route::post( 'maintenance-settings', [ SettingController::class, 'maintenanceSettings' ] )->name( 'admin.setting.maintenanceSettings' );
                Route::post( 'update-bonus-setting', [ SettingController::class, 'updateBonusSetting' ] )->name( 'admin.setting.updateBonusSetting' );
                Route::post( 'update-maintenance-setting', [ SettingController::class, 'updateMaintenanceSetting' ] )->name( 'admin.setting.updateMaintenanceSetting' );
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

            Route::prefix( 'voucher-usages' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vouchers' ] ], function() {
                    Route::get( '/', [ VoucherUsageController::class, 'index' ] )->name( 'admin.module_parent.voucher_usage.index' );
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

            Route::prefix( 'product-bundles' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vouchers' ] ], function() {
                    Route::get( '/', [ ProductBundleController::class, 'index' ] )->name( 'admin.module_parent.product_bundle.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vouchers' ] ], function() {
                    Route::get( 'add', [ ProductBundleController::class, 'add' ] )->name( 'admin.product_bundle.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vouchers' ] ], function() {
                    Route::get( 'edit', [ ProductBundleController::class, 'edit' ] )->name( 'admin.product_bundle.edit' );
                } );
    
                Route::post( 'all-product-bundles', [ ProductBundleController::class, 'allProductBundles' ] )->name( 'admin.product_bundle.allProductBundles' );
                Route::post( 'one-product-bundle', [ ProductBundleController::class, 'oneProductBundle' ] )->name( 'admin.product_bundle.oneProductBundle' );
                Route::post( 'create-product-bundle', [ ProductBundleController::class, 'createProductBundle' ] )->name( 'admin.product_bundle.createProductBundle' );
                Route::post( 'update-product-bundle', [ ProductBundleController::class, 'updateProductBundle' ] )->name( 'admin.product_bundle.updateProductBundle' );
                Route::post( 'update-product-bundle-status', [ ProductBundleController::class, 'updateProductBundleStatus' ] )->name( 'admin.product_bundle.updateProductBundleStatus' );
                Route::post( 'remove-product-bundle-gallery-image', [ ProductBundleController::class, 'removeProductBundleGalleryImage' ] )->name( 'admin.product_bundle.removeProductBundleGalleryImage' );
                Route::post( 'ckeUpload', [ ProductBundleController::class, 'ckeUpload' ] )->name( 'admin.product_bundle.ckeUpload' );
            } );

            Route::prefix( 'user-bundles' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vouchers' ] ], function() {
                    Route::get( '/', [ UserBundleController::class, 'index' ] )->name( 'admin.module_parent.user_bundle.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vouchers' ] ], function() {
                    Route::get( 'add', [ UserBundleController::class, 'add' ] )->name( 'admin.user_bundle.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vouchers' ] ], function() {
                    Route::get( 'edit', [ UserBundleController::class, 'edit' ] )->name( 'admin.user_bundle.edit' );
                } );
    
                Route::post( 'all-user-bundles', [ UserBundleController::class, 'allUserBundles' ] )->name( 'admin.user_bundle.allUserBundles' );
                Route::post( 'one-user-bundle', [ UserBundleController::class, 'oneuUserBundle' ] )->name( 'admin.user_bundle.oneuUserBundle' );
                Route::post( 'create-user-bundle', [ UserBundleController::class, 'createUserBundle' ] )->name( 'admin.user_bundle.createUserBundle' );
                Route::post( 'update-user-bundle', [ UserBundleController::class, 'updateUserBundle' ] )->name( 'admin.user_bundle.updateUserBundle' );
                Route::post( 'update-user-bundle-status', [ UserBundleController::class, 'updateUserBundleStatus' ] )->name( 'admin.user_bundle.updateUserBundleStatus' );
                Route::post( 'remove-user-bundle-gallery-image', [ UserBundleController::class, 'removeUserBundleGalleryImage' ] )->name( 'admin.user_bundle.removeUserBundleGalleryImage' );
                Route::post( 'ckeUpload', [ UserBundleController::class, 'ckeUpload' ] )->name( 'admin.user_bundle.ckeUpload' );
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

Route::prefix( 'eghl' )->group( function() {
    Route::get( 'initiate', [ PaymentController::class, 'initEghl' ] )->withoutMiddleware( [ \App\Http\Middleware\VerifyCsrfToken::class ] );
    Route::any( 'notify', [ PaymentController::class, 'notifyEghl' ] )->withoutMiddleware( [ \App\Http\Middleware\VerifyCsrfToken::class ] );
    Route::any( 'query', [ PaymentController::class, 'queryEghl' ] )->withoutMiddleware( [ \App\Http\Middleware\VerifyCsrfToken::class ] );
    Route::any( 'callback', [PaymentController::class, 'callbackEghl'] )->name( 'payment.callbackEghl' )->withoutMiddleware( [ \App\Http\Middleware\VerifyCsrfToken::class ] );
    Route::any( 'fallback', [PaymentController::class, 'fallbackEghl'] )->name( 'payment.fallbackEghl' )->withoutMiddleware( [ \App\Http\Middleware\VerifyCsrfToken::class ] );
} );


if( 1 == 2 ){
    Route::prefix('eghl-test')->group(function () {
        Route::get('/', function () {
            $order = Order::latest()->first();
    
            $data = [
                'TransactionType' => 'SALE',
                'PymtMethod' => 'ANY',
                'ServiceID' => config('services.eghl.merchant_id'),
                'PaymentID' => $order->reference . '-' . $order->payment_attempt,
                'OrderNumber' => $order->reference,
                'PaymentDesc' => $order->reference,
                'MerchantName' => 'Yobe Froyo',
                'MerchantReturnURL' => config('services.eghl.staging_callabck_url'),
                'Amount' => $order->total_price,
                'CurrencyCode' => 'MYR',
                'CustIP' => request()->ip(),
                'CustName' => $order->user->username ?? 'Yobe Guest',
                'HashValue' => '',
                'CustEmail' => $order->user->email ?? 'yobeguest@gmail.com',
                'CustPhone' => $order->user->phone_number,
                'MerchantTermsURL' => null,
                'LanguageCode' => 'en',
                'PageTimeout' => '780',
            ];
    
            $data['HashValue'] = Helper::generatePaymentHash($data);
            $url2 = config('services.eghl.test_url') . '?' . http_build_query($data);
    
            $orderTransaction = OrderTransaction::create( [
                'order_id' => $order->id,
                'checkout_id' => null,
                'checkout_url' => null,
                'payment_url' => $url2,
                'transaction_id' => null,
                'layout_version' => 'v1',
                'redirect_url' => null,
                'notify_url' => null,
                'order_no' => $order->reference,
                'order_title' => $order->reference,
                'order_detail' => $order->reference,
                'amount' => $order->total_price,
                'currency' => 'MYR',
                'transaction_type' => 1,
                'status' => 10,
            ] );
    
            $order->payment_url = $url2;
            $order->order_transaction_id = $orderTransaction->id;
            $order->save();
    
            return redirect($url2);
        });
    });
}