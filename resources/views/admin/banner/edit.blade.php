<?php
$banner_edit = 'banner_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.banners' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist" style="gap:20px;">
                        <button class="nav-link active" id="en_name-tab" data-bs-toggle="tab" data-bs-target="#en_name" type="button" role="tab" aria-controls="en_name" aria-selected="true"> English </button>
                        <button class="nav-link" id="zh_name-tab" data-bs-toggle="tab" data-bs-target="#zh_name" type="button" role="tab" aria-controls="zh_name" aria-selected="false">  中文 </button>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade pt-4 show active" id="en_name" role="tabpanel" aria-labelledby="en_name-tab">
                        <div class="mb-3 row">
                            <label for="{{ $banner_edit }}_en_name" class="col-sm-4 col-form-label">{{ __( 'banner.name' ) }} ( English )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $banner_edit }}_en_name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $banner_edit }}_en_desc" class="col-sm-4 col-form-label">{{ __( 'banner.desc' ) }} ( English )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $banner_edit }}_en_desc">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade pt-4" id="zh_name" role="tabpanel" aria-labelledby="zh_name-tab">
                        <div class="mb-3 row">
                            <label for="{{ $banner_edit }}_zh_name" class="col-sm-4 col-form-label">{{ __( 'banner.name' ) }} ( 中文 )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $banner_edit }}_zh_name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $banner_edit }}_zh_desc" class="col-sm-4 col-form-label">{{ __( 'banner.desc' ) }} ( 中文 )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $banner_edit }}_zh_desc">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $banner_edit }}_category" class="col-sm-5 col-form-label">{{ __( 'banner.category' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-control select2" id="{{ $banner_edit }}_category" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'template.category' ) ] ) }}"></select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $banner_edit }}_membership_level" class="col-sm-5 col-form-label">{{ __( 'banner.min_membership_level' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $banner_edit }}_membership_level">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $banner_edit }}_priority" class="col-sm-5 col-form-label">{{ __( 'banner.priority' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $banner_edit }}_priority">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'collection.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $banner_edit }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="row mb-3">
                    <div>
                        <label for="{{ $banner_edit }}_banners" class="form-label" style="font-size:16px; font-weight:bold;">{{ __( 'banner.banners' ) }}</label>
                        <select class="form-select form-select-md" id="{{ $banner_edit }}_banners" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'template.banners' ) ] ) }}">></select>
                    </div>

                    <div id="selected-banners" class="d-flex flex-wrap gap-2 my-4"></div>

                    <input type="hidden" name="tags" id="{{ $banner_edit }}_hide_banners">
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

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
window.ckeupload_path = '{{ route( 'admin.banner.ckeUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
window.cke_element = [ 'banner_edit_en_name', 'banner_edit_zh_name', 'banner_edit_en_desc', 'banner_edit_zh_desc' ];
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multi.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let de = '#{{ $banner_edit }}',
            fileID = '';

        $( de + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.banner.index' ) }}';
        } );

        $( de + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'en_name', editors['banner_edit_en_name'].getData()  );
            formData.append( 'zh_name', editors['banner_edit_zh_name'].getData() );
            formData.append( 'en_desc', editors['banner_edit_en_desc'].getData()  );
            formData.append( 'zh_desc', editors['banner_edit_zh_desc'].getData() );
            formData.append( 'image', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.banner.updateBanner' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
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

        Dropzone.autoDiscover = false;
        getBanner();

        function getBanner() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.banner.oneBanner' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    editors['banner_edit_en_name'].setData( response.en_name ?? '' );
                    editors['banner_edit_zh_name'].setData( response.zh_name ?? '' );
                    editors['banner_edit_en_desc'].setData( response.en_desc ?? '' );
                    editors['banner_edit_zh_desc'].setData( response.zh_desc ?? '' );

                    imagePath = response.image_url;
                    fileID = response.image_url;

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
                        },
                        removedfile: function( file ) {
                            fileID = null;
                            file.previewElement.remove();
                        },
                        success: function( file, response ) {
                            fileID = response.file;
                        }
                    } );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
    } );
</script>