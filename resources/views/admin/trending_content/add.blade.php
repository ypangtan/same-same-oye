<?php
$trending_content_create = 'trending_content_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.trending_contents' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $trending_content_create }}_title" class="col-sm-5 col-form-label">{{ __( 'trending_content.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $trending_content_create }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $trending_content_create }}_desc" class="col-sm-5 col-form-label">{{ __( 'trending_content.desc' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $trending_content_create }}_desc">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $trending_content_create }}_upload_type" class="col-sm-5 col-form-label">{{ __( 'trending_content.upload_type' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <select class="form-select" id="{{ $trending_content_create }}_upload_type">
                                <option value="1" selected>{{ __( 'trending_content.upload_file' ) }}</option>
                                <option value="2">{{ __( 'trending_content.upload_url' ) }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row trending_content-file">
                    <label>{{ __( 'trending_content.song' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $trending_content_create }}_file" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row d-none trending_content-url">
                    <label for="{{ $trending_content_create }}_url" class="col-sm-5 col-form-label">{{ __( 'trending_content.url' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input type="text" class="form-control" id="{{ $trending_content_create }}_url">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'trending_content.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $trending_content_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="text-end">
                    <button id="{{ $trending_content_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $trending_content_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
window.ckeupload_path = '{{ route( 'admin.trending_content.ckeUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
window.cke_element = [ 'trending_content_create_desc'];
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multi.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let dc = '#{{ $trending_content_create }}',
            fileID = '',
            file2ID = '',
            songPath = '';

        $( dc + '_upload_type' ).change( function() {
            let selectedType = $( this ).val();

            if ( selectedType == 1 ) {
                $( '.trending_content-file' ).removeClass( 'd-none' );
                $( '.trending_content-url' ).addClass( 'd-none' );
            } else {
                $( '.trending_content-file' ).addClass( 'd-none' );
                $( '.trending_content-url' ).removeClass( 'd-none' );
            }
        } );

        $( dc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.trending_content.index' ) }}';
        } );

        $( dc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'title', $( dc + '_title' ).val() ?? '' );
            formData.append( 'desc', editors['trending_content_create_desc'].getData() );
            formData.append( 'image', fileID ?? '' );
            formData.append( 'upload_type', $( dc + '_upload_type' ).val() ?? '' );
            formData.append( 'url', $( dc + '_url' ).val() ?? '' );
            formData.append( 'file', file2ID ?? '' );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.trending_content.createTrendingContent' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.trending_content.index' ) }}';
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
        const dropzone = new Dropzone( dc + '_image', { 
            url: '{{ route( 'admin.trending_content.imageUpload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            init: function() {
                this.on("addedfile", function (file) {
                    if (this.files.length > 1) {
                        this.removeFile(this.files[0]);
                    }
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
            removedfile: function( file ) {
                fileID = null;
                file.previewElement.remove();
            },
            success: function( file, response ) {
                fileID = response.file;
            }
        } );

        const dropzone2 = new Dropzone( dc + '_file', { 
            url: '{{ route("admin.trending_content.songUpload") }}',
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
                if (file.previewElement) file.previewElement.remove();
            },
            success: function(file, response) {
                file2ID = response.file;
                file._fileUrl = response.url;

                file.previewElement.addEventListener("click", () => {
                    window.open(response.url, "_blank");
                });
            }
        });

    } );
</script>