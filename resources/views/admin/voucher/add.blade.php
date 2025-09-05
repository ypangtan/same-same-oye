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
$voucher_create = 'voucher_create';
$discountTypes = $data['discount_types'];
$voucherTypes = $data['voucher_type'];
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.vouchers' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
        <div class="row">
            <div class="col-md-6">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist" style="gap:20px;">
                        <button class="nav-link active" id="en_title-tab" data-bs-toggle="tab" data-bs-target="#en_title" type="button" role="tab" aria-controls="en_title" aria-selected="true"> English </button>
                        <button class="nav-link" id="zh_title-tab" data-bs-toggle="tab" data-bs-target="#zh_title" type="button" role="tab" aria-controls="zh_title" aria-selected="false">  中文 </button>
                    </div>
                </nav>

                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade pt-4 show active" id="en_title" role="tabpanel" aria-labelledby="en_title-tab">
                        <div class="mb-3 row">
                            <label for="{{ $voucher_create }}_en_title" class="col-sm-5 col-form-label">{{ __( 'voucher.title' ) }} ( English )</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" id="{{ $voucher_create }}_en_title">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $voucher_create }}_en_description" class="col-sm-5 col-form-label">{{ __( 'voucher.description' ) }} ( English )</label>
                            <div class="col-sm-7">
                                <textarea class="form-control"  style="min-height: 80px;" id="{{ $voucher_create }}_en_description"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade pt-4" id="zh_title" role="tabpanel" aria-labelledby="zh_title-tab">
                        <div class="mb-3 row">
                            <label for="{{ $voucher_create }}_zh_title" class="col-sm-5 col-form-label">{{ __( 'voucher.title' ) }} ( 中文 )</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" id="{{ $voucher_create }}_zh_title">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $voucher_create }}_zh_description" class="col-sm-5 col-form-label">{{ __( 'voucher.description' ) }} ( 中文 )</label>
                            <div class="col-sm-7">
                                <textarea class="form-control"  style="min-height: 80px;" id="{{ $voucher_create }}_zh_description"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label>{{ __( 'voucher.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $voucher_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $voucher_create }}_voucher_type" class="col-sm-5 col-form-label">{{ __( 'voucher.voucher_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $voucher_create }}_voucher_type">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'voucher.voucher_type' ) ] ) }}</option>
                            @forEach( $voucherTypes as $key => $voucherType )
                                <option value="{{ $key }}">{{ $voucherType }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $voucher_create }}_discount_type" class="col-sm-5 col-form-label">{{ __( 'voucher.discount_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $voucher_create }}_discount_type">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'voucher.discount_type' ) ] ) }}</option>
                            @forEach( $discountTypes as $key => $discountType )
                                <option value="{{ $key }}">{{ $discountType }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <section id="bxgy" class="rule-section hidden mb-3 row">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3 row">
                                        <div class="col-sm-3">
                                            <h5>{{ __( 'voucher.buy' ) }}</h5>
                                            <small>{!!__( 'voucher.buy_description' )!!}</small>
                                        </div>
                                        <div class="col-sm-3">
                                            <select class="form-select form-select-sm" id="{{ $voucher_create }}_bxgy_buy_products" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'voucher.buy_products' ) ] ) }}" multiple>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3 row">
                                        <div class="col-sm-3">
                                            <h5>{{ __( 'voucher.discount' ) }}</h5>
                                            <small>{!!__( 'voucher.discount_description' )!!}</small>
                                        </div>
                                        <div class="col-sm">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-4">
                                                    <fieldset class="border p-2">
                                                        <legend class="mb-0 fs-6 float-none w-auto">{{ __( 'voucher.buy_quantity' ) }}</legend>
                                                        <input type="number" class="form-control form-control-sm" id="{{ $voucher_create }}_bxgy_buy_quantity">
                                                        <div class="invalid-feedback"></div>
                                                    </fieldset>
                                                </div>
                                                <div class="col-sm-12 col-md-8">
                                                    <fieldset class="border p-2">
                                                        <legend class="mb-0 fs-6 float-none w-auto">{{ __( 'voucher.get_quantity' ) }}</legend>
                                                        <div class="row">
                                                            <div class="col">
                                                                <input type="number" class="form-control form-control-sm" id="{{ $voucher_create }}_bxgy_get_quantity">
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                            <div class="col">
                                                                <select class="form-select form-select-sm" id="{{ $voucher_create }}_bxgy_get_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'voucher.get_product' ) ] ) }}">
                                                                </select>
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <section id="cartd" class="rule-section hidden mb-3 row">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3 row">
                                        <div class="col-sm-3">
                                            <h5>{{ __( 'voucher.discount' ) }}</h5>
                                            <small>{!!__( 'voucher.discount_description' )!!}</small>
                                        </div>
                                        <div class="col-sm">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-4">
                                                    <fieldset class="border p-2">
                                                        <legend class="mb-0 fs-6 float-none w-auto">{{ __( 'voucher.buy_quantity_rm' ) }}</legend>
                                                        <input type="number" class="form-control form-control-sm" id="{{ $voucher_create }}_cartd_buy_quantity">
                                                        <div class="invalid-feedback"></div>
                                                    </fieldset>
                                                </div>
                                                <div class="col-sm-12 col-md-8">
                                                    <fieldset class="border p-2">
                                                        <legend class="mb-0 fs-6 float-none w-auto">{{ __( 'voucher.discount_quantity' ) }}</legend>
                                                        <div class="row">
                                                            <div class="col">
                                                                <input type="number" class="form-control form-control-sm" id="{{ $voucher_create }}_cartd_discount_quantity">
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="mb-3 row">
                    <label for="{{ $voucher_create}}_total_claimable" class="col-sm-5 col-form-label">{{ __( 'voucher.total_claimable' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $voucher_create}}_total_claimable" value=1>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $voucher_create}}_claim_per_user" class="col-sm-5 col-form-label">{{ __( 'voucher.claim_per_user' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $voucher_create}}_claim_per_user">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $voucher_create}}_points_required" class="col-sm-5 col-form-label">{{ __( 'voucher.points_required' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $voucher_create}}_points_required">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row d-none">
                    <label for="{{ $voucher_create}}_usable_amount" class="col-sm-5 col-form-label">{{ __( 'voucher.usable_amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $voucher_create}}_usable_amount">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $voucher_create}}_validity_days" class="col-sm-5 col-form-label">{{ __( 'voucher.validity_days' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $voucher_create}}_validity_days">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $voucher_create }}_promo_code" class="col-sm-5 col-form-label">{{ __( 'voucher.promo_code' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $voucher_create }}_promo_code">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $voucher_create}}_start_date" class="col-sm-5 col-form-label">{{ __( 'voucher.start_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $voucher_create}}_start_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $voucher_create}}_expired_date" class="col-sm-5 col-form-label">{{ __( 'voucher.expired_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $voucher_create}}_expired_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button id="{{ $voucher_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                &nbsp;
                <button id="{{ $voucher_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
window.ckeupload_path = '{{ route( 'admin.voucher.ckeUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
window.cke_element = [ 'voucher_create_en_description', 'voucher_create_zh_description' ];
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multi.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $voucher_create }}',
                fileID = '';

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.voucher.index' ) }}';
        } );

        $( fc + '_start_date' ).flatpickr( {
            disableMobile: false,
        } );

        $( fc + '_expired_date' ).flatpickr( {
            disableMobile: false,
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            let type = $( fc + '_discount_type' ).val();

            let data = {};

            if( type == 3 ){
                data = bxgyData( data );
            }else {
                data = cartdData( data );
            }

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'en_title', $( fc + '_en_title' ).val() );
            formData.append( 'zh_title', $( fc + '_zh_title' ).val() );
            formData.append( 'promo_code', $( fc + '_promo_code' ).val() );
            formData.append( 'discount_type', type );
            formData.append( 'voucher_type', $( fc + '_voucher_type' ).val() );
            formData.append( 'total_claimable', $( fc + '_total_claimable' ).val() );
            formData.append( 'points_required', $( fc + '_points_required' ).val() );
            formData.append( 'start_date', $( fc + '_start_date' ).val() );
            formData.append( 'expired_date', $( fc + '_expired_date' ).val() );
            formData.append( 'usable_amount', $( fc + '_usable_amount' ).val() );
            formData.append( 'validity_days', $( fc + '_validity_days' ).val() );
            formData.append( 'claim_per_user', $( fc + '_claim_per_user' ).val() );
            formData.append( 'en_description', editors['voucher_create_en_description'].getData() );
            formData.append( 'zh_description', editors['voucher_create_zh_description'].getData() );
            formData.append( 'image', fileID );
            formData.append( 'adjustment_data', JSON.stringify(data) );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.voucher.createVoucher' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.voucher.index' ) }}';
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
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            removedfile: function( file ) {

                var idToRemove = file.previewElement.id;

                var idArrays = fileID.split(/\s*,\s*/);

                var indexToRemove = idArrays.indexOf( idToRemove.toString() );
                if (indexToRemove !== -1) {
                    idArrays.splice( indexToRemove, 1 );
                }

                fileID = idArrays.join( ', ' );

                file.previewElement.remove();
            },
            success: function( file, response ) {

                if ( response.status == 200 )  {
                    if ( fileID !== '' ) {
                        fileID += ','; // Add a comma if fileID is not empty
                    }
                    fileID += response.data.id;

                    file.previewElement.id = response.data.id;
                }
            }
        } );

        $( fc + '_discount_type' ).change( function() {
            $( '.rule-section' ).addClass( 'hidden' );
            
            switch ( parseInt( $( this ).val() ) ) {
                case 3:
                    $( '#bxgy' ).removeClass( 'hidden' );
                    $( '#cartd' ).addClass( 'hidden' );
                    break;

                default:
                    $( '#cartd' ).removeClass( 'hidden' );
                    $( '#bxgy' ).addClass( 'hidden' );
                    break;
            }
        } );

        $( fc + '_bxgy_buy_products' ).select2( {
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.product.allProducts' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        title: params.term, // search term
                        start: params.page ? params.page : 0,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.products.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.title,
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

        $( fc + '_bxgy_get_product' ).select2( {
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: true,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.product.allProducts' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        title: params.term, // search term
                        start: params.page ? params.page : 0,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.products.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.title,
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

        function bxgyData( data ) {

            data['buy_products'] = [];
            let selected = $( fc + '_bxgy_buy_products' ).find(':selected');
            for ( let i = 0; i < selected.length; i++ ) {
                const element = selected[i];
                data['buy_products'].push( $( element ).val() );
            }

            data['buy_quantity'] = $( fc + '_bxgy_buy_quantity' ).val();
            data['get_quantity'] = $( fc + '_bxgy_get_quantity' ).val();
            data['get_product'] = $( fc + '_bxgy_get_product' ).val();

            return data;
        }

        function cartdData( data ) {

            data['buy_quantity'] = $( fc + '_cartd_buy_quantity' ).val();
            data['discount_quantity'] = $( fc + '_cartd_discount_quantity' ).val();

            return data;
        }

        function bxgyValidation( error ) {
            let errors = error.responseJSON.errors;
            $.each( errors, function( key, value ) {
                $( fc + '_bxgy_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
            } );
        }

        function cartdValidation( error ) {
            let errors = error.responseJSON.errors;
            $.each( errors, function( key, value ) {
                $( fc + '_cartd_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
            } );
        }

    } );
</script>