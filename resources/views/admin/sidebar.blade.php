            <!-- sidebar @s -->
            <div class="nk-sidebar nk-sidebar-fixed is-light " data-content="sidebarMenu">
                <div class="nk-sidebar-element nk-sidebar-head">
                    <div class="nk-sidebar-brand">
                        <a href="{{ route( 'admin.home' ) }}" class="logo-link nk-sidebar-logo">
                            <img class="logo-dark logo-img" src="{{ asset( 'admin/images/logo.png' ) . Helper::assetVersion() }}" srcset="{{ asset( 'admin/images/logo.png' ) . Helper::assetVersion() }} 2x" alt="logo-dark">
                            <img class="logo-small logo-img logo-img-small" src="{{ asset( 'admin/images/logo.png' ) . Helper::assetVersion() }}" srcset="{{ asset( 'admin/images/logo.png' ) . Helper::assetVersion() }} 2x" alt="logo-small">
                        </a>
                    </div>
                    <div class="nk-menu-trigger me-n2">
                        <a href="#" class="nk-nav-toggle nk-quick-nav-icon d-xl-none" data-target="sidebarMenu"><em class="icon ni ni-arrow-left"></em></a>
                        <a href="#" class="nk-nav-compact nk-quick-nav-icon d-none d-xl-inline-flex" data-target="sidebarMenu"><em class="icon ni ni-menu"></em></a>
                    </div>
                </div><!-- .nk-sidebar-element -->
                <div class="nk-sidebar-element">
                    <div class="nk-sidebar-content">
                        <div class="nk-sidebar-menu" data-simplebar>
                            <ul class="nk-menu">
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\DashboardController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.dashboard' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-growth-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.dashboard' ) }}</span>
                                        @if ( 1 == 2 )<span class="nk-menu-badge">HOT</span>@endif
                                    </a>
                                </li>
                                @can( 'view administrators' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\AdministratorController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.administrator.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user-list-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.administrators' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view roles' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\RoleController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.role.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user-list-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.roles' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view audits' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\AuditController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.audit.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-db-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.audit_logs' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                <li class="nk-menu-heading">
                                    <h6 class="overline-title text-primary-alt">{{ __( 'template.operations' ) }}</h6>
                                </li>
                                {{-- New module starts here --}}
                                @can( 'view Users' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\UserController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.user.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-user-group-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.users' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view Wallets' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\WalletController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.wallet.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-money"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.wallets' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view Wallet Transactions' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\WalletTransactionController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.wallet_transaction.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-swap"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.wallet_transactions' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view vending_machines' )
                                <li class="nk-menu-item has-sub {{ ($controller == 'App\Http\Controllers\Admin\OutletController' || $controller == 'App\Http\Controllers\Admin\VendingMachineController' || $controller == 'App\Http\Controllers\Admin\VendingMachineStockController') ? 'active current-page' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-dot-box"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.vending_machines' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        @if( 1 == 2 )
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\OutletController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.outlet.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.outlets' ) }}</span></a>
                                        </li>
                                        @endif
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\VendingMachineController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.vending_machine.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.vending_machines' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\VendingMachineStockController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.vending_machine_stock.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.vending_machine_stocks' ) }}</span></a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan

                                @can( 'view Froyos' )
                                <li class="nk-menu-item has-sub {{ ($controller == 'App\Http\Controllers\Admin\FroyoController' || $controller == 'App\Http\Controllers\Admin\SyrupController' || $controller == 'App\Http\Controllers\Admin\ToppingController') ? 'active current-page' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-centos"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.froyos' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\FroyoController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.froyo.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.froyos' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\SyrupController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.syrup.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.syrups' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ToppingController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.topping.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.toppings' ) }}</span></a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan

                                @can( 'view Products' )
                                <li class="nk-menu-item has-sub {{ ($controller == 'App\Http\Controllers\Admin\ProductController' || $controller == 'App\Http\Controllers\Admin\ProductBundleController' || $controller == 'App\Http\Controllers\Admin\UserBundleController') ? 'active current-page' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-centos"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.menus' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ProductController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.product.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.menus' ) }}</span></a>
                                        </li>
                                        
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ProductBundleController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.product_bundle.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.product_bundles' ) }}</span></a>
                                        </li>
                                        
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\UserBundleController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.user_bundle.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.user_bundles' ) }}</span></a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan

                                @can( 'view orders' )
                                <li class="nk-menu-item has-sub {{ ($controller == 'App\Http\Controllers\Admin\OrderController') ? 'active current-page' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-list-index-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.orders' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\OrderController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.order.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.orders' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\OrderController' && in_array( $action, [ 'scanner' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.order.scanner' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.scan_qr' ) }}</span></a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan

                                @can( 'view Vouchers' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\VoucherController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.voucher.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-ticket-alt"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.vouchers' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view User Vouchers' )
                                <li class="nk-menu-item has-sub {{ ($controller == 'App\Http\Controllers\Admin\UserVoucherController' || $controller == 'App\Http\Controllers\Admin\VoucherUsageController') ? 'active current-page' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-ticket-plus"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.user_vouchers' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\UserVoucherController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.user_voucher.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.user_vouchers' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\VoucherUsageController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.voucher_usage.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.voucher_usages' ) }}</span></a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan

                                @can( 'view Checkin Rewards' )
                                <li class="nk-menu-item has-sub {{ ($controller == 'App\Http\Controllers\Admin\UserCheckinController' || $controller == 'App\Http\Controllers\Admin\CheckinRewardController') ? 'active current-page' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-check-c"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.checkins' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\CheckinRewardController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.checkin_reward.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.checkin_rewards' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\UserCheckinController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.user_checkin.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.user_checkins' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\UserCheckinController' && in_array( $action, [ 'calendar' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.user_checkin.calendar' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.checkin_calendar' ) }}</span></a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan

                                @can( 'view Settings' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\SettingController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.setting.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-setting-alt"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.settings' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                            </ul><!-- .nk-menu -->
                        </div><!-- .nk-sidebar-menu -->
                    </div><!-- .nk-sidebar-content -->
                </div><!-- .nk-sidebar-element -->
            </div>
            <!-- sidebar @e -->