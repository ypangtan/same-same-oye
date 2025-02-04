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
$product_bundle_create = 'product_bundle_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.product_bundles' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
            
                <div class="mb-3">
                    <label class="form-label">{{ __( 'product.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $product_bundle_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_bundle_create }}_title" class="col-sm-5 form-label">{{ __( 'product.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $product_bundle_create }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $product_bundle_create }}_code" class="col-sm-5 form-label">{{ __( 'product.code' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $product_bundle_create }}_code">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_bundle_create }}_description" class="col-sm-5 form-label">{{ __( 'product.description' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" name="{{ $product_bundle_create }}_description" id="{{ $product_bundle_create }}_description" rows="5"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $product_bundle_create }}_validity_days" class="col-sm-5 form-label">{{ __( 'product.validity_days' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $product_bundle_create }}_validity_days">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_bundle_create }}_price" class="col-sm-5 form-label">{{ __( 'product.price' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $product_bundle_create }}_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_bundle_create }}_discount_price" class="col-sm-5 form-label">{{ __( 'product.discount_price' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $product_bundle_create }}_discount_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $product_bundle_create }}_product" class="col-sm-5 form-label">{{ __( 'product_bundle.product' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $product_bundle_create }}_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product_bundle.product' ) ] ) }}" multiple="multiple">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div id="productBundleContainer" ></div>

                <div class="text-end">
                    <button id="{{ $product_bundle_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $product_bundle_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
window.ckeupload_path = '{{ route( 'admin.product_bundle.ckeUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
window.cke_element1 = 'product_bundle_create_description';
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $product_bundle_create }}',
                fileID = '',
                container = $('#productBundleContainer');

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.product_bundle.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'products', $( fc + '_product' ).val() );
            formData.append( 'code', $( fc + '_code' ).val() );
            formData.append( 'title', $( fc + '_title' ).val() );
            formData.append( 'description', $( fc + '_description' ).val() );
            formData.append( 'price', $( fc + '_price' ).val() );
            formData.append( 'discount_price', $( fc + '_discount_price' ).val() );
            formData.append( 'quantity', $( fc + '_quantity' ).val()  );
            $('#productBundleContainer input[type="number"]').each(function () {
                let productId = $(this).attr('id').replace('product_quantity_', ''); // Extract product ID
                let quantity = $(this).val(); // Get input value

                // Append as array (so PHP can process multiple values)
                formData.append(`quantities[${productId}]`, quantity);
            });
            formData.append( 'validity_days', $( fc + '_validity_days' ).val()  );
            
            formData.append( 'description', editor.getData() );
            formData.append( 'image', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.product_bundle.createProductBundle' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.product_bundle.index' ) }}';
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

        $( fc + '_product' ).select2( {
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
                        status: 10,
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

        $(fc + '_product').on('select2:select', function (e) {
            let data = e.params.data; 
            let inputId = `product_quantity_${data.id}`;

            if ($('#' + inputId).length === 0) {
                let inputHtml = `
                    <div class="mb-3 row" id="input_${data.id}">
                        <label for="${inputId}" class="col-sm-5 form-label">${data.text} Quantity</label>
                        <div class="col-sm-7">
                            <input type="number" class="form-control" id="${inputId}" value="1">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                `;
                container.append(inputHtml);
            }
        });

        $(fc + '_product').on('select2:unselect', function (e) {
            let data = e.params.data;
            $('#input_' + data.id).remove();
        });

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( fc + '_image', { 
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 10,
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
    } );
</script>