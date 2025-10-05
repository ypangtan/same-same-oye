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
                                @can( 'view otp_logs' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\OtpLogController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.otp_log.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-notes"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.otp_logs' ) }}</span>
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
                                
                                @canany( [ 'view items', 'view playlists', 'view collections' ] )
                                <li class="nk-menu-item has-sub {{ ($controller == 'App\Http\Controllers\Admin\CollectionController' || $controller == 'App\Http\Controllers\Admin\PlaylistController' || $controller == 'App\Http\Controllers\Admin\ItemController' ) ? 'active current-page' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-note-add-c"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.songs' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        @can( 'view collections' )
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\CollectionController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.collection.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.collections' ) }}</span></a>
                                        </li>
                                        @endcan
                                        @can( 'view playlists' )
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\PlaylistController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.playlist.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.playlists' ) }}</span></a>
                                        </li>
                                        @endcan
                                        @can( 'view items' )
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ItemController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.item.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.items' ) }}</span></a>
                                        </li>
                                        @endcan
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

                                {{-- @can( 'view ranks' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\RankController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.rank.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon fa fa-signal"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.ranks' ) }}</span>
                                        </a>
                                    </li>
                                @endcan --}}

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