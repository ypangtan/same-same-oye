<style>
    .ck-content ul {
      list-style-type: disc;
      margin-left: 20px;
    }

    .ck-content ol {
      list-style-type: decimal;
      margin-left: 20px;
    }

    .ck-content ul li,
    .ck-content ol li {
      display: list-item;
    }

    .ck-editor__editable_inline {
      min-height: 300px;
    }
</style>
<?php
$voucher_notification_create = 'voucher_notification_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.subscription_voucher_notifications' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-body">
        <div class="alert alert-info">{{ __( 'subscription_voucher.content_hint' ) }}<br>{{ __( 'subscription_voucher.url_content_hint' ) }}</div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $voucher_notification_create }}_en_title" class="col-sm-4 col-form-label">{{ __( 'datatables.title' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $voucher_notification_create }}_en_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $voucher_notification_create }}_en_content" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.content' ) }}</label>
                    <div class="col-sm-8">
                        <textarea class="form-control form-control-sm" id="{{ $voucher_notification_create }}_en_content" rows="10"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $voucher_notification_create }}_subscription_voucher" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.voucher' ) }}</label>
                    <div class="col-sm-8">
                        <select class="form-select form-select-sm" id="{{ $voucher_notification_create }}_subscription_voucher">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'subscription_voucher.voucher' ) ] ) }}</option>
                            @foreach ( $data['vouchers'] as $voucher )
                            @if ( $voucher->isThirdParty() )
                            <option value="{{ $voucher->encrypted_id }}">{{ $voucher->name }} ( {{ __( 'subscription_voucher.type_third_party' ) }} )</option>
                            @else
                            <option value="{{ $voucher->encrypted_id }}">{{ $voucher->name }} ( {{ $voucher->duration_in_months }} {{ __( 'subscription_voucher.months' ) }}, {{ __( 'subscription_voucher.remaining' ) }}: {{ max( 0, $voucher->quantity - $voucher->codes_count ) }} )</option>
                            @endif
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="mb-1">{{ __( 'subscription_voucher.image' ) }}</label>
                    <div class="dropzone" id="{{ $voucher_notification_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $voucher_notification_create }}_users" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.recipients' ) }}</label>
                    <div class="col-sm-8">
                        <select class="form-select form-select-sm" multiple="multiple" id="{{ $voucher_notification_create }}_users" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.users' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                        <div class="form-note">{{ __( 'subscription_voucher.select_free_users' ) }}</div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $voucher_notification_create }}_publishing_date" class="col-sm-4 col-form-label">{{ __( 'template.publishing_date' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $voucher_notification_create }}_publishing_date" placeholder="{{ __( 'template.publishing_date_placeholder' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $voucher_notification_create }}_cancel" type="button" class="btn btn-sm btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $voucher_notification_create }}_submit" type="button" class="btn btn-sm btn-success">{{ __( 'template.save_changes' ) }}</button>
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
window.cke_element = [ 'voucher_notification_create_en_content' ];
</script>

<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multi.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let vnc = '#{{ $voucher_notification_create }}',
            fileID = '';

        flatpickr( vnc + '_publishing_date', {
            dateFormat: 'Y-m-d H:i',
            enableTime: true,
            time_24hr: true,
            disableMobile: true,
            allowInput: true,
        } );

        $( vnc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.subscription_voucher_notification.index' ) }}';
        } );

        $( vnc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'subscription_voucher', $( vnc + '_subscription_voucher' ).val() );
            formData.append( 'en_title', $( vnc + '_en_title' ).val() );
            formData.append( 'en_content', editors['voucher_notification_create_en_content'].getData() );
            formData.append( 'users', $( vnc + '_users' ).val() );
            formData.append( 'publishing_date', $( vnc + '_publishing_date' ).val() ?? '' );
            formData.append( 'image', fileID ?? '' );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.subscription_voucher_notification.createVoucherNotification' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function ( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.subscription_voucher_notification.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( vnc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( vnc + '_image', {
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            init: function() {
                this.on( 'addedfile', function( file ) {
                    if ( this.files.length > 1 ) {
                        this.removeFile( this.files[0] );
                    }
                } );
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

        $( vnc + '_users' ).select2( {
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: $( vnc + '_users' ).data( 'placeholder' ),
            allowClear: false,
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.user.allUsers' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        mixed_search: params.term,
                        membership: 0, // Only free user can claim
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
    } );
</script>
