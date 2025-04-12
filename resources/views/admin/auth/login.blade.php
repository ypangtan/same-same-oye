<body class="nk-body npc-default pg-auth">
    <div class="nk-app-root">
        <div class="nk-main">
            <div class="nk-wrap nk-wrap-nosidebar">
                <div class="nk-content ">
                    <div class="nk-block nk-block-middle nk-auth-body  wide-xs">
                        <div class="brand-logo pb-4 text-center">
                            <a href="{{ route( 'admin.home' ) }}" class="logo-link">
                                <img src="{{ asset( 'admin/images/logo.png' ) . Helper::assetVersion() }}" width="100%" />
                            </a>
                        </div>
                        <div class="card" style="border-radius: 10px;">
                            <div class="card-inner card-inner-lg">
                                <div class="nk-block-head">
                                    <div class="nk-block-head-content">
                                        <h4 class="nk-block-title">{{ __( 'auth.sign_in_to' ) }}</h4>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route( 'admin.login' ) }}">
                                    @csrf
                                    <div class="form-group">
                                        <div class="form-label-group">
                                            <label class="form-label" for="email">{{ __( 'auth.credentials' ) }}</label>
                                        </div>
                                        <div class="form-control-wrap input-group">
                                            <input type="text" class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                            id="email" name="email" 
                                            placeholder="{{ __( 'auth.enter_your_x', [ 'type' => strtolower( __( 'auth.email' ) .' or '. __( 'auth.phone_number' ) ) ] ) }}"
                                            value="{{ old( 'email' ) ? old( 'email' ) : '' }}">
                                            @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form-label-group">
                                            <label class="form-label" for="password">{{ __( 'auth.password' ) }}</label>
                                        </div>
                                        <div class="form-control-wrap">
                                            <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="{{ __( 'auth.enter_your_x', [ 'type' => strtolower( __( 'auth.password' ) ) ] ) }}">
                                            <a href="#" class="form-icon form-icon-right passcode-switch lg" data-target="password">
                                                <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                                <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-lg btn-primary btn-block">{{ __( 'auth.continue' ) }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="nk-footer nk-auth-footer-full">
                        <div class="container wide-lg">
                            <div class="row g-3">
                                <div class="col-lg-6 order-lg-last">
                                    <ul class="nav nav-sm justify-content-center justify-content-lg-end">
                                        <li class="nav-item dropup">
                                            <a class="dropdown-toggle dropdown-indicator has-indicator nav-link" data-bs-toggle="dropdown" data-offset="0,10">
                                                <span>{{ Config::get( 'languages' )[App::getLocale()] }}</span>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-end">
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
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-lg-6">
                                    <div class="nk-block-content text-center text-lg-left">
                                        <p class="text-soft">&copy; {{ date( 'Y' ) . ' ' . config( 'app.name' ) }} All Rights Reserved.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset( 'admin/js/bundle.js' ) }}"></script>
    <script src="{{ asset( 'admin/js/scripts.js' ) }}"></script>
</body>