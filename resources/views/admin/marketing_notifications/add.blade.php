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
$announcement_create = 'announcement_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.marketing_notifications' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                {{-- <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist" style="gap:20px;">
                        <button class="nav-link active" id="en_title-tab" data-bs-toggle="tab" data-bs-target="#en_title" type="button" role="tab" aria-controls="en_title" aria-selected="true"> English </button>
                        <button class="nav-link" id="zh_title-tab" data-bs-toggle="tab" data-bs-target="#zh_title" type="button" role="tab" aria-controls="zh_title" aria-selected="false">  中文 </button>
                    </div>
                </nav> --}}
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade pt-4 show active" id="en_title" role="tabpanel" aria-labelledby="en_title-tab">
                        <div class="mb-3 row">
                            <label for="{{ $announcement_create }}_en_title" class="col-sm-4 col-form-label">{{ __( 'datatables.title' ) }} </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $announcement_create }}_en_title">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $announcement_create }}_en_content" class="col-sm-4 col-form-label">{{ __( 'announcement.content' ) }} </label>
                            <div class="col-sm-8">
                                <textarea class="form-control form-control-sm" id="{{ $announcement_create }}_en_content" rows="10"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade pt-4" id="zh_title" role="tabpanel" aria-labelledby="zh_title-tab">
                        <div class="mb-3 row">
                            <label for="{{ $announcement_create }}_zh_title" class="col-sm-4 col-form-label">{{ __( 'datatables.title' ) }} ( 中文 )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $announcement_create }}_zh_title">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $announcement_create }}_zh_content" class="col-sm-4 col-form-label">{{ __( 'announcement.content' ) }} ( 中文 )</label>
                            <div class="col-sm-8">
                                <textarea class="form-control form-control-sm" id="{{ $announcement_create }}_zh_content" rows="10"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label class="mb-1">{{ __( 'announcement.image' ) }}</label>
                    <div class="dropzone" id="{{ $announcement_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $announcement_create }}_type" class="col-sm-4 col-form-label">{{ __( 'datatables.type' ) }}</label>
                    <div class="col-sm-8">
                        <select class="form-control form-control-sm" id="{{ $announcement_create }}_type">
                            <option value="2">{{ __( 'announcement.news' ) }}</option>
                            <option value="3">{{ __( 'announcement.event' ) }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $announcement_create }}_url_slug" class="col-sm-4 col-form-label">{{ __( 'announcement.url_slug' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $announcement_create }}_url_slug">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $announcement_create }}_users" class="col-sm-4 col-form-label">{{ __( 'template.users' ) }}</label>
                    <div class="col-sm-8">
                        <select class="form-select form-select-sm" multiple="multiple" id="{{ $announcement_create }}_users" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.users' ) ] ) }}">
                        </select>
                        <input class="form-check-input" type="checkbox" id="{{ $announcement_create }}_all_users">
                        <label class="form-check-label" for="{{ $announcement_create }}_all_users">
                            <small>{{ __( 'announcement.all_users' ) }}</small>
                        </label>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $announcement_create }}_cancel" type="button" class="btn btn-sm btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $announcement_create }}_submit" type="button" class="btn btn-sm btn-success">{{ __( 'template.save_changes' ) }}</button>
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
window.cke_element = [ 'announcement_create_en_content', 'announcement_create_zh_content' ];
</script>

<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multi.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {
        
        let ac = '#announcement_create',
            fileID = '';

        $( ac + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.marketing_notifications.index' ) }}';
        } );

        $( ac + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            var all_users = $( ac + '_all_users' ).is( ":checked" ) ? 1 : 0 ;

            let formData = new FormData();

            formData.append( 'type', $( ac + '_type' ).val() );
            formData.append( 'en_title', $( ac + '_en_title' ).val() );
            formData.append( 'zh_title', $( ac + '_zh_title' ).val() );
            formData.append( 'en_content', editors['announcement_create_en_content'].getData() );
            formData.append( 'zh_content', editors['announcement_create_zh_content'].getData() );
            formData.append( 'users', $( ac + '_users' ).val() );
            formData.append( 'url_slug', $( ac + '_url_slug' ).val() );
            formData.append( 'all_users', all_users );
            formData.append( 'image', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.marketing_notifications.createMarketingNotification' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function ( response ) {
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
                            $( ac + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();       
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( ac + '_image', { 
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            inti: function() {
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
                if ( response.status == 200 )  {
                    fileID = response.data.id;
                }
            }
        } );

        $( ac + '_users' ).select2( {
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            allowClear: false,
            closeOnSelect: true,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.user.allUsers' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        mixed_search: params.term, // search term
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.users.map( function( v, i ) {
                        processedResult.push( {
                            id: v.encrypted_id,
                            text: ( v.calling_code ? v.calling_code : '+60' ) + ( v.phone_number ? v.phone_number : '-' ) + ' (' + ( v.email ? v.email : '-' ) + ')',
                        } );
                    } );

                    return {
                        results: processedResult,
                        pagination: {
                            more: ( params.page * 10 ) < data.recordsFiltered
                        }
                    };
                }
            }
        } );

        $( ac + "_all_users" ).change( function() {
            if ( $( this ).is( ":checked" ) ) {
                $( this ).siblings( ".select2" ).hide()
            } else {
                $( this ).siblings( ".select2" ).show()
            }
        });

        $( ac + "_users" ).on('select2:select', function (e) {
            $( ac + "_all_users" ).hide()
            $( ac + "_all_users" ).siblings( ".form-check-label" ).hide()
        });

        $( ac + "_users" ).on('select2:unselect', function (e) {
            $( ac + "_all_users" ).show()
            $( ac + "_all_users" ).siblings( ".form-check-label" ).show()
        });

    } );
</script>