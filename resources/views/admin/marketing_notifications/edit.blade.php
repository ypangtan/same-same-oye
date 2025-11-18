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
$announcement_edit = 'announcement_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.marketing_notifications' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist" style="gap:20px;">
                        <button class="nav-link active" id="en_title-tab" data-bs-toggle="tab" data-bs-target="#en_title" type="button" role="tab" aria-controls="en_title" aria-selected="true"> English </button>
                        {{-- <button class="nav-link" id="zh_title-tab" data-bs-toggle="tab" data-bs-target="#zh_title" type="button" role="tab" aria-controls="zh_title" aria-selected="false">  中文 </button> --}}
                    </div>
                </nav>
                
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade pt-4 show active" id="en_title" role="tabpanel" aria-labelledby="en_title-tab">
                        <div class="mb-3 row">
                            <label for="{{ $announcement_edit }}_en_title" class="col-sm-4 col-form-label">{{ __( 'datatables.title' ) }} </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $announcement_edit }}_en_title">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $announcement_edit }}_en_content" class="col-sm-4 col-form-label">{{ __( 'announcement.content' ) }} </label>
                            <div class="col-sm-8">
                                <textarea class="form-control form-control-sm" id="{{ $announcement_edit }}_en_content" rows="10"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade pt-4" id="zh_title" role="tabpanel" aria-labelledby="zh_title-tab">
                        <div class="mb-3 row">
                            <label for="{{ $announcement_edit }}_zh_title" class="col-sm-4 col-form-label">{{ __( 'datatables.title' ) }} ( 中文 )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $announcement_edit }}_zh_title">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $announcement_edit }}_zh_content" class="col-sm-4 col-form-label">{{ __( 'announcement.content' ) }} ( 中文 )</label>
                            <div class="col-sm-8">
                                <textarea class="form-control form-control-sm" id="{{ $announcement_edit }}_zh_content" rows="10"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label class="mb-1">{{ __( 'announcement.image' ) }}</label>
                    <div class="dropzone" id="{{ $announcement_edit}}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $announcement_edit}}_type" class="col-sm-4 col-form-label">{{ __( 'datatables.type' ) }}</label>
                    <div class="col-sm-8">
                        <select class="form-control form-control-sm" id="{{ $announcement_edit}}_type">
                            <option value="2">{{ __( 'announcement.news' ) }}</option>
                            <option value="3">{{ __( 'announcement.event' ) }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $announcement_edit}}_url_slug" class="col-sm-4 col-form-label">{{ __( 'announcement.url_slug' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $announcement_edit}}_url_slug">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $announcement_edit }}_users" class="col-sm-4 col-form-label">{{ __( 'template.users' ) }}</label>
                    <div class="col-sm-8">
                        <input class="form-check-input" type="checkbox" id="{{ $announcement_edit }}_all_users">
                        <label class="form-check-label" for="{{ $announcement_edit }}_all_users">
                            <small>{{ __( 'announcement.all_users' ) }}</small>
                        </label>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $announcement_edit}}_cancel" type="button" class="btn btn-sm btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $announcement_edit}}_submit" type="button" class="btn btn-sm btn-success">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
window.ckeupload_path = '{{ route( 'admin.marketing_notifications.ckeUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
window.cke_element = [ 'announcement_edit_en_content', 'announcement_edit_zh_content' ];
</script>

<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multi.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let ae = '#{{ $announcement_edit }}',
            fileID = '';

        $( ae + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.marketing_notifications.index' ) }}';
        } );

        $( ae + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );
            
            var all_users = $( ae + '_all_users' ).is( ":checked" ) ? 1 : 0 ;

            let formData = new FormData();
            
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'image', fileID );
            formData.append( 'en_title', $( ae + '_en_title' ).val() );
            formData.append( 'zh_title', $( ae + '_zh_title' ).val() );
            formData.append( 'url_slug', $( ae + '_url_slug' ).val() );
            formData.append( 'en_content', editors['announcement_edit_en_content'].getData() );
            formData.append( 'zh_content', editors['announcement_edit_zh_content'].getData() );
            formData.append( 'type', $( ae + '_type' ).val() );
            formData.append( 'all_users', all_users );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.marketing_notifications.updateMarketingNotification' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.marketing_notifications.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( ae + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();       
                    }
                }
            } );
        } );

        getAnnouncement();

        function getAnnouncement() {

            Dropzone.autoDiscover = false;

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.marketing_notifications.oneMarketingNotification' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( ae + '_url_slug' ).val( response.url_slug );
                    $( ae + '_en_title' ).val( response.en_title ?? response.title.en );
                    $( ae + '_zh_title' ).val( response.zh_title ?? response.title.zh );
                    editors['announcement_edit_en_content'].setData( response.en_content ?? ( response.content.en ?? '' ) );
                    editors['announcement_edit_zh_content'].setData( response.zh_content ?? ( response.content.zh ?? '' ) );
                    $( ae + '_type' ).val( response.type ).change();
                    $( ae + '_all_users' ).prop( "checked", response.is_broadcast == 10 ? true : false );

                    fileID = response.path;

                    let imagePath = response.path;

                    const dropzone = new Dropzone( ae + '_image', {
                        url: '{{ route( 'admin.file.upload' ) }}',
                        maxFiles: 1,
                        acceptedFiles: 'image/jpg,image/jpeg,image/png',
                        addRemoveLinks: true,
                        init: function() {
                            if ( imagePath ) {
                                let myDropzone = this,
                                    mockFile = { name: 'Default', size: 1024, accepted: true };

                                myDropzone.files.push( mockFile );
                                myDropzone.displayExistingFile( mockFile, imagePath );
                            }
                        },
                        removedfile: function( file, b ) {
                            fileID = null;
                            file.previewElement.remove();
                        },
                        success: function( file, response ) {
                            if ( response.status == 200 )  {
                                fileID = response.data.id;
                            }
                        }
                    } );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

    } );
</script>