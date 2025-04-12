<?php
$delivery_order_create = 'delivery_order_create';
$taxTypes = $data['tax_types'];
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.delivery_orders' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $delivery_order_create }}_warehouse" class="col-sm-5 form-label">{{ __( 'template.warehouses' ) }}</label>
                        <div class="col-sm-7">
                            <select class="form-select" id="{{ $delivery_order_create }}_warehouse" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.warehouses' ) ] ) }}">
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $delivery_order_create }}_supplier" class="col-sm-5 form-label">{{ __( 'template.suppliers' ) }}</label>
                        <div class="col-sm-7">
                            <select class="form-select" id="{{ $delivery_order_create }}_supplier" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.suppliers' ) ] ) }}">
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $delivery_order_create }}_customer" class="col-sm-5 form-label">{{ __( 'template.customers' ) }}</label>
                        <div class="col-sm-7">
                            <select class="form-select" id="{{ $delivery_order_create }}_customer" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.customers' ) ] ) }}">
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $delivery_order_create }}_salesman" class="col-sm-5 form-label">{{ __( 'template.salesmen' ) }}</label>
                        <div class="col-sm-7">
                            <select class="form-select" id="{{ $delivery_order_create }}_salesman" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.salesmen' ) ] ) }}">
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $delivery_order_create }}_product" class="col-sm-5 form-label">{{ __( 'template.products' ) }}</label>
                    <div class="col-sm-7">

                        <select class="form-select" id="{{ $delivery_order_create }}_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.products' ) ] ) }}" multiple="multiple">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'delivery_order.attachment' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $delivery_order_create }}_attachment" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
            
                <label class="mb-3" >{{ __( 'template.products' ) }}</label>
                <table class="table table-bordered mb-3" id="product-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Tax</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic rows will be added here -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total Shipping:</th>
                            <th id="total-shipping">0.00</th>
                            <th ></th>
                            <th ></th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Total Discount:</th>
                            <th id="total-discount">0.00</th>
                            <th ></th>
                            <th ></th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th id="total-subtotal">0.00</th>
                            <th id="total-tax">0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>

                <div class="mb-3 row">
                    <label for="{{ $delivery_order_create }}_tax_method" class="col-sm-5 col-form-label">{{ __( 'product.tax_method' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $delivery_order_create }}_tax_method" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product.tax_method' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $delivery_order_create }}_discount" class="col-sm-5 col-form-label">{{ __( 'delivery_order.discount' ) }}</label>
                    <div class="col-sm-7">
                        <input class="form-control" type="number" name="{{ $delivery_order_create }}_discount" id="{{ $delivery_order_create }}_discount">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $delivery_order_create }}_shipping_cost" class="col-sm-5 col-form-label">{{ __( 'delivery_order.shipping_cost' ) }}</label>
                    <div class="col-sm-7">
                        <input class="form-control" type="number" name="{{ $delivery_order_create }}_shipping_cost" id="{{ $delivery_order_create }}_shipping_cost">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $delivery_order_create }}_remarks" class="col-sm-5 col-form-label">{{ __( 'delivery_order.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $delivery_order_create }}_remarks"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $delivery_order_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $delivery_order_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $delivery_order_create }}',
                fileID = '';

        $( fc + '_delivery_order_date' ).flatpickr( {
            disableMobile: true,
            defaultDate: 'today',
        } );

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.delivery_order.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'remarks', $( fc + '_remarks' ).val() );
            formData.append( 'attachment', fileID );
            formData.append( 'products', $(fc + '_product').val() );
            formData.append( 'warehouse', $(fc + '_warehouse').val() );
            formData.append( 'supplier', $(fc + '_supplier').val() );
            formData.append( 'customer', $(fc + '_customer').val() );
            formData.append( 'salesman', $(fc + '_salesman').val() );
            formData.append( 'tax_type', $(fc + '_tax_type').val() );
            formData.append( 'tax_method', $(fc + '_tax_method').val() );
            formData.append( 'discount', $(fc + '_discount').val() );
            formData.append( 'shipping_cost', $(fc + '_shipping_cost').val() );
            formData.append( '_token', '{{ csrf_token() }}' );
            let selectedProducts = $(fc + '_product').val();

            selectedProducts.forEach(function(productId,index) {
                let quantityInput = $(`#product-${productId} .product-quantity`).val();
                let IdInput = $(`#product-${productId} .meta-id`).val();

                formData.append(`products[${index}][metaId]`, IdInput == undefined ? null : IdInput );
                formData.append(`products[${index}][id]`, productId);
                formData.append(`products[${index}][quantity]`, quantityInput);
            });

            $.ajax( {
                url: '{{ route( 'admin.delivery_order.createDeliveryOrder' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.delivery_order.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    $('#product-table tbody tr').each(function () {
                        $(this).find('.error-message').remove();
                    });
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            let match = key.match(/^products\.(\d+)\.quantity$/);
                            if (match) {
                                let index = match[1];
                                let productRow = $(`#product-table tbody tr:eq(${index})`);
                                
                                if (productRow.length > 0) {
                                    // Add error message below the quantity input
                                    console.log(productRow)
                                    console.log(productRow.find('.product-quantity').closest('td')[0].outerHTML);

                                    // Append error message below the quantity input
                                    $(productRow.find('.product-quantity').closest('td')[0]).append(`
                                        <div class="text-danger error-message">${value[0]}</div>
                                    `);
                                }
                            }else{
                                $( fc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                            }
                        } );

                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( fc + '_attachment', { 
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

        $( fc + '_tax_method' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.tax_method.allTaxMethods' ) }}',
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

                    data.tax_methods.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.title,
                            formatted_tax: v.formatted_tax,
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

        $( fc + '_warehouse' ).select2( {
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

        $( fc + '_supplier' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.supplier.allSuppliers' ) }}',
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

                    data.suppliers.map( function( v, i ) {
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

        $( fc + '_customer' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.user.allUsers' ) }}',
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

                    data.users.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.email,
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

        $( fc + '_salesman' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.administrator.allSalesmen' ) }}',
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

                    data.administrators.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.name,
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

        let productSelect = $(fc + '_product');
        let quantityInputContainer = $('#quantity-input-container');

        // Clear all select2 selections
        productSelect.val(null).trigger('change');

        // Clear all quantity input fields
        quantityInputContainer.empty();

        $(fc + '_product').prop('disabled', true);

        $(fc + '_warehouse').on('select2:select', function () {
            $(fc + '_product').prop('disabled', false);
            $(fc + '_product').val(null).trigger('change');
            $('#product-table tbody').empty();
            productCount = 0;
        });

        $(fc + '_product').select2({
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
                        warehouse: $( fc + '_warehouse' ).val(), // search term
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
                    let addedVariants = new Set(); // Track unique bundles

                    // Process products and bundles with indicators
                    data.products.forEach(function(product) {
                        // Add the main product with a unique prefixed ID

                        let productPrice = product.price;

                        // Check for warehouses and apply warehouse-specific price
                        if (Array.isArray(product.warehouses) && product.warehouses.length > 0) {
                            let matchingWarehouse = product.warehouses.find(warehouse => warehouse.id === parseInt($(fc + '_warehouse').val(), 10));
                            if (matchingWarehouse) {
                                productPrice = ( matchingWarehouse.pivot.price && matchingWarehouse.pivot.price > 0 ) ? matchingWarehouse.pivot.price : product.price;
                            }
                        }

                        processedResult.push({
                            id: `product-${product.id}`, 
                            text: `Product: ${product.title}`,
                            price: productPrice,
                        });

                        // Add bundles associated with the product, ensuring uniqueness
                        if (product.bundles && Array.isArray(product.bundles)) {
                            product.bundles.forEach(function(bundle) {
                                if (!addedBundles.has(bundle.id)) { 
                                    processedResult.push({
                                        id: `bundle-${bundle.id}`,
                                        text: `Bundle: ${bundle.title}`,
                                        price: product.price,
                                    });
                                    addedBundles.add(bundle.id);
                                }
                            });
                        }

                        if (product.variants && Array.isArray(product.variants)) {
                            product.variants.forEach(function(variant) {
                                if (!addedVariants.has(variant.id)) { 
                                    processedResult.push({
                                        id: `variant-${variant.id}`,
                                        text: `Variant: ${variant.title}`,
                                        price: variant.price,
                                    });
                                    addedVariants.add(variant.id);
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
        let productCount = 0; // Counter for product rows

        function updateTotals() {
            let totalSubtotal = 0;
            let totalTax = 0;

            // Loop through each row to calculate totals
            $('#product-table tbody tr').each(function () {
                let quantity = parseFloat($(this).find('.product-quantity').val()) || 0;
                let subtotal = parseFloat($(this).find('.product-subtotal').text()) || 0;
                let tax = parseFloat($(this).find('.product-tax').text()) || 0;

                totalSubtotal += subtotal;
                totalTax += tax;
            });

            
            let shipping = parseFloat($(fc + '_shipping_cost').val()) || 0;
            let discount = parseFloat($(fc + '_discount').val()) || 0;
            totalSubtotal += shipping - discount;

            // Update the footer totals
            $('#total-subtotal').text(totalSubtotal.toFixed(2));
            $('#total-tax').text(totalTax.toFixed(2));

            $('#total-shipping').text(shipping.toFixed(2));
            $('#total-discount').text(discount.toFixed(2));
        }

        $(fc + '_discount').on('keyup', function (e) {
            updateTotals();
        });

        $(fc + '_shipping_cost').on('keyup', function (e) {
            updateTotals();
        });

        // Add product row logic (adjusted)
        $(fc + '_product').on('select2:select', function (e) {
            let selectedProduct = e.params.data;

            // Check if the product is already in the table
            if ($('#product-' + selectedProduct.id).length === 0) {
                productCount++;

                // Mock values for demo purposes
                let price = e.params.data.price; // Example price
                let taxRate = $(fc + '_tax_method').select2('data').length > 0 ?  $(fc + '_tax_method').select2('data')[0]['formatted_tax'] : 0.06; // Example tax rate
                let subtotal = price * 1; // Quantity starts at 1
                let tax = subtotal * taxRate;

                // Append a new row to the table
                $('#product-table tbody').append(`
                    <tr id="product-${selectedProduct.id}">
                        <td>${productCount}</td>
                        <td>${selectedProduct.text}</td>
                        <td>
                            <input type="number" class="form-control product-quantity" 
                                style="width: 100px;" min="1" value="1"
                                data-product-id="${selectedProduct.id}"
                                data-product-price="${selectedProduct.price}">
                        </td>
                        <td class="product-subtotal">${subtotal.toFixed(2)}</td>
                        <td class="product-tax">${tax.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-product" 
                                    data-product-id="${selectedProduct.id}">
                                <i class="fa fa-trash"></i> Remove
                            </button>
                        </td>
                    </tr>
                `);

                updateTotals(); // Update totals after adding a product
            }
        });

        // Handle quantity change
        $(document).on('input', '.product-quantity', function () {
            let quantity = parseFloat($(this).val()) || 0;
            let productId = $(this).data('product-id');

            // Update subtotal and tax
            let price =  $(this).data('product-price'); // Example price, replace with real data
            let taxRate = $(fc + '_tax_method').select2('data').length > 0 ?  $(fc + '_tax_method').select2('data')[0]['formatted_tax'] : 0.06; // Example tax rate
            let subtotal = price * quantity;
            let tax = subtotal * taxRate;
            // Update the respective row
            $(`#product-${productId}`).find('.product-subtotal').text(subtotal.toFixed(2));
            $(`#product-${productId}`).find('.product-tax').text(tax.toFixed(2));

            updateTotals(); // Update totals after modifying quantity
        });

        // Handle product removal
        $(document).on('click', '.remove-product', function () {
            let productId = $(this).data('product-id');

            // Remove row from the table
            $(`#product-${productId}`).remove();
            var selectedValues = $(fc + '_product').val(); // Get the current selected values
            selectedValues = selectedValues.filter(function (id) {
                return id != productId; // Remove the unselected productId
            });
            $(fc + '_product').val(selectedValues).trigger('change');

            // Re-index rows
            productCount = 0;
            $('#product-table tbody tr').each(function () {
                productCount++;
                $(this).find('td:first').text(productCount);
            });

            updateTotals(); 
        });

        $(fc + '_product').on('select2:unselect', function (e) {
            var categoryId = e.params.data.id; 
            $('#product-' + categoryId).remove();
            updateTotals(); 

            var selectedValues = $(fc + '_product').val(); // Get the current selected values
            selectedValues = selectedValues.filter(function (id) {
                return id != categoryId; // Remove the unselected productId
            });

            // Reassign the remaining selected values back to select2
            $(fc + '_product').val(selectedValues).trigger('change');
            productCount = 0;

        });

        $(fc + '_product').on("select2:select", function (evt) {
            var element = evt.params.data.element;
            var $element = $(element);
            
            $element.detach();
            $(this).append($element);
            $(this).trigger("change");
        });

        $(fc + '_tax_method').on('select2:select', function (e) {
            let selectedTax = e.params.data;
        });

        $(fc + '_tax_method').on('select2:select', function (e) {

            let selectedTax = e.params.data; // Get the selected tax method
            let newTaxRate = selectedTax.formatted_tax; // Convert percentage to a decimal

            // Loop through each product row and recalculate tax
            $('#product-table tbody tr').each(function () {
                let quantity = parseFloat($(this).find('.product-quantity').val()) || 0; // Get the quantity
                let price = $(this).find('.product-quantity').data('product-price') || 0; // Get the price

                // Recalculate subtotal and tax
                let subtotal = price * quantity;
                let tax = subtotal * newTaxRate;

                // Update the respective row
                $(this).find('.product-subtotal').text(subtotal.toFixed(2));
                $(this).find('.product-tax').text(tax.toFixed(2));
            });

            updateTotals(); // Recalculate the totals for all products
        });

    } );
</script>