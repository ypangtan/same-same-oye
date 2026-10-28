<?php
$festive_animation_create = 'festive_animation_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.festive_animations' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $festive_animation_create }}_label" class="col-sm-4 col-form-label">{{ __( 'festive_animation.label' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $festive_animation_create }}_label">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label>{{ __( 'festive_animation.file' ) }}</label>
                    <div class="form-note mb-2">{{ __( 'festive_animation.file_hint' ) }}</div>
                    <div class="dropzone mb-3" id="{{ $festive_animation_create }}_file" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="text-warning small mt-n2 mb-2" id="{{ $festive_animation_create }}_duration_reminder" style="display: none;">{{ __( 'festive_animation.short_duration_warning', [ 'duration' => \App\Models\FestiveAnimation::IMAGE_DURATION ] ) }}</div>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="text-end">
                    <button id="{{ $festive_animation_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $festive_animation_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $festive_animation_create }}',
            fileID = '',
            fileName = '',
            fileDuration = '';

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.festive_animation.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'label', $( fc + '_label' ).val() );
            formData.append( 'file', fileID ?? '' );
            formData.append( 'file_name', fileName ?? '' );
            formData.append( 'duration', fileDuration ?? '' );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.festive_animation.createFestiveAnimation' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.festive_animation.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( fc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( fc + '_file', {
            url: '{{ route( 'admin.festive_animation.fileUpload' ) }}',
            params: {
                '_token': '{{ csrf_token() }}',
            },
            maxFiles: 1,
            maxFilesize: null,
            acceptedFiles: 'image/*,video/*',
            timeout: 0,
            addRemoveLinks: true,
            init: function() {
                this.on( 'addedfile', function( file ) {
                    if ( this.files.length > 1 ) {
                        this.removeFile( this.files[0] );
                    }
                } );
                this.on( 'sending', function( file ) {
                    $( 'body' ).loading( {
                        message: '{{ __( 'template.loading' ) }}'
                    } );
                } );
                this.on( 'complete', function( file ) {
                    $( 'body' ).loading( 'stop' );
                } );
            },
            removedfile: function( file ) {
                fileID = null;
                fileName = null;
                fileDuration = null;
                $( fc + '_duration_reminder' ).hide();
                file.previewElement.remove();
            },
            success: function( file, response ) {
                fileID = response.file;
                fileName = response.file_name;
                fileDuration = response.duration;

                // GIF / video shorter than minimum duration will be replayed
                $( fc + '_duration_reminder' ).toggle( [ 2, 3 ].includes( parseInt( response.file_type ) ) && response.duration != null && parseFloat( response.duration ) < {{ \App\Models\FestiveAnimation::IMAGE_DURATION }} );
            },
            error: function( file, message ) {
                $( '#modal_danger .caption-text' ).html( message.message ?? message );
                modalDanger.toggle();
                this.removeFile( file );
            }
        } );
    } );
</script>
