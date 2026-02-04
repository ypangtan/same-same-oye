<?php
$setting = 'setting';
?>

<div class="card">
    <div class="card-body">
        <div class="row gy-3">
            <div class="col-md-2">                
                <div class="list-group" role="tablist">
                    <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#avs" role="tab">{{ __( 'setting.app_version_settings' ) }}</a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#cues" role="tab">{{ __( 'setting.contact_us_email_settings' ) }}</a>
                </div>
            </div>
            <div class="col-md-10">
                <div class="tab-content p-2">
                    <div class="tab-pane fade show active" id="avs" role="tabpanel">
                        <h5 class="card-title mb-0">{{ __( 'setting.app_version_settings' ) }}</h5>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="{{ $setting }}_force_logout">
                                        <label class="form-check-label" for="{{ $setting }}_force_logout">{{ __( 'setting.enable_force_logout' ) }}</label>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="{{ $setting }}_version" class="col-sm-5 col-form-label">{{ __( 'setting.app_version' ) }}</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_version">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button class="btn btn-sm btn-primary" id="app_version_save">{{ __( 'template.save_changes' ) }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="cues" role="tabpanel">
                        <h5 class="card-title mb-0">{{ __( 'setting.contact_us_email_settings' ) }}</h5>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3 row">
                                    <label for="{{ $setting }}_contact_us_email" class="col-sm-5 col-form-label">{{ __( 'setting.contact_us_email' ) }}</label>
                                    <div class="col-sm-7">
                                        <input type="email" class="form-control form-control-sm" id="{{ $setting }}_contact_us_email">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button class="btn btn-sm btn-primary" id="contact_us_email_save">{{ __( 'template.save_changes' ) }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        getAppVersionSettings();
        getSettings();

        let s = '#{{ $setting }}';

        $( '#contact_us_email_save' ).on( 'click', function() {

            resetInputValidation();

            $.ajax( {
                url: '{{ route( 'admin.setting.updateContactUsEmailSetting' ) }}',
                type: 'POST',
                data: {
                    contact_us_email: $( s + '_contact_us_email' ).val(),
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.show();
                },
                error: function( error ) {
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( s + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.show();       
                    }
                }
            } );
        } );

        $( '#app_version_save' ).on( 'click', function() {

            resetInputValidation();

            $.ajax( {
                url: '{{ route( 'admin.setting.updateAppVersionSetting' ) }}',
                type: 'POST',
                data: {
                    version: $( s + '_version' ).val(),
                    force_logout: $( s + '_force_logout' ).is( ':checked' ) ? 10 : 20,
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.show();
                },
                error: function( error ) {
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( s + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.show();       
                    }
                }
            } );
        } );

        function getAppVersionSettings() {

            $.ajax( {
                url: '{{ route( 'admin.setting.lastestAppVersion' ) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    if ( response.data ) {
                        $( s + '_version').val( response.data.version );
                        if ( response?.data.force_logout == 10 ) {
                            $( s + '_force_logout' ).prop('checked', true);
                        }
                    }
                },
            } );
        }
    } );
</script>

