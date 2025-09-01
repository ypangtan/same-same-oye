<?php
$upload_pn = 'upload';
?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 row">
                    <div class="mb-2">{!! __( 'lucky_draw_reward.upload_desc_1', [ 'url' => asset( 'admin/template/Template.xlsm' ) ] ) !!}</div>
                </div>
                <hr>
                <div class="mb-3 row">
                    <label for="{{ $upload_pn }}_file" class="col-sm-5 col-form-label">{{ __( 'lucky_draw_reward.select_file' ) }}</label>
                    <div class="col-sm-7">
                        <input type="file" class="form-control form-control-sm" id="{{ $upload_pn }}_file">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $upload_pn }}_cancel" type="button" class="btn btn-sm btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    <button id="{{ $upload_pn }}_submit" type="button" class="btn btn-sm btn-primary">{{ __( 'template.upload' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let upn = '#{{ $upload_pn }}';

        $( upn + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.lucky_draw_reward.index' ) }}';
        } );

        $( upn + '_submit' ).on( 'click', function() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'file', $( upn + '_file' )[0].files.length === 0 ? '' : $( upn + '_file' )[0].files[0] );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.lucky_draw_reward.importLuckyDrawReward' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );

                    if ( response.errors ) {
                        $( '#modal_warning .caption-text' ).html( response.message );

                        let errorText = '<br><strong>Errors:</strong>';

                        let errors = response.errors;

                        errors.map( function( v, i ) {
                            errorText += ( '<div>' + v + '</div>' );
                        } );

                        $( '#modal_warning .caption-text2' ).html( errorText );
                        $( '#modal_warning .btn_warning' ).html( '{{ __( 'template.replace_it' ) }}' );

                        modalWarning.toggle();

                        document.getElementById( 'btn_warning' ).addEventListener( 'click', function (event) {
                        
                            $.ajax( {
                                url: '{{ route( 'admin.lucky_draw_reward.importLuckyDrawRewardV2' ) }}',
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function( response ) {
                                    $( 'body' ).loading( 'stop' );

                                    if ( response.errors ) {
                                        $( '#modal_warning .caption-text' ).html( response.message );

                                        let errorText = '<br><strong>Errors:</strong>';

                                        let errors = response.errors;

                                        errors.map( function( v, i ) {
                                            errorText += ( '<div>' + v + '</div>' );
                                        } );

                                        $( '#modal_warning .caption-text2' ).html( errorText );
                                        $( '#modal_warning .btn_warning' ).html( '{{ __( 'template.ok' ) }}' );

                                        modalWarning.toggle();

                                        document.getElementById( 'modal_warning' ).addEventListener( 'hidden.bs.modal', function (event) {
                                            window.location.href = '{{ route( 'admin.module_parent.lucky_draw_reward.index' ) }}';
                                        } );
                                    } else {
                                        $( '#modal_success .caption-text' ).html( response.message );
                                        modalSuccess.toggle();

                                        document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                                            window.location.href = '{{ route( 'admin.module_parent.lucky_draw_reward.index' ) }}';
                                        } );
                                    }
                                },
                                error: function( error ) {
                                    $( 'body' ).loading( 'stop' );

                                    if ( error.status === 422 ) {
                                        let errors = error.responseJSON.errors;
                                        $.each( errors, function( key, value ) {
                                            $( upn + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                                        } );
                                    } else {
                                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                                        modalDanger.toggle();       
                                    }
                                }
                            } );
                        } );
                    } else {
                        $( '#modal_success .caption-text' ).html( response.message );
                        modalSuccess.toggle();

                        document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                            window.location.href = '{{ route( 'admin.module_parent.lucky_draw_reward.index' ) }}';
                        } );
                    }
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( upn + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();       
                    }
                }
            } );
        } );
    } );
</script>