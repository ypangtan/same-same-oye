<?php
$adjustment_edit = 'adjustment_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.adjustments' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $adjustment_edit }}_adjustment_date" class="col-sm-5 col-form-label">{{ __( 'adjustment.adjustment_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $adjustment_edit }}_adjustment_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $adjustment_edit }}_warehouse" class="col-sm-5 form-label">{{ __( 'template.warehouses' ) }}</label>
                        <div class="col-sm-7">
                            <select class="form-select" id="{{ $adjustment_edit }}_warehouse" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.warehouses' ) ] ) }}">
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $adjustment_edit }}_product" class="col-sm-5 form-label">{{ __( 'template.products' ) }}</label>
                    <div class="col-sm-7">

                        <select class="form-select" id="{{ $adjustment_edit }}_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.products' ) ] ) }}" multiple="multiple">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $adjustment_edit }}_remarks" class="col-sm-5 col-form-label">{{ __( 'adjustment.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $adjustment_edit }}_remarks"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'adjustment.attachment' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $adjustment_edit }}_attachment" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
            
                <label class="mb-3" >{{ __( 'template.products' ) }}</label>
                <table class="table table-bordered" id="product-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic rows will be added here -->
                    </tbody>
                </table>

                <div class="text-end mt-3">
                    <button id="{{ $adjustment_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $adjustment_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $adjustment_edit }}',
                fileID = '';

        $( fe + '_adjustment_date' ).flatpickr( {
            disableMobile: true,
            defaultDate: 'today',
        } );

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.adjustment.index' ) }}';
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'adjustment_date', $( fe + '_adjustment_date' ).val() );
            formData.append( 'remarks', $( fe + '_remarks' ).val() );
            formData.append( 'attachment', fileID );
            formData.append( 'products', $(fe + '_product').val() );
            formData.append( 'warehouse', $(fe + '_warehouse').val() );
            formData.append( '_token', '{{ csrf_token() }}' );
            let selectedProducts = $(fe + '_product').val();

            selectedProducts.forEach(function(productId,index) {
                let quantityInput = $(`#product-${productId} .product-quantity`).val();
                let IdInput = $(`#product-${productId} .meta-id`).val();

                formData.append(`products[${index}][metaId]`, IdInput == undefined ? null : IdInput );
                formData.append(`products[${index}][id]`, productId);
                formData.append(`products[${index}][quantity]`, quantityInput);

                $(`.variant-row[data-parent-id="product-${productId}"]`).each(function (variantIndex, variantRow) {
                    let variantId = $(variantRow).attr('id').replace('variant-', '');
                    let variantQuantity = $(variantRow).find('.variant-quantity').val();
                    let IdInput = $(`#variant-${variantId} .meta-id`).val();
                    // Append variant details under the corresponding product

                    formData.append(`products[${index}][variants][${variantIndex}][metaId]`, IdInput == undefined ? null : IdInput );
                    formData.append(`products[${index}][variants][${variantIndex}][id]`, variantId);
                    formData.append(`products[${index}][variants][${variantIndex}][quantity]`, variantQuantity);
                });
            });

            $.ajax( {
                url: '{{ route( 'admin.adjustment.updateAdjustment' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.adjustment.index' ) }}';
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

        getAdjustment();
        Dropzone.autoDiscover = false;

        let adjustmentDate = $( fe + '_adjustment_date' ).flatpickr( {
            disableMobile: true,
        } );

        let wareHouseSelect2 =$( fe + '_warehouse' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.warehouse.allWarehouses' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        status: 10,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.warehouses.map( function( v, i ) {
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
            },
        } );
        
        let productSelect2 = $(fe + '_product').select2({
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            allowClear: true,
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            closeOnSelect: true, // Auto close after selection
            ajax: {
                method: 'POST',
                url: '{{ route('admin.product.allProducts') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        name: params.term, // search term
                        status: 10,
                        start: ((params.page ? params.page : 1) - 1) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];
                    let addedBundles = new Set(); // Track unique bundles

                    // Process products and bundles with indicators
                    data.products.forEach(function(product) {
                        // Add the main product with a unique prefixed ID
                        processedResult.push({
                            id: `product-${product.id}`, 
                            text: `Product: ${product.title}`,
                            variants: product.variants
                        });

                        // Add bundles associated with the product, ensuring uniqueness
                        if (product.bundles && Array.isArray(product.bundles)) {
                            product.bundles.forEach(function(bundle) {
                                if (!addedBundles.has(bundle.id)) { // Check if the bundle is already added
                                    processedResult.push({
                                        id: `bundle-${bundle.id}`, // Prefix with "bundle-"
                                        text: `Bundle: ${bundle.title}`, // Prefix with "Bundle:"
                                        variants: null
                                    });
                                    addedBundles.add(bundle.id); // Mark this bundle as added
                                }
                            });
                        }
                    });
                    
                    return {
                        results: processedResult,
                        pagination: {
                            more: (params.page * 10) < data.recordsFiltered
                        }
                    };
                }
            }
        });

        function getAdjustment() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.adjustment.oneAdjustment' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    
                    $( fe + '_remarks' ).val( response.remarks );
                    adjustmentDate.setDate( response.adjustment_date );

                    if ( response.warehouse ) {
                        let option1 = new Option( response.warehouse.title, response.warehouse.id, true, true );
                        wareHouseSelect2.append( option1 );
                        wareHouseSelect2.trigger( 'change' );
                    }
                    appended = [];
                    response.adjustment_metas.forEach((product, index) => {

                        if( (product.product && !product.variant) || (product.bundle && !product.variant) ){

                            formattedId = product.bundle ? 'bundle-' + product.bundle.id : 'product-' + product.product.id;
                            formattedTitle = product.bundle ? product.bundle.title : product.product.title;

                            let option = new Option(formattedTitle, formattedId, true, true); 
                            productSelect2.append(option);

                            let productTable = $('#product-table tbody');
                            if (!productTable.length) {
                                console.error("Product table body not found.");
                                return;
                            }

                            if ($(`#product-${product.id}`).length === 0) {
                                productCount++;
                                productTable.append(`
                                    <tr id="product-${formattedId}">
                                        <td>${productCount}</td>
                                        <td>${formattedTitle}</td>
                                        <td>
                                            <input type="hidden" class="form-control meta-id" value="${product.id}"> 
                                            <input type="number" class="form-control product-quantity" 
                                                style="width: 100px;" min="1" 
                                                data-product-id="${formattedId}" value="${product.amount}">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-product" 
                                                    data-product-id="${formattedId}">
                                                <i class="fa fa-trash"></i> Remove
                                            </button>
                                        </td>
                                    </tr>
                                `);
                            }

                            $(document).on('click', '.remove-product', function () {
                                let productId = $(this).data('product-id');
                                $('#product-' + productId).remove();
                                $(`.variant-row[data-parent-id="product-${productId}"]`).remove();

                                // Re-index the table rows
                                productCount = 0;
                                $('#product-table tbody tr').each(function () {
                                    productCount++;
                                    $(this).find('td:first').text(productCount);
                                });
                            });
                        }else {

                            if(product.variant != null) {

                                formattedId = product.bundle ? 'bundle-' + product.bundle.id : 'product-' + product.product.id;
                                formattedTitle = product.bundle ? product.bundle.title : product.product.title;

                                $('#product-table tbody').append(`
                                    <tr id="variant-${product.variant.id}" class="variant-row" data-parent-id="product-${formattedId}">
                                        <td>${productCount}.${index + 1}</td>
                                        <td>Variant: ${product.variant.title}</td>
                                        <td>
                                            <input type="hidden" class="form-control meta-id" value="${product.id}"> 
                                            <input type="number" class="form-control variant-quantity" 
                                                style="width: 100px;" min="1" 
                                                data-variant-id="${product.variant.id}" data-product-id="${formattedId}" value="${product.amount}"
                                        </td>
                                        <td></td>
                                    </tr>
                                `);
                            }
                        }
                       
                    });
                    const dropzone = new Dropzone( fe + '_attachment', {
                        url: '{{ route( 'admin.file.upload' ) }}',
                        maxFiles: 10,
                        acceptedFiles: 'image/jpg,image/jpeg,image/png',
                        addRemoveLinks: true,
                        init: function() {

                            let that = this;
                            if ( response.attachment_path != 0 ) {
                                let myDropzone = that
                                    cat_id = '{{ request('id') }}',
                                    mockFile = { name: 'Default', size: 1024, accepted: true, id: cat_id };

                                myDropzone.files.push( mockFile );
                                myDropzone.displayExistingFile( mockFile, response.attachment_path );
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

        let productCount = 0; // Counter for product rows
        $(fe + '_product').on('select2:select', function (e) {
            let selectedProduct = e.params.data;

            // Check if the product is already in the table
            if ($('#product-' + selectedProduct.id).length === 0) {
                productCount++;

                // Append a new row to the table
                $('#product-table tbody').append(`
                    <tr id="product-${selectedProduct.id}">
                        <td>${productCount}</td>
                        <td>${selectedProduct.text}</td>
                        <td>
                            <input type="number" class="form-control product-quantity" 
                                style="width: 100px;" min="1" 
                                data-product-id="${selectedProduct.id}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-product" 
                                    data-product-id="${selectedProduct.id}">
                                <i class="fa fa-trash"></i> Remove
                            </button>
                        </td>
                    </tr>
                `);

                if(selectedProduct.variants != null) {
                    selectedProduct.variants.forEach((variant, index) => {
                        $('#product-table tbody').append(`
                            <tr id="variant-${variant.id}" class="variant-row" data-parent-id="product-${selectedProduct.id}">
                                <td>${productCount}.${index + 1}</td>
                                <td>Variant: ${variant.title}</td>
                                <td>
                                    <input type="number" class="form-control variant-quantity" 
                                        style="width: 100px;" min="1" 
                                        data-variant-id="${variant.id}" data-product-id="${selectedProduct.id}">
                                </td>
                                <td></td>
                            </tr>
                        `);
                    });
                }
            }
        });

        $(document).on('click', '.remove-product', function () {
            let productId = $(this).data('product-id');
            
            // Remove from table
            $('#product-' + productId).remove();
            $(`.variant-row[data-parent-id="product-${productId}"]`).remove();
            
            // Remove from Select2
            let selectElement = $(fe + '_product'); // Replace with your Select2 element ID
            let selectedValues = selectElement.val();
            if (selectedValues) {
                selectedValues = selectedValues.filter(value => value !== productId.toString());
                selectElement.val(selectedValues).trigger('change');
            }

            // Re-index the table rows
            productCount = 0;
            $('#product-table tbody tr').each(function () {
                productCount++;
                $(this).find('td:first').text(productCount);
            });
        });
        $(fe + '_product').on('select2:unselect', function (e) {
            var categoryId = e.params.data.id; 
            $('#product-' + categoryId).remove();
            $(`.variant-row[data-parent-id="product-${categoryId}"]`).remove();

            var selectedValues = $(fe + '_product').val(); // Get the current selected values
            selectedValues = selectedValues.filter(function (id) {
                return id != categoryId; // Remove the unselected productId
            });

            // Reassign the remaining selected values back to select2
            $(fe + '_product').val(selectedValues).trigger('change');
            // Re-index the table rows
            productCount = 0;
            $('#product-table tbody tr').each(function () {
                productCount++;
                $(this).find('td:first').text(productCount);
            });
        });

        $(fe + '_product').on("select2:select", function (evt) {
            var element = evt.params.data.element;
            var $element = $(element);
            
            $element.detach();
            $(this).append($element);
            $(this).trigger("change");
        });

        function removeGallery( gallery ) {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', gallery );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.adjustment.removeAdjustmentAttachment' ) }}',
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

    } );
</script>