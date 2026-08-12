<body class="nk-body npc-default pg-auth">
    <div class="nk-app-root">
        <div class="nk-main">
            <div class="nk-wrap nk-wrap-nosidebar">
                <div class="nk-content ">
                    <div class="nk-block nk-block-middle nk-auth-body  wide-xs">
                        <div class="card verify-mfa">
                            <div class="card-body">
                                <h5 class="card-title mb-3">{{ __( 'setting.verify_mfa_title' ) }}</h5>
                                <div class="mb-3">
                                    {{ __( 'setting.verify_mfa_subtitle' ) }}
                                </div>
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="authentication_code" value="" />
                                    <div class="invalid-feedback"></div>
                                </div>    
                                <div class="">
                                    <button type="button" id="logout" class="btn btn-outline-secondary">{{ __( 'template.sign_out' ) }}</button>
                                    &nbsp;
                                    <button type="button" id="submit" class="btn btn-primary">{{ __( 'template.confirm' ) }}</button>                                    
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

        $( '#submit' ).click( function() {

            $.ajax( {
                url: '{{ route( 'admin.verifyCode' ) }}',
                type: 'POST',
                data: {
                    authentication_code: $( '#authentication_code' ).val(),
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    console.log( response );

                    if( response.status ) {
                        window.location.href = '{{ route( 'admin.module_parent.dashboard' ) }}';
                    }
                },
                error: function( error ) {

                    console.log( error );

                    if( error.status === 422 ) {

                        let errors = error.responseJSON.errors;

                        $.each( errors, function( key, value ) {
                            $( '#' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    }
                }
            } );
        } );

        $( '#logout' ).click( function() {
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