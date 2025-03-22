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
$voucher_edit = 'voucher_edit';
$discountTypes = $data['discount_types'];
$voucherTypes = $data['voucher_type'];
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.vouchers' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3">
                    <label>{{ __( 'voucher.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $voucher_edit }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $voucher_edit }}_voucher_type" class="col-sm-5 col-form-label">{{ __( 'voucher.voucher_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $voucher_edit }}_voucher_type">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'voucher.voucher_type' ) ] ) }}</option>
                            @forEach( $voucherTypes as $key => $voucherType )
                                <option value="{{ $key }}">{{ $voucherType }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $voucher_edit }}_discount_type" class="col-sm-5 col-form-label">{{ __( 'voucher.discount_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $voucher_edit }}_discount_type">
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
                                            <select class="form-select form-select-sm" id="{{ $voucher_edit }}_bxgy_buy_products" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'voucher.buy_products' ) ] ) }}" multiple>
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
                                                        <input type="number" class="form-control form-control-sm" id="{{ $voucher_edit }}_bxgy_buy_quantity">
                                                        <div class="invalid-feedback"></div>
                                                    </fieldset>
                                                </div>
                                                <div class="col-sm-12 col-md-8">
                                                    <fieldset class="border p-2">
                                                        <legend class="mb-0 fs-6 float-none w-auto">{{ __( 'voucher.get_quantity' ) }}</legend>
                                                        <div class="row">
                                                            <div class="col">
                                                                <input type="number" class="form-control form-control-sm" id="{{ $voucher_edit }}_bxgy_get_quantity">
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                            <div class="col">
                                                                <select class="form-select form-select-sm" id="{{ $voucher_edit }}_bxgy_get_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'voucher.get_product' ) ] ) }}">
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
                                                        <input type="number" class="form-control form-control-sm" id="{{ $voucher_edit }}_cartd_buy_quantity">
                                                        <div class="invalid-feedback"></div>
                                                    </fieldset>
                                                </div>
                                                <div class="col-sm-12 col-md-8">
                                                    <fieldset class="border p-2">
                                                        <legend class="mb-0 fs-6 float-none w-auto">{{ __( 'voucher.discount_quantity' ) }}</legend>
                                                        <div class="row">
                                                            <div class="col">
                                                                <input type="number" class="form-control form-control-sm" id="{{ $voucher_edit }}_cartd_discount_quantity">
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
                    <label for="{{ $voucher_edit}}_total_claimable" class="col-sm-5 col-form-label">{{ __( 'voucher.total_claimable' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $voucher_edit}}_total_claimable">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $voucher_edit}}_claim_per_user" class="col-sm-5 col-form-label">{{ __( 'voucher.claim_per_user' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $voucher_edit}}_claim_per_user">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $voucher_edit}}_points_required" class="col-sm-5 col-form-label">{{ __( 'voucher.points_required' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $voucher_edit}}_points_required">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                
                <div class="mb-3 row">
                    <label for="{{ $voucher_edit}}_usable_amount" class="col-sm-5 col-form-label">{{ __( 'voucher.usable_amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $voucher_edit}}_usable_amount">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $voucher_edit}}_validity_days" class="col-sm-5 col-form-label">{{ __( 'voucher.validity_days' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $voucher_edit}}_validity_days">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $voucher_edit }}_promo_code" class="col-sm-5 col-form-label">{{ __( 'voucher.promo_code' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $voucher_edit }}_promo_code">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $voucher_edit }}_title" class="col-sm-5 col-form-label">{{ __( 'voucher.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $voucher_edit }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $voucher_edit }}_description" class="col-sm-5 col-form-label">{{ __( 'voucher.description' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control"  style="min-height: 80px;" name="{{ $voucher_edit }}_description" id="{{ $voucher_edit }}_description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $voucher_edit}}_start_date" class="col-sm-5 col-form-label">{{ __( 'voucher.start_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $voucher_edit}}_start_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $voucher_edit}}_expired_date" class="col-sm-5 col-form-label">{{ __( 'voucher.expired_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $voucher_edit}}_expired_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $voucher_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $voucher_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
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
window.cke_element1 = 'voucher_edit_description';
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init.js' ) }}"></script>


<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $voucher_edit }}',
                fileID = '';

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.voucher.index' ) }}';
        } );

        let startDate = $( fe + '_start_date' ).flatpickr( {
            disableMobile: false,
        } );

        let endDate = $( fe + '_expired_date' ).flatpickr( {
            disableMobile: false,
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();
            
            let type = $( fe + '_discount_type' ).val();

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
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'title', $( fe + '_title' ).val() );
            formData.append( 'promo_code', $( fe + '_promo_code' ).val() );
            formData.append( 'discount_type', type );
            formData.append( 'voucher_type', $( fe + '_voucher_type' ).val() );
            formData.append( 'total_claimable', $( fe + '_total_claimable' ).val() );
            formData.append( 'points_required', $( fe + '_points_required' ).val() );
            formData.append( 'start_date', $( fe + '_start_date' ).val() );
            formData.append( 'expired_date', $( fe + '_expired_date' ).val() );
            formData.append( 'usable_amount', $( fe + '_usable_amount' ).val() );
            formData.append( 'validity_days', $( fe + '_validity_days' ).val() );
            formData.append( 'claim_per_user', $( fe + '_claim_per_user' ).val() );
            formData.append( 'description', editor.getData() );
            formData.append( 'image', fileID );
            formData.append( 'adjustment_data', JSON.stringify(data) );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.voucher.updateVoucher' ) }}',
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
                            $( fe + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getVoucher();
        Dropzone.autoDiscover = false;

        function getVoucher() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.voucher.oneVoucher' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    
                    $( fe + '_title' ).val( response.title );
                    $( fe + '_description' ).val( response.description );
                    $( fe + '_promo_code' ).val( response.promo_code );
                    $( fe + '_discount_type' ).val( response.discount_type );
                    $( fe + '_total_claimable' ).val( response.total_claimable );
                    $( fe + '_points_required' ).val( response.points_required );
                    $( fe + '_voucher_type' ).val( response.type );
                    $( fe + '_usable_amount' ).val( response.usable_amount );
                    $( fe + '_validity_days' ).val( response.validity_days );
                    $( fe + '_claim_per_user' ).val( response.claim_per_user );
                    endDate.setDate( response.expired_date );
                    startDate.setDate( response.start_date );
                    editor.setData( response.description );

                    switch ( parseInt( response.discount_type ) ) {
                        case 3:
                            if( response.decoded_adjustment ) {
                                setBxgyData( response );
                            }

                            $( '#bxgy' ).removeClass( 'hidden' );
                            break

                        default:
                            if( response.decoded_adjustment ) {

                                setcartdData( response );
                            }

                            $( '#cartd' ).removeClass( 'hidden' );
                            break
                    }

                    const dropzone = new Dropzone( fe + '_image', {
                        url: '{{ route( 'admin.file.upload' ) }}',
                        maxFiles: 10,
                        acceptedFiles: 'image/jpg,image/jpeg,image/png',
                        addRemoveLinks: true,
                        init: function() {

                            let that = this;
                            console.log(response)
                            if ( response.image_path != 0 ) {
                                let myDropzone = that
                                    cat_id = '{{ request('id') }}',
                                    mockFile = { name: 'Default', size: 1024, accepted: true, id: cat_id };

                                myDropzone.files.push( mockFile );
                                myDropzone.displayExistingFile( mockFile, response.image_path );
                                $( myDropzone.files[myDropzone.files.length - 1].previewElement ).data( 'id', cat_id );
                            }
                        },
                        removedfile: function( file ) {
                            var idToRemove = file.id;

                            var idArrays = fileID.split(/\s*,\s*/);

                            var indexToRemove = idArrays.indexOf( idToRemove.toString() );
                            if (indexToRemove !== -1) {
                                idArrays.splice( indexToRemove, 1 );
                            }

                            fileID = idArrays.join( ', ' );

                            file.previewElement.remove();

                            removeGallery( idToRemove );

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

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

        function removeGallery( gallery ) {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', gallery );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.voucher.removeVoucherGalleryImage' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
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
        }

        $( fe + '_discount_type' ).change( function() {
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

        let bxgyBuyProduct = $( fe + '_bxgy_buy_products' ).select2( {
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

        let bxgyGetProduct = $( fe + '_bxgy_get_product' ).select2( {
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
            let selected = $( fe + '_bxgy_buy_products' ).find(':selected');
            for ( let i = 0; i < selected.length; i++ ) {
                const element = selected[i];
                data['buy_products'].push( $( element ).val() );
            }

            data['buy_quantity'] = $( fe + '_bxgy_buy_quantity' ).val();
            data['get_quantity'] = $( fe + '_bxgy_get_quantity' ).val();
            data['get_product'] = $( fe + '_bxgy_get_product' ).val();

            return data;
        }

        function cartdData( data ) {

            data['buy_quantity'] = $( fe + '_cartd_buy_quantity' ).val();
            data['discount_quantity'] = $( fe + '_cartd_discount_quantity' ).val();

            return data;
        }

        function bxgyValidation( error ) {
            let errors = error.responseJSON.errors;
            $.each( errors, function( key, value ) {
                $( fe + '_bxgy_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
            } );
        }

        function cartdValidation( error ) {
            let errors = error.responseJSON.errors;
            $.each( errors, function( key, value ) {
                $( fe + '_cartd_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
            } );
        }

        function setBxgyData( response ) {
            let filter = response.decoded_adjustment.buy_products_info;
            filter.map( function( v, i ) {
                let option = new Option( v.title, v.id, true, true );
                bxgyBuyProduct.append( option )
            } );
            bxgyBuyProduct.trigger( 'change' );

            let bxgyAdjustment = response.decoded_adjustment;

            $( fe + '_bxgy_buy_quantity' ).val( bxgyAdjustment.buy_quantity );
            $( fe + '_bxgy_get_quantity' ).val( bxgyAdjustment.get_quantity );

            let option1 = new Option( bxgyAdjustment.get_product_info.title, bxgyAdjustment.get_product_info.id, true, true );            
            bxgyGetProduct.append( option1 );
            bxgyGetProduct.trigger( 'change' );
        }

        function setcartdData( response ) {

            let bxgyAdjustment = response.decoded_adjustment;

            $( fe + '_cartd_buy_quantity' ).val( bxgyAdjustment.buy_quantity );
            $( fe + '_cartd_discount_quantity' ).val( bxgyAdjustment.discount_quantity );
        }

    } );
</script>