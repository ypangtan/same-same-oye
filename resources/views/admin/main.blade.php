<?php echo view( 'admin/header', [ 'header' => @$header ] );?>

<body class="nk-body bg-lighter npc-default has-sidebar ">
    <div class="nk-app-root">
        <!-- main @s -->
        <div class="nk-main ">
            <?php echo view( 'admin/sidebar', [ 'controller' => @$controller, 'action' => @$action ] );?>
            <?php
            echo $controller;
            ?>
            <!-- wrap @s -->
            <div class="nk-wrap ">
                <!-- main header @s -->
                <div class="nk-header nk-header-fixed is-light">
                    <div class="container-fluid">
                        <div class="nk-header-wrap">
                            <div class="nk-menu-trigger d-xl-none ms-n1">
                                <a href="#" class="nk-nav-toggle nk-quick-nav-icon" data-target="sidebarMenu"><em class="icon ni ni-menu"></em></a>
                            </div>
                            <div class="nk-header-brand d-xl-none">
                                <a href="{{ route( 'admin.home' ) }}" class="logo-link">
                                    <img class="logo-dark logo-img" src="{{ asset( 'admin/images/logo.png' ) . Helper::assetVersion() }}" srcset="{{ asset( 'admin/images/logo.png' ) . Helper::assetVersion() }} 2x" alt="logo-dark">
                                </a>
                            </div><!-- .nk-header-brand -->
                            @if ( 1 == 2 )
                            <div class="nk-header-search ms-3 ms-xl-0">
                                <em class="icon ni ni-search"></em>
                                <input type="text" class="form-control border-transparent form-focus-none" placeholder="Search anything">
                            </div><!-- .nk-header-news -->
                            @endif
                            <div class="nk-header-tools">
                                <ul class="nk-quick-nav">
                                    <li class="dropdown language-dropdown d-none d-sm-block me-n1">
                                        <a href="#" class="dropdown-toggle nk-quick-nav-icon" data-bs-toggle="dropdown">
                                            <div class="quick-icon border border-light">
                                                <img class="icon" src="{{ asset( 'admin/images/flags/' . App::getLocale() . '-sq.svg' ) }}" alt="">
                                            </div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-s1">
                                            <ul class="language-list">
@foreach ( Config::get( 'languages' ) as $lang => $language )
@if ( $lang != App::getLocale() )
                                                <li>
                                                    <a href="{{ route( 'admin.switchLanguage', [ 'lang' => $lang ] ) }}" class="language-item">
                                                        <img src="{{ asset( 'admin/images/flags/' . $lang . '.svg' ) }}" alt="" class="language-flag">
                                                        <span class="language-name">{{ $language }}</span>
                                                    </a>
                                                </li>
