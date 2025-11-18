<style>
    .ck-content ul {
      list-style-type: disc;
      margin-left: 20px;
    }
    
    /* Style for numbered lists inside CKEditor */
    .ck-content ol {
      list-style-type: decimal;
      margin-left: 20px;
    }
    
    /* Ensure list items have correct display inside CKEditor */
    .ck-content ul li, 
    .ck-content ol li {
      display: list-item;
    }
    
    /* Apply a minimum height to the CKEditor editable area */
    .ck-editor__editable_inline {
      min-height: 400px;
    }
</style>
<?php
$pop_announcement_create = 'pop_announcement_create';
$discountTypes = $data['discount_types'];
$voucherTypes = $data['voucher_type'];
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.pop_announcements' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                
                <div class="mb-3">
                    <label>{{ __( 'pop_announcement.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $pop_announcement_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        {{-- <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist" style="gap:20px;">
                                <button class="nav-link active" id="en_title-tab" data-bs-toggle="tab" data-bs-target="#en_title" type="button" role="tab" aria-controls="en_title" aria-selected="true"> English </button>
                                <button class="nav-link" id="zh_title-tab" data-bs-toggle="tab" data-bs-target="#zh_title" type="button" role="tab" aria-controls="zh_title" aria-selected="false">  中文 </button>
                            </div>
                        </nav> --}}

                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade pt-4 show active" id="en_title" role="tabpanel" aria-labelledby="en_title-tab">
                                <div class="mb-3 row">
                                    <label for="{{ $pop_announcement_create }}_en_title" class="col-sm-5 col-form-label">{{ __( 'pop_announcement.title' ) }} </label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control" id="{{ $pop_announcement_create }}_en_title">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="{{ $pop_announcement_create }}_en_text" class="col-sm-5 col-form-label">{{ __( 'pop_announcement.text' ) }} </label>
                                    <div class="col-sm-7">
                                        <textarea class="form-control"  style="min-height: 80px;" id="{{ $pop_announcement_create }}_en_text"></textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade pt-4" id="zh_title" role="tabpanel" aria-labelledby="zh_title-tab">
                                <div class="mb-3 row">
                                    <label for="{{ $pop_announcement_create }}_zh_title" class="col-sm-5 col-form-label">{{ __( 'pop_announcement.title' ) }} ( 中文 )</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control" id="{{ $pop_announcement_create }}_zh_title">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="{{ $pop_announcement_create }}_zh_text" class="col-sm-5 col-form-label">{{ __( 'pop_announcement.text' ) }} ( 中文 )</label>
                                    <div class="col-sm-7">
                                        <textarea class="form-control"  style="min-height: 80px;" id="{{ $pop_announcement_create }}_zh_text"></textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $pop_announcement_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $pop_announcement_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
window.ckeupload_path = '{{ route( 'admin.pop_announcement.ckeUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
window.cke_element = [ 'pop_announcement_create_en_text', 'pop_announcement_create_zh_text' ];
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multi.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $pop_announcement_create }}',
            fileID = '';

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.pop_announcement.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'en_title', $( fc + '_en_title' ).val() );
            formData.append( 'zh_title', $( fc + '_zh_title' ).val() );
            formData.append( 'en_text', editors['pop_announcement_create_en_text'].getData() );
            formData.append( 'zh_text', editors['pop_announcement_create_zh_text'].getData() );
            formData.append( 'image', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.pop_announcement.createPopAnnouncement' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.pop_announcement.index' ) }}';
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
        const dropzone = new Dropzone( fc + '_image', { 
            url: '{{ route( 'admin.pop_announcement.imageUpload' ) }}',
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

    } );
</script>