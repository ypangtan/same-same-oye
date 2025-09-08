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
                                @can( 'view users' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\UserController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.user.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-user-group-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.users' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view wallets' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\WalletController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.wallet.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-money"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.wallets' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view wallet_transactions' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\WalletTransactionController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.wallet_transaction.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-swap"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.wallet_transactions' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view sales_records' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\SalesRecordController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.sales_record.index' ) }}" class="nk-menu-link">
                                           <span class="nk-menu-icon"><em class="icon ni ni-report-profit"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.sales_records' ) }}</span>
                                        </a>
                                    </li>
                                @endcan 

                                @can( 'view announcements' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\AnnouncementController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.announcement.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-list-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.announcements' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                @if( 1 == 2 )

                                    @can( 'view Product' )
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ProductController' ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.product.index' ) }}" class="nk-menu-link">
                                                <span class="nk-menu-icon"><em class="icon ni ni-notes-alt"></em></span>
                                                <span class="nk-menu-text">{{ __( 'template.products' ) }}</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can( 'view announcements' )
                                    <li class="nk-menu-item has-sub {{ ($controller == 'App\Http\Controllers\Admin\AnnouncementController' || $controller == 'App\Http\Controllers\Admin\AnnouncementRewardController' ) ? 'active current-page' : '' }}">
                                        <a href="#" class="nk-menu-link nk-menu-toggle">
                                            <span class="nk-menu-icon"><em class="icon ni ni-list-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.announcements' ) }}</span>
                                        </a>
                                        <ul class="nk-menu-sub">
                                            <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\AnnouncementController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                                <a href="{{ route( 'admin.module_parent.announcement.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.announcements' ) }}</span></a>
                                            </li>
                                            @if( 1 == 2 )
                                            <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\AnnouncementRewardController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                                <a href="{{ route( 'admin.module_parent.announcement_reward.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.announcement_rewards' ) }}</span></a>
                                            </li>
                                            @endif
                                        </ul>
                                    </li>
                                    @endcan
                                @endif

                                @can( 'view banners' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\BannerController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.banner.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-flag-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.banners' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view vouchers' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\VoucherController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.voucher.index' ) }}" class="nk-menu-link">
                                           <span class="nk-menu-icon"><em class="icon ni ni-ticket-alt"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.vouchers' ) }}</span>
                                        </a>
                                    </li>
                                @endcan 

                                @can( 'view user_vouchers' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\UserVoucherController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.user_voucher.index' ) }}" class="nk-menu-link">
                                           <span class="nk-menu-icon"><em class="icon ni ni-ticket-plus"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.user_vouchers' ) }}</span>
                                        </a>
                                    </li>
                                @endcan 

                                @if( 1 == 2 )
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
                                @endif

                                @can( 'view checkin_rewards' )
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
                                        @if( 1 == 2)
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\UserCheckinController' && in_array( $action, [ 'calendar' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.user_checkin.calendar' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.checkin_calendar' ) }}</span></a>
                                        </li>
                                        @endif
                                    </ul>
                                </li>
                                @endcan
                                
                                @canany( [ 'view marketing_notifications', 'view pop_announcements' ] )
                                <li class="nk-menu-item has-sub {{ ($controller == 'App\Http\Controllers\Admin\MarketingAnnouncementController' || $controller == 'App\Http\Controllers\Admin\PopAnnouncementController') ? 'active current-page' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-note-add-c"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.marketing_notifications' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        @can( 'view marketing_notifications' )
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\MarketingAnnouncementController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.marketing_notifications.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.marketing_notifications' ) }}</span></a>
                                        </li>
                                        @endcan
                                        @can( 'view pop_announcements' )
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\PopAnnouncementController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.pop_announcement.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.pop_announcements' ) }}</span></a>
                                        </li>
                                        @endcan
                                    </ul>
                                </li>
                                @endcan

                                @can( 'view lucky_draw_rewards' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\LuckyDrawController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.lucky_draw_reward.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon fa fa-pie-chart"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.lucky_draw_rewards' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view ranks' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\RankController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.rank.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon fa fa-signal"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.ranks' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view settings' )
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