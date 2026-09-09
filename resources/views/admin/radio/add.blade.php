<?php
$radio_create = 'radio_create';
$parent_route = route( 'admin.module_parent.radio.index' );
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.radio_queue' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $radio_create }}_title" class="col-sm-5 col-form-label">{{ __( 'radio.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $radio_create }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label>{{ __( 'radio.song' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $radio_create }}_file" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="text-end">
                    <button id="{{ $radio_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $radio_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let dc = '#{{ $radio_create }}',
            file2ID = '',
            song_file = '',
            duration = '';

        $( dc + '_cancel' ).click( function() {
            window.location.href = '{{ $parent_route }}';
        } );

        $( dc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.radio.createItem' ) }}',
                type: 'POST',
                data: {
                    title: $( dc + '_title' ).val() ?? '',
                    file: file2ID ?? '',
                    file_name: song_file ?? '',
                    duration: duration ?? '',
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ $parent_route }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( dc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( dc + '_file', {
            url: '{{ route("admin.radio.songUpload") }}',
            maxFiles: 1,
            acceptedFiles: 'audio/mpeg,audio/mp3',
            addRemoveLinks: true,
            previewTemplate: `
                <div class="dz-preview dz-file-preview" style="cursor:pointer;">
                    <img src="{{ asset('admin/images/song.png') }}"
                        style="width:120px;height:120px;object-fit:contain;">

                    <div class="dz-details" style="margin-top:5px;">
                        <div class="dz-filename"><span data-dz-name></span></div>
                        <div class="dz-size" data-dz-size></div>
                    </div>
                </div>
            `,
            init: function() {
                this.on("addedfile", function(file) {
                    if (this.files.length > 1) {
                        this.removeFile(this.files[0]);
                    }
                    file.previewElement.addEventListener("click", () => {
                        if (file._fileUrl) window.open(file._fileUrl, "_blank");
                    });
                });
                this.on("sending", function( file ) {
                    $( 'body' ).loading( {
                        message: '{{ __( 'template.loading' ) }}'
                    } );
                });
                this.on("complete", function( file ) {
                    $( 'body' ).loading( 'stop' );
                });
            },
            removedfile: function(file) {
                file2ID = "";
                song_file = "";
                duration = "";
                if (file.previewElement) file.previewElement.remove();
            },
            success: function(file, response) {
                file2ID = response.file;
                song_file = response.file_name ?? '';
                duration = response.duration ?? '';
                file._fileUrl = response.url;

                file.previewElement.addEventListener("click", () => {
                    window.open(response.url, "_blank");
                });
            }
        });

    } );
</script>
