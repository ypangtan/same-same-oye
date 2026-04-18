<?php
$trending_content_edit = 'trending_content_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.trending_contents' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $trending_content_edit }}_title" class="col-sm-5 col-form-label">{{ __( 'trending_content.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $trending_content_edit }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $trending_content_edit }}_desc" class="col-sm-5 col-form-label">{{ __( 'trending_content.desc' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $trending_content_edit }}_desc">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $trending_content_edit }}_upload_type" class="col-sm-5 col-form-label">{{ __( 'trending_content.upload_type' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <select class="form-select" id="{{ $trending_content_edit }}_upload_type">
                                <option value="1" selected>{{ __( 'trending_content.upload_file' ) }}</option>
                                <option value="2">{{ __( 'trending_content.upload_url' ) }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row trending_content-file">
                    <label>{{ __( 'trending_content.song' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $trending_content_edit }}_file" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row d-none trending_content-url">
                    <label for="{{ $trending_content_edit }}_url" class="col-sm-5 col-form-label">{{ __( 'trending_content.url' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input type="text" class="form-control" id="{{ $trending_content_edit }}_url">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'trending_content.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $trending_content_edit }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="text-end">
                    <button id="{{ $trending_content_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $trending_content_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
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
window.cke_element = [ 'trending_content_edit_desc'];
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multi.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let de = '#{{ $trending_content_edit }}',
            fileID = '',
            file2ID = '';

        $( de + '_upload_type' ).change( function() {
            let selectedType = $( this ).val();

            if ( selectedType == 1 ) {
                $( '.trending_content-file' ).removeClass( 'd-none' );
                $( '.trending_content-url' ).addClass( 'd-none' );
            } else {
                $( '.trending_content-file' ).addClass( 'd-none' );
                $( '.trending_content-url' ).removeClass( 'd-none' );
            }
        } );

        $( de + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.trending_content.index' ) }}';
        } );

        $( de + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'title', $( de + '_title' ).val() ?? '' );
            formData.append( 'desc', editors['trending_content_edit_desc'].getData() );
            formData.append( 'image', fileID ?? '' );
            formData.append( 'file', file2ID ?? '' );
            formData.append( 'url', $( de + '_url' ).val() ?? '' );
            formData.append( 'upload_type', $( de + '_upload_type' ).val() ?? '' );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.trending_content.updateTrendingContent' ) }}',
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
                            $( de + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        getTrendingContent();

        function getTrendingContent() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.trending_content.oneTrendingContent' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    $( de + '_title' ).val( response.title );
                    $( de + '_upload_type' ).val( response.upload_type ).trigger( 'change' );
                    $( de + '_url' ).val( response.url );
                    editors['trending_content_edit_desc'].setData( response.desc ?? '' );

                    fileID = response.image;

                    songPath = response.song_url;
                    file2ID = response.file;

                    imagePath = response.image_url;
                    const dropzone = new Dropzone( de + '_image', { 
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

                            if ( imagePath ) {
                                let myDropzone = this,
                                    mockFile = { name: 'Default', size: 12345, accepted: true };

                                myDropzone.emit("addedfile", mockFile);
                                myDropzone.emit("thumbnail", mockFile, imagePath);
                                myDropzone.emit("complete", mockFile);
                                myDropzone.files.push( mockFile );
                            }
                        },
                        removedfile: function( file ) {
                            fileID = '';
                            file.previewElement.remove();
                        },
                        success: function( file, response ) {
                            fileID = response.file;
                        }
                    } );

                    const dropzone2 = new Dropzone(de + '_file', { 
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

                            // ---------- Existing file ----------
                            if (songPath) {

                                file_name = song_file ?? 'Default.mp3';
                                let myDropzone = this,
                                    mockFile = { name: file_name, size: 1024, accepted: true };

                                myDropzone.files.push(mockFile);

                                myDropzone.displayExistingFile(
                                    mockFile, 
                                    "{{ asset('admin/image/song.png') }}"
                                );

                                mockFile._fileUrl = songPath;

                                setTimeout(() => {
                                    mockFile.previewElement.querySelector("[data-dz-name]").textContent = file_name;
                                    mockFile.previewElement.addEventListener("click", () => {
                                        window.open(songPath, "_blank");
                                    });
                                }, 50);
                            }
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

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
        
    } );
</script>