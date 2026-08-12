<body class="nk-body npc-default pg-auth">
    <div class="nk-app-root">
        <div class="nk-main">
            <div class="nk-wrap nk-wrap-nosidebar">
                <div class="nk-content ">
                    <div class="nk-block nk-block-middle nk-auth-body  wide-xs">
                        <div class="card first-mfa text-center">
                            <div class="card-body">
                                <h5 class="card-title mb-3">{{ __( 'setting.first_mfa_title' ) }}</h5>
                                <div class="" id="setup_mfa">
                                    <div class="mb-3">
                                        {{ __( 'setting.first_mfa_subtitle' ) }}
                                    </div>
                                    <div class="mb-3">
                                        <strong>{{ __( 'setting.first_mfa_step_1' ) }}</strong>
                                    </div>
                                    <div class="mb-3">
                                        @if ( str_contains( $data['mfa_qr'], 'data:image/png' ) )
                                        <img src="<?=$data['mfa_qr'];?>" alt="QR" />
                                        @else
                                        <?=$data['mfa_qr'];?>
                                        @endif
                                    </div>
                                    <hr>
                                    <div class="mb-3">
                                        <strong>{{ __( 'setting.first_mfa_step_2' ) }}</strong>
                                    </div>
                                    <div class="mb-3">
                                        <input type="text" class="form-control" id="mfa_authentication_code" value="" />
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="">
                                        <button type="button" id="mfa_logout" class="btn btn-outline-secondary">{{ __( 'template.sign_out' ) }}</button>
                                        &nbsp;
                                        <button type="button" id="mfa_save" class="btn btn-primary">{{ __( 'template.confirm' ) }}</button>
                                    </div>
                                </div>
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
                                        <p class="text-soft">&copy; {{ date( 'Y' ) }} Settlelaah. All Rights Reserved.</p>
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

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let mfaSecret = '{{ $data['mfa_secret'] }}';

        $( '#mfa_save' ).click( function() {
            
            let data = {
                authentication_code: $( '#mfa_authentication_code' ).val(),
                mfa_secret: mfaSecret,
                _token: '{{ csrf_token() }}',
            }, that = $( this );

            $.ajax( {
                url: '{{ route( 'admin.setupMFA' ) }}',
                type: 'POST',
                data: data,
                success: function( response ) {
                    
                    $( '#mfa_authentication_code' ).val( '' ).removeClass( 'is-invalid' ).next().text( '' );

                    // $( '#toast .toast-body' ).text( '{{ __( 'setting.mfa_setup_complete' ) }}' );
                    // $( that ).removeClass( 'disabled' ).html( '{{ __( 'template.submit' ) }}' );
                    // toast.show();

                    that.addClass( 'disabled' );

                    setTimeout(function(){
                        window.location.href = '{{ route( 'admin.module_parent.dashboard' ) }}';
                    }, 2000 );

                },
                error: function( error ) {

                    console.log( error );
                    
                    if( error.status === 422 ) {

                        let errors = error.responseJSON.errors;

                        $.each( errors, function( key, value ) {
                            $( '#mfa_' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    }

                    $( that ).removeClass( 'disabled' ).html( '{{ __( 'template.submit' ) }}' );
                }
            } );
        } );

        $( '#mfa_logout' ).click( function() {
            $.ajax( {
                url: '{{ route( 'admin.signout' ) }}',
                type: 'POST',
                data: { '_token': '{{ csrf_token() }}' },
                success: function() {
                    document.getElementById( 'logoutForm' ).submit();
                }
            } );
        } );
    } );
</script>