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
$voucher_notification_edit = 'voucher_notification_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.subscription_voucher_notifications' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-body">
        <div class="alert alert-info">{{ __( 'subscription_voucher.content_hint' ) }}<br>{{ __( 'subscription_voucher.url_content_hint' ) }}<br>{{ __( 'subscription_voucher.content_not_editable_hint' ) }}</div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $voucher_notification_edit }}_en_title" class="col-sm-4 col-form-label">{{ __( 'datatables.title' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $voucher_notification_edit }}_en_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $voucher_notification_edit }}_en_content" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.content' ) }}</label>
                    <div class="col-sm-8">
                        <textarea class="form-control form-control-sm" id="{{ $voucher_notification_edit }}_en_content" rows="10"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.voucher' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $voucher_notification_edit }}_subscription_voucher" disabled>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="mb-1">{{ __( 'subscription_voucher.image' ) }}</label>
                    <div class="dropzone" id="{{ $voucher_notification_edit }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $voucher_notification_edit }}_publishing_date" class="col-sm-4 col-form-label">{{ __( 'template.publishing_date' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $voucher_notification_edit }}_publishing_date" placeholder="{{ __( 'template.publishing_date_placeholder' ) }}">
                        <div class="invalid-feedback"></div>
                        <div class="form-note" id="{{ $voucher_notification_edit }}_publishing_date_note" style="display: none;">{{ __( 'subscription_voucher.publishing_date_sent_hint' ) }}</div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $voucher_notification_edit }}_cancel" type="button" class="btn btn-sm btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $voucher_notification_edit }}_submit" type="button" class="btn btn-sm btn-success">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title mb-3">{{ __( 'subscription_voucher.recipients' ) }} <small class="text-soft" id="{{ $voucher_notification_edit }}_recipient_summary"></small></h5>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>{{ __( 'subscription_voucher.user' ) }}</th>
                        <th>{{ __( 'subscription_voucher.code' ) }} / {{ __( 'subscription_voucher.url' ) }}</th>
                        <th class="claim-col">{{ __( 'datatables.status' ) }}</th>
                        <th class="claim-col">{{ __( 'subscription_voucher.claimed_at' ) }}</th>
                    </tr>
                </thead>
                <tbody id="{{ $voucher_notification_edit }}_recipients"></tbody>
            </table>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
window.ckeupload_path = '{{ route( 'admin.marketing_notifications.ckeUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
window.cke_element = [ 'voucher_notification_edit_en_content' ];
</script>

<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multi.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let vne = '#{{ $voucher_notification_edit }}',
            fileID = '',
            codeStatusMapper = {
                '1': '<span class="badge rounded-pill bg-info">{{ __( 'subscription_voucher.available' ) }}</span>',
                '10': '<span class="badge rounded-pill bg-success">{{ __( 'subscription_voucher.claimed' ) }}</span>',
                '20': '<span class="badge rounded-pill bg-danger">{{ __( 'subscription_voucher.voided' ) }}</span>',
            };

        let publishingDatePicker = flatpickr( vne + '_publishing_date', {
            dateFormat: 'Y-m-d H:i',
            enableTime: true,
            time_24hr: true,
            disableMobile: true,
            allowInput: true,
        } );

        $( vne + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.subscription_voucher_notification.index' ) }}';
        } );

        $( vne + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'en_title', $( vne + '_en_title' ).val() );
            formData.append( 'en_content', editors['voucher_notification_edit_en_content'].getData() );
            formData.append( 'image', fileID ?? '' );
            formData.append( 'publishing_date', $( vne + '_publishing_date' ).val() ?? '' );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.subscription_voucher_notification.updateVoucherNotification' ) }}',
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
                            $( vne + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;

        // CKEditor init is async, wait for editors before loading data
        let waitEditors = setInterval( function() {
            if ( typeof editors !== 'undefined' && editors['voucher_notification_edit_en_content'] ) {
                clearInterval( waitEditors );
                getVoucherNotification();
            }
        }, 100 );

        function getVoucherNotification() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.subscription_voucher_notification.oneVoucherNotification' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( vne + '_en_title' ).val( response.en_title ?? '' );
                    editors['voucher_notification_edit_en_content'].setData( response.en_content ?? '' );

                    // Publishing date only editable before sent ( status 1: scheduled )
                    if ( response.publishing_date ) {
                        publishingDatePicker.setDate( response.publishing_date );
                    }
                    if ( response.status != 1 ) {
                        $( vne + '_publishing_date' ).prop( 'disabled', true );
                        $( vne + '_publishing_date_note' ).show();
                    }

                    let isThirdParty = response.subscription_voucher && response.subscription_voucher.type == {{ \App\Models\SubscriptionVoucher::TYPE_THIRD_PARTY }};

                    if ( response.subscription_voucher ) {
                        $( vne + '_subscription_voucher' ).val( response.subscription_voucher.name + ' ( ' + ( isThirdParty ? '{{ __( 'subscription_voucher.type_third_party' ) }}' : response.subscription_voucher.duration_in_months + ' {{ __( 'subscription_voucher.months' ) }}' ) + ' )' );
                    }

                    // Recipients with their own code & claim status ( third party: url only, no claim )
                    let codes = response.subscription_voucher_codes ?? [],
                        claimed = 0,
                        rows = '';

                    codes.forEach( function( code, i ) {
                        if ( code.status == 10 ) {
                            claimed++;
                        }
                        let user = code.user
                            ? ( code.user.calling_code ? code.user.calling_code : '+60' ) + ( code.user.phone_number ?? '-' ) + ' <small class="text-soft">(' + ( code.user.email ?? '-' ) + ')</small>'
                            : '-',
                            voucher = isThirdParty
                            ? ( code.url ? '<a href="' + $( '<div>' ).text( code.url.url ).html() + '" target="_blank" rel="noopener" style="word-break: break-all;">' + $( '<div>' ).text( code.url.url ).html() + '</a>' : '-' )
                            : '<span class="fw-bold" style="font-family: monospace;">' + code.code + '</span>';
                        rows += '<tr>'
                            + '<td>' + ( i + 1 ) + '</td>'
                            + '<td>' + user + '</td>'
                            + '<td>' + voucher + '</td>'
                            + ( isThirdParty ? '' : '<td>' + ( codeStatusMapper[code.status] ?? '-' ) + '</td>'
                            + '<td>' + ( code.claimed_at ?? '-' ) + '</td>' )
                            + '</tr>';
                    } );

                    $( vne + '_recipients' ).html( rows );
                    $( vne + '_recipients' ).closest( 'table' ).find( 'th.claim-col' ).toggle( !isThirdParty );
                    $( vne + '_recipient_summary' ).text( isThirdParty
                        ? '( {{ __( 'subscription_voucher.sent' ) }}: ' + codes.length + ' )'
                        : '( {{ __( 'subscription_voucher.claimed_count' ) }}: ' + claimed + ' / ' + codes.length + ' )' );

                    let imagePath = response.path ?? '';

                    new Dropzone( vne + '_image', {
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
                            if ( imagePath ) {
                                let myDropzone = this,
                                    mockFile = { name: 'Default', size: 1024, accepted: true };

                                myDropzone.files.push( mockFile );
                                myDropzone.displayExistingFile( mockFile, imagePath );
                            }
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

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
    } );
</script>
