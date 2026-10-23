<?php
$festive_animation_edit = 'festive_animation_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.festive_animations' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $festive_animation_edit }}_label" class="col-sm-4 col-form-label">{{ __( 'festive_animation.label' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $festive_animation_edit }}_label">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label>{{ __( 'festive_animation.file' ) }}</label>
                    <div class="form-note mb-2">{{ __( 'festive_animation.file_hint' ) }}</div>
                    <div class="dropzone mb-3" id="{{ $festive_animation_edit }}_file" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="text-end">
                    <button id="{{ $festive_animation_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $festive_animation_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $festive_animation_edit }}',
            fileID = '',
            fileName = '',
            fileDuration = '';

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.festive_animation.index' ) }}';
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'label', $( fe + '_label' ).val() );
            formData.append( 'file', fileID ?? '' );
            formData.append( 'file_name', fileName ?? '' );
            formData.append( 'duration', fileDuration ?? '' );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.festive_animation.updateFestiveAnimation' ) }}',
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
                            $( fe + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        getFestiveAnimation();

        function getFestiveAnimation() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.festive_animation.oneFestiveAnimation' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( fe + '_label' ).val( response.label ?? '' );

                    fileID = response.file ?? '';
                    fileName = response.file_name ?? '';
                    fileDuration = response.duration ?? '';
                    let filePath = response.file_path ?? '';

                    const dropzone = new Dropzone( fe + '_file', {
                        url: '{{ route( 'admin.festive_animation.fileUpload' ) }}',
                        params: {
                            '_token': '{{ csrf_token() }}',
                        },
                        maxFiles: 1,
                        maxFilesize: null,
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
                            if ( filePath ) {
                                let myDropzone = this,
                                    mockFile = { name: fileName || 'Default', size: 1024, accepted: true };

                                myDropzone.files.push( mockFile );
                                // 不要使用 displayExistingFile，手动创建预览
                                myDropzone.emit( 'addedfile', mockFile );
                                myDropzone.emit( 'complete', mockFile );

                                // 手动设置缩略图，直接使用图片 URL
                                if ( [ 1, 2 ].includes( parseInt( response.file_type ) ) && mockFile.previewElement ) {
                                    let img = mockFile.previewElement.querySelector( '[data-dz-thumbnail]' );
                                    if ( img ) {
                                        img.src = filePath;
                                        img.style.width = '100%';
                                        img.style.height = '100%';
                                        img.style.objectFit = 'cover';
                                    }
                                }

                                // 视频：用 video 取代缩略图，显示第一帧
                                if ( parseInt( response.file_type ) == 3 && mockFile.previewElement ) {
                                    let img = mockFile.previewElement.querySelector( '[data-dz-thumbnail]' );
                                    if ( img ) {
                                        let video = document.createElement( 'video' );
                                        video.src = filePath;
                                        video.muted = true;
                                        video.playsInline = true;
                                        video.preload = 'metadata';
                                        video.style.width = '100%';
                                        video.style.height = '100%';
                                        video.style.objectFit = 'cover';
                                        img.replaceWith( video );
                                    }
                                }
                            }
                        },
                        removedfile: function( file ) {
                            fileID = null;
                            fileName = null;
                            fileDuration = null;
                            file.previewElement.remove();
                        },
                        success: function( file, response ) {
                            fileID = response.file;
                            fileName = response.file_name;
                            fileDuration = response.duration;
                        },
                        error: function( file, message ) {
                            $( '#modal_danger .caption-text' ).html( message.message ?? message );
                            modalDanger.toggle();
                            this.removeFile( file );
                        }
                    } );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
    } );
</script>