@endif
@endforeach
                                            </ul>
                                        </div>
                                    </li><!-- .dropdown -->
                                    <li class="dropdown notification-dropdown">
                                        <a href="#" class="dropdown-toggle nk-quick-nav-icon" data-bs-toggle="dropdown">
                                            @if ( 1 == 2 )
                                            <div class="icon-status icon-status-info"><em class="icon ni ni-bell"></em></div>
                                            @endif
                                            <em class="icon ni ni-bell"></em>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-xl dropdown-menu-end">
                                            <div class="dropdown-head">
                                                <span class="sub-title nk-dropdown-title">{{ __( 'notification.notifications' ) }}</span>
                                                <div class="link-primary" role="button" id="readAllNotification">{{ __( 'notification.mark_all_as_read' ) }}</div>
                                            </div>
                                            <div class="dropdown-body">
                                                <div class="nk-notification">
                                                    @if ( 1 == 2 )
                                                    <div class="nk-notification-item dropdown-inner">
                                                        <div class="nk-notification-icon">
                                                            <em class="icon icon-circle bg-warning-dim ni ni-curve-down-right"></em>
                                                        </div>
                                                        <div class="nk-notification-content">
                                                            <strong>Road Expiring</strong>
                                                            <div class="nk-notification-text">You have requested to <span>Widthdrawl</span></div>
                                                            <div class="nk-notification-time">2 hrs ago</div>
                                                        </div>
                                                    </div>
                                                    <div class="nk-notification-item dropdown-inner">
                                                        <div class="nk-notification-icon">
                                                            <em class="icon icon-circle bg-success-dim ni ni-curve-down-left"></em>
                                                        </div>
                                                        <div class="nk-notification-content">
                                                            <div class="nk-notification-text">Your <span>Deposit Order</span> is placed</div>
                                                            <div class="nk-notification-time">2 hrs ago</div>
                                                        </div>
                                                    </div>
                                                    <div class="nk-notification-item dropdown-inner">
                                                        <div class="nk-notification-icon">
                                                            <em class="icon icon-circle bg-warning-dim ni ni-curve-down-right"></em>
                                                        </div>
                                                        <div class="nk-notification-content">
                                                            <div class="nk-notification-text">You have requested to <span>Widthdrawl</span></div>
                                                            <div class="nk-notification-time">2 hrs ago</div>
                                                        </div>
                                                    </div>
                                                    <div class="nk-notification-item dropdown-inner">
                                                        <div class="nk-notification-icon">
                                                            <em class="icon icon-circle bg-success-dim ni ni-curve-down-left"></em>
                                                        </div>
                                                        <div class="nk-notification-content">
                                                            <div class="nk-notification-text">Your <span>Deposit Order</span> is placed</div>
                                                            <div class="nk-notification-time">2 hrs ago</div>
                                                        </div>
                                                    </div>
                                                    <div class="nk-notification-item dropdown-inner">
                                                        <div class="nk-notification-icon">
                                                            <em class="icon icon-circle bg-warning-dim ni ni-curve-down-right"></em>
                                                        </div>
                                                        <div class="nk-notification-content">
                                                            <div class="nk-notification-text">You have requested to <span>Widthdrawl</span></div>
                                                            <div class="nk-notification-time">2 hrs ago</div>
                                                        </div>
                                                    </div>
                                                    <div class="nk-notification-item dropdown-inner">
                                                        <div class="nk-notification-icon">
                                                            <em class="icon icon-circle bg-success-dim ni ni-curve-down-left"></em>
                                                        </div>
                                                        <div class="nk-notification-content">
                                                            <div class="nk-notification-text">Your <span>Deposit Order</span> is placed</div>
                                                            <div class="nk-notification-time">2 hrs ago</div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div><!-- .nk-notification -->
                                            </div><!-- .nk-dropdown-body -->
                                        </div>
                                    </li>
                                    <li class="dropdown user-dropdown">
                                        <a href="#" class="dropdown-toggle me-n1" data-bs-toggle="dropdown">
                                            <div class="user-toggle">
                                                <div class="user-avatar sm">
                                                    <img src="https://ui-avatars.com/api/?background=4C9FA3&color=fff&name={{ auth()->user()->fullname }}" alt="" />
                                                </div>
                                                <div class="user-info d-none d-xl-block">
                                                    <div class="user-status">{{ auth()->user()->getRoleNames()->first() ?? '-' }}</div>
                                                    <div class="user-name dropdown-indicator">{{ auth()->user()->fullname }}</div>
                                                </div>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-md dropdown-menu-end">
                                            <div class="dropdown-inner user-card-wrap bg-lighter d-none d-md-block">
                                                <div class="user-card">
                                                    <div class="user-avatar">
                                                        <img src="https://ui-avatars.com/api/?background=4C9FA3&color=fff&name={{ auth()->user()->fullname }}" alt="" />
                                                    </div>
                                                    <div class="user-info">
                                                        <span class="lead-text">{{ auth()->user()->fullname }}</span>
                                                        <span class="sub-text">{{ auth()->user()->email }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            @if ( 1 == 2 )
                                            <div class="dropdown-inner">
                                                <ul class="link-list">
                                                    <li><a href="html/user-profile-regular.html"><em class="icon ni ni-user-alt"></em><span>View Profile</span></a></li>
                                                    <li><a href="html/user-profile-setting.html"><em class="icon ni ni-setting-alt"></em><span>Account Setting</span></a></li>
                                                </ul>
                                            </div>
                                            @endif
                                            <div class="dropdown-inner">
                                                <ul class="link-list">
                                                    <li><a href="#" id="_logout"><em class="icon ni ni-signout"></em><span>{{ __( 'template.sign_out' ) }}</span></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div><!-- .nk-header-wrap -->
                    </div><!-- .container-fliud -->
                </div>
                <!-- main header @e -->
                <!-- content @s -->
                <div class="nk-content ">
                    <div class="container-fluid">
                        <div class="nk-content-inner">
                            <div class="nk-content-body">
                                @if ( @$breadcrumb )
                                <nav class="mb-1">
                                    <ul class="breadcrumb">
                                        @foreach ( $breadcrumb as $bc )
                                        <li class="breadcrumb-item {{ $bc['class'] }}">
                                            @if ( $bc['url'] )
                                            <a href="{{ $bc['url'] }}">{{ $bc['text'] }}</a>
                                            @else
                                            {{ $bc['text'] }}
                                            @endif
                                        </li>
                                        @endforeach
                                    </ul>
                                </nav>
                                @endif
                                <?php echo view( $content, [ 'data' => @$data ] );?>
                            </div>
                        </div>
                    </div>
                </div>
                <form id="logoutForm" action="{{ route( 'admin.logout' ) }}" method="POST">
                    @csrf
                </form>
                <!-- content @e -->
                <?php echo view( 'admin/footer' );?>

            </div>
            <!-- wrap @e -->
        </div>
        <!-- main @e -->
    </div>

    <x-modal-warning />
    <x-modal-success />
    <x-modal-danger />

    <!-- app-root @e -->
    <?php echo view( 'admin/script' );?>
</body>
</html>