<?php
$purchase_create = 'purchase_create';
$taxTypes = $data['tax_types'];
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.purchases' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $purchase_create }}_purchase_date" class="col-sm-5 col-form-label">{{ __( 'purchase.purchase_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $purchase_create }}_purchase_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $purchase_create }}_warehouse" class="col-sm-5 form-label">{{ __( 'template.warehouses' ) }}</label>
                        <div class="col-sm-7">
                            <select class="form-select" id="{{ $purchase_create }}_warehouse" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.warehouses' ) ] ) }}">
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $purchase_create }}_supplier" class="col-sm-5 form-label">{{ __( 'template.suppliers' ) }}</label>
                        <div class="col-sm-7">
                            <select class="form-select" id="{{ $purchase_create }}_supplier" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.suppliers' ) ] ) }}">
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $purchase_create }}_product" class="col-sm-5 form-label">{{ __( 'template.products' ) }}</label>
                    <div class="col-sm-7">

                        <select class="form-select" id="{{ $purchase_create }}_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.products' ) ] ) }}" multiple="multiple">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'purchase.attachment' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $purchase_create }}_attachment" style="min-height: 0px;">
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
                            <th colspan="3" class="text-end">Total:</th>
                            <th id="total-subtotal">0.00</th>
                            <th id="total-tax">0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>

                <div class="mb-3 row">
                    <label for="{{ $purchase_create}}_tax_type" class="col-sm-5 col-form-label">{{ __( 'purchase.tax_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $purchase_create}}_tax_type" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'purchase.tax_type' ) ] ) }}">
                            
                            @foreach ($taxTypes as $taxType => $content)
                                <option value="{{ $taxType }}">{{ $content['title'] }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $purchase_create }}_discount" class="col-sm-5 col-form-label">{{ __( 'purchase.discount' ) }}</label>
                    <div class="col-sm-7">
                        <input class="form-control" type="number" name="{{ $purchase_create }}_discount" id="{{ $purchase_create }}_discount">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $purchase_create }}_shipping_cost" class="col-sm-5 col-form-label">{{ __( 'purchase.shipping_cost' ) }}</label>
                    <div class="col-sm-7">
                        <input class="form-control" type="number" name="{{ $purchase_create }}_shipping_cost" id="{{ $purchase_create }}_shipping_cost">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $purchase_create }}_remarks" class="col-sm-5 col-form-label">{{ __( 'purchase.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $purchase_create }}_remarks"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $purchase_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $purchase_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $purchase_create }}',
                fileID = '';

        $( fc + '_purchase_date' ).flatpickr( {
            disableMobile: true,
            defaultDate: 'today',
        } );

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.purchase.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'purchase_date', $( fc + '_purchase_date' ).val() );
            formData.append( 'remarks', $( fc + '_remarks' ).val() );
            formData.append( 'attachment', fileID );
            formData.append( 'products', $(fc + '_product').val() );
            formData.append( 'warehouse', $(fc + '_warehouse').val() );
            formData.append( 'supplier', $(fc + '_supplier').val() );
            formData.append( 'tax_type', $(fc + '_tax_type').val() );
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
                url: '{{ route( 'admin.purchase.createPurchase' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.purchase.index' ) }}';
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

        let productSelect = $(fc + '_product');
        let quantityInputContainer = $('#quantity-input-container');

        // Clear all select2 selections
        productSelect.val(null).trigger('change');

        // Clear all quantity input fields
        quantityInputContainer.empty();

        $('.select2-container--default .select2-results__option[aria-selected="true"]').on('click mousedown', function(event) {
            event.stopPropagation();
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
                        status: 10,
                        start: ((params.page ? params.page : 1) - 1) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.products.map(function(v, i) {
                        processedResult.push({
                            id: v.id,
                            text: v.title,
                            price: v.price
                        });
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

            // Update the footer totals
            $('#total-subtotal').text(totalSubtotal.toFixed(2));
            $('#total-tax').text(totalTax.toFixed(2));
        }

        // Add product row logic (adjusted)
        $(fc + '_product').on('select2:select', function (e) {
            let selectedProduct = e.params.data;

            // Check if the product is already in the table
            if ($('#product-' + selectedProduct.id).length === 0) {
                productCount++;

                // Mock values for demo purposes
                let price = e.params.data.price; // Example price
                let taxRate = 0.06; // Example tax rate
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
            let taxRate = 0.06; // Example tax rate
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

            // Remove the option from the Select2 dropdown
            $(fc + '_product').find(`option[value="${productId}"]`).remove(); // Replace 'product-select' with your Select2 element's ID
            $(fc + '_product').trigger('change'); // Update Select2 to reflect changes

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
        });


        $(fc + '_product').on("select2:select", function (evt) {
            var element = evt.params.data.element;
            var $element = $(element);
            $element.detach();
            $(this).append($element);
            $(this).trigger("change");
        });


    } );
</script>