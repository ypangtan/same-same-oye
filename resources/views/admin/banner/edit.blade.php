<?php $banner_edit = 'banner_edit'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.banners' ) ) ] ) }}</h3>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

                <div class="mb-3">
                    <label>{{ __( 'banner.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $banner_edit }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="{{ $banner_edit }}_url">{{ __( 'banner.banner_url' ) }}</label>
                    <input type="url" class="form-control" id="{{ $banner_edit }}_url" placeholder="https://example.com">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="{{ $banner_edit }}_publishing_date">{{ __( 'template.publishing_date' ) }}</label>
                    <input type="text" class="form-control" id="{{ $banner_edit }}_publishing_date" placeholder="{{ __( 'template.publishing_date_placeholder' ) }}">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="text-end">
                    <button id="{{ $banner_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $banner_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener( 'DOMContentLoaded', function() {

    let de = '#{{ $banner_edit }}';
    let bannerId = '{{ request( 'id' ) }}',
        fileID = '';


    $( de + '_cancel' ).click( function() {
        window.location.href = '{{ route( 'admin.module_parent.banner.index' ) }}';
    } );

    $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );
    Dropzone.autoDiscover = false;

    $.ajax( {
        url: '{{ route( 'admin.banner.oneBanner' ) }}',
        type: 'POST',
        data: { id: bannerId, _token: '{{ csrf_token() }}' },
        success: function( response ) {
            $( 'body' ).loading( 'stop' );

            $( de + '_url' ).val( response.url ?? '' );

            if ( response.publishing_date ) {
                $( de + '_publishing_date' ).val( response.publishing_date.substring( 0, 10 ) );
            }

            imagePath = response.image_url;
            fileID = response.image;

            const dropzone = new Dropzone( de + '_image', { 
                url: '{{ route( 'admin.banner.imageUpload' ) }}',
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
                            mockFile = { name: 'Default', size: 1024, accepted: true };

                        myDropzone.files.push( mockFile );
                        // 不要使用 displayExistingFile，手动创建预览
                        myDropzone.emit("addedfile", mockFile);
                        myDropzone.emit("complete", mockFile);
                        
                        // 手动设置缩略图，直接使用图片 URL
                        if (mockFile.previewElement) {
                            let img = mockFile.previewElement.querySelector("[data-dz-thumbnail]");
                            if (img) {
                                img.src = imagePath;
                                img.style.width = "100%";
                                img.style.height = "100%";
                                img.style.objectFit = "cover";
                            }
                        }
                    }
                },
                removedfile: function( file ) {
                    fileID = null;
                    file.previewElement.remove();
                },
                success: function( file, response ) {
                    fileID = response.file;
                }
            } );
        },
        error: function() {
            $( 'body' ).loading( 'stop' );
        }
    } );

    flatpickr( de + '_publishing_date', {
        dateFormat: 'Y-m-d',
        disableMobile: true,
        allowInput: true,
    } );

    $( de + '_submit' ).click( function() {

        $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

        let formData = new FormData();
        formData.append( 'id', bannerId );
        formData.append( 'image', fileID ?? '' );
        formData.append( 'url', $( de + '_url' ).val() ?? '' );
        formData.append( 'publishing_date', $( de + '_publishing_date' ).val() ?? '' );
        formData.append( '_token', '{{ csrf_token() }}' );

        $.ajax( {
            url: '{{ route( 'admin.banner.updateBanner' ) }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function( response ) {
                $( 'body' ).loading( 'stop' );
                $( '#modal_success .caption-text' ).html( response.message );
                modalSuccess.toggle();
                document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function() {
                    window.location.href = '{{ route( 'admin.module_parent.banner.index' ) }}';
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

} );
</script>
