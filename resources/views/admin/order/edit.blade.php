<?php
$order_create = 'order_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.orders' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row gx-5">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_user" class="col-sm-4 col-form-label">{{ __( 'order.user' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_create }}_user" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.user' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_vending_machine" class="col-sm-4 col-form-label">{{ __( 'order.vending_machine' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_create }}_vending_machine" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.vending_machine' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_product" class="col-sm-4 col-form-label">{{ __( 'order.product' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_create }}_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.product' ) ] ) }}"  multiple="multiple">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-12 order-details">
            </div>

        </div>
        <div class="text-end">
            <button id="{{ $order_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
            &nbsp;
            <button id="{{ $order_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let oc = '#{{ $order_create }}',
            odIndex = 1,
            orderDetailsContainer = $(".order-details");

        $( oc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.order.index' ) }}';
        } );

        $( oc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( '_token', '{{ csrf_token() }}' );
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'user', $( oc + '_user').val() );
            formData.append( 'vending_machine', $( oc + '_vending_machine').val()  );
            // Loop through each product card to get the selected data
            $('.item-card').each(function () {
                const productId = $(this).attr('id').replace('product-card-', '');
                const froyoSelect = $(this).find(`#froyo-${productId}`);
                const syrupSelect = $(this).find(`#syrup-${productId}`);
                const toppingSelect = $(this).find(`#topping-${productId}`);

                // Get selected froyo, syrup, and topping values
                const froyoSelected = froyoSelect.select2('data').map(f => ({ id: f.id, price: parseFloat(f.price || 0) }));
                const syrupSelected = syrupSelect.select2('data').map(s => ({ id: s.id, price: parseFloat(s.price || 0) }));
                const toppingSelected = toppingSelect.select2('data').map(t => ({ id: t.id, price: parseFloat(t.price || 0) }));

                // Calculate subtotal dynamically
                const froyoSubtotal = froyoSelected.reduce((sum, item) => sum + item.price, 0);
                const syrupSubtotal = syrupSelected.reduce((sum, item) => sum + item.price, 0);
                const toppingSubtotal = toppingSelected.reduce((sum, item) => sum + item.price, 0);
                const totalSubtotal = froyoSubtotal + syrupSubtotal + toppingSubtotal;

                // Update the subtotal field in the UI
                $(this).find(`#subtotal-${productId}`).val(totalSubtotal.toFixed(2));

                // Append product details to FormData
                formData.append('products[]', JSON.stringify({
                    productId: productId,
                    froyo: froyoSelected.map(f => f.id), // Only send IDs
                    syrup: syrupSelected.map(s => s.id), // Only send IDs
                    topping: toppingSelected.map(t => t.id), // Only send IDs
                    subtotal: totalSubtotal
                }));
            });

            $.ajax( {
                url: '{{ route( 'admin.order.updateOrder' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event ) {
                        window.location.href = '{{ route( 'admin.module_parent.order.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {

                            if ( key.includes( 'order_items' ) ) {

                                let stringKey = key.split( '.' );

                                $( '#order_details_' + stringKey[1] ).find( oc + '_' + stringKey[2] ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );

                                return true;
                            }else{

                                $( oc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                            }

                        } );

                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        $( oc + "_product" ).on("select2:select", function (e) {
            const selectedItem = e.params.data;
            const productId = selectedItem.id;
            const productName = selectedItem.text;
            const productPrice = selectedItem.price;
            const maxFroyo = selectedItem.maxFroyo;
            const maxSyrup = selectedItem.maxSyrup;
            const maxTopping = selectedItem.maxTopping;

            // Check if the product card already exists
            if ($(`#product-card-${productId}`).length === 0) {
                const cardHtml = `
                    <div class="mb-4 item-card" id="product-card-${productId}" data-max-froyo="${maxFroyo}" data-max-syrup="${maxSyrup}" data-max-topping="${maxTopping}">
                        <h5 class="card-title">${productName}</h5>
                        <h6 class="card-title">You may choose: ${maxFroyo} Froyo(s), ${maxSyrup} Syrup(s), ${maxTopping} Topping(s) extra selection will be charged</h6>
                        
                        <div class="mb-3 row">
                            <label for="froyo-${productId}" class="col-sm-4 col-form-label">{{ __( 'order.froyo' ) }}</label>
                            <div class="col-sm-6">
                                <select class="form-select select2-froyo" id="froyo-${productId}" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.froyo' ) ] ) }}" multiple="multiple">
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3 row">
                            <label for="syrup-${productId}" class="col-sm-4 col-form-label">{{ __( 'order.syrup' ) }}</label>
                            <div class="col-sm-6">
                                <select class="form-select select2-syrup" id="syrup-${productId}" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.syrup' ) ] ) }}" multiple="multiple">
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3 row">
                            <label for="topping-${productId}" class="col-sm-4 col-form-label">{{ __( 'order.topping' ) }}</label>
                            <div class="col-sm-6">
                                <select class="form-select select2-topping" id="topping-${productId}" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.topping' ) ] ) }}" multiple="multiple">
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="topping-${productId}" class="col-sm-4 col-form-label">{{ __( 'order.subtotal' ) }}</label>
                            <div class="col-sm-6">
                                <input type="number" class="form-control" id="subtotal-${productId}" value="${productPrice}" placeholder="Enter quantity">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-danger btn-sm remove-product-card" data-id="${productId}">
                            {{ __( 'order.remove' ) }}
                        </button>

                    </div>
                `;

                // Append the new product card to the container
                orderDetailsContainer.append(cardHtml);
        
                $(`#froyo-${productId}`).select2( {
                    language: '{{ App::getLocale() }}',
                    theme: 'bootstrap-5',
                    width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                    placeholder: $( this ).data( 'placeholder' ),
                    closeOnSelect: true,
                    ajax: {
                        method: 'POST',
                        url: '{{ route( 'admin.froyo.allFroyos' ) }}',
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

                            data.froyos.map( function( v, i ) {
                                processedResult.push( {
                                    id: v.id,
                                    text: v.title + ' RM (' + v.price + ')',
                                    price: v.price,
                                    productId : productId,
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

                $(`#syrup-${productId}`).select2( {
                    language: '{{ App::getLocale() }}',
                    theme: 'bootstrap-5',
                    width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                    placeholder: $( this ).data( 'placeholder' ),
                    closeOnSelect: true,
                    ajax: {
                        method: 'POST',
                        url: '{{ route( 'admin.syrup.allSyrups' ) }}',
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

                            data.syrups.map( function( v, i ) {
                                processedResult.push( {
                                    id: v.id,
                                    text: v.title + ' RM (' + v.price + ')',
                                    price: v.price,
                                    productId : productId,
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

                $(`#topping-${productId}`).select2( {
                    language: '{{ App::getLocale() }}',
                    theme: 'bootstrap-5',
                    width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                    placeholder: $( this ).data( 'placeholder' ),
                    closeOnSelect: true,
                    ajax: {
                        method: 'POST',
                        url: '{{ route( 'admin.topping.allToppings' ) }}',
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

                            data.toppings.map( function( v, i ) {
                                processedResult.push( {
                                    id: v.id,
                                    text: v.title + ' RM (' + v.price + ')',
                                    price: v.price,
                                    productId : productId,
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

            }
            recalculateTotal();
        });

        // Handle unselect event for Select2

        $(document).on("select2:unselect", ".select2-froyo, .select2-syrup, .select2-topping", function (e) {
            const unselectedItem = e.params.data; // Get unselected item
            const select2Type = $(this).attr("class").includes("froyo") ? "froyo" 
                                : $(this).attr("class").includes("syrup") ? "syrup" 
                                : "topping"; // Determine which select2 triggered
            const productSelect = $(oc + `_product`).select2("data");
            const selectedProduct = productSelect.find(p => (p.id) === (unselectedItem.productId));
            const productCardId = $(this).closest('.mb-4').attr('id'); // Get the unique card ID

            if (!selectedProduct) return; // No product found, exit

            const maxFroyo = selectedProduct.maxFroyo || 0;
            const maxSyrup = selectedProduct.maxSyrup || 0;
            const maxTopping = selectedProduct.maxTopping || 0;

            // Get updated counts after unselecting
            const froyoCount = $(".select2-froyo").select2("data").length;
            const syrupCount = $(".select2-syrup").select2("data").length;
            const toppingCount = $(".select2-topping").select2("data").length;

            // Initialize subtotal with the product price
            let subtotal = parseFloat(selectedProduct.price);
            // let subtotal = parseFloat($('#subtotal-' + unselectedItem.productId).val());

            // Iterate over froyo, syrup, and topping select elements within the current product card
            $(`#${productCardId} .select2-froyo, #${productCardId} .select2-syrup, #${productCardId} .select2-topping`).each(function () {
                const select2 = $(this);

                const type = select2.hasClass("select2-froyo")
                    ? "froyo"
                    : select2.hasClass("select2-syrup")
                    ? "syrup"
                    : select2.hasClass("select2-topping")
                    ? "topping"
                    : "";

                if (!type) return; // Skip if no valid type

                // Get selected items for each category
                const selectedItems = select2.select2("data");
                let maxSelection = 0;

                // Dynamic selection counts based on type
                if (type === "froyo") maxSelection = getMaxSelectionForProductCard(productCardId, "froyo");
                if (type === "syrup") maxSelection = getMaxSelectionForProductCard(productCardId, "syrup");
                if (type === "topping") maxSelection = getMaxSelectionForProductCard(productCardId, "topping");

                // Handle excess selections
                // if (selectedItems.length > maxSelection) {
                    // Sort selected items by price in descending order
                    const sortedItems = selectedItems.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));

                    // Keep only the highest-priced items
                    const itemsToAdd = sortedItems;

                    // Add the prices of the selected items to the subtotal
                    itemsToAdd.forEach(item => {
                        subtotal += parseFloat(item.price || 0);
                    });
                // }
            });

            // Update subtotal input
            const subtotalInput = $(`#subtotal-${selectedProduct.id}`);
            if (subtotalInput.length > 0) {
                subtotalInput.val(subtotal);
            } else {
                // Remove the subtotal input if no items are selected for the product
                $(`#subtotal-${selectedProduct.id}`).closest(".mb-3.row").remove();
            }

            // Recalculate the total
            recalculateTotal();
        });

        $(document).on("select2:select", ".select2-froyo, .select2-syrup, .select2-topping", function (e) {

            const selectedItem = e.params.data; // Get selected item
            const select2Type = $(this).attr("class").includes("froyo") ? "froyo" 
                                : $(this).attr("class").includes("syrup") ? "syrup" 
                                : "topping"; // Determine which select2 triggered
            const productSelect = $(oc + `_product`).select2("data");
            const selectedProduct = productSelect.find(p => (p.id) === (selectedItem.productId));
            const productCardId = $(this).closest('.mb-4').attr('id'); // Get the unique card ID
            // let subtotal = 0; // Initialize subtotal for the current product card
            // let subtotal = parseFloat($('#subtotal-' + selectedItem.productId).val());
            let subtotal = parseFloat(selectedProduct.price);

            // Iterate over froyo, syrup, and topping select elements within the current product card
            $(`#${productCardId} .select2-froyo, #${productCardId} .select2-syrup, #${productCardId} .select2-topping`).each(function () {
                const select2 = $(this);

                const type = select2.hasClass("select2-froyo")
                    ? "froyo"
                    : select2.hasClass("select2-syrup")
                    ? "syrup"
                    : select2.hasClass("select2-topping")
                    ? "topping"
                    : "";

                if (!type) return; // Skip if no valid type

                // Get selected items for each category
                const selectedItems = select2.select2("data");
                let maxSelection = 0;

                // Dynamic selection counts based on type
                if (type === "froyo") maxSelection = getMaxSelectionForProductCard(productCardId, "froyo");
                if (type === "syrup") maxSelection = getMaxSelectionForProductCard(productCardId, "syrup");
                if (type === "topping") maxSelection = getMaxSelectionForProductCard(productCardId, "topping");

                // Handle excess selections
                // if (selectedItems.length > maxSelection) {
                    // Sort selected items by price in descending order
                    const sortedItems = selectedItems.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));

                    // Keep only the highest-priced items
                    const itemsToAdd = sortedItems;

                    // Add the prices of the selected items to the subtotal
                    itemsToAdd.forEach(item => {
                        subtotal += parseFloat(item.price || 0);
                    });
                // }
            });

            // Update or create the subtotal input for the product card
            const subtotalInput = $(`#subtotal-${selectedProduct.id}`);
            if (subtotalInput.length > 0) {
                subtotalInput.val(subtotal.toFixed(2));
            } else {
                // Append subtotal input if it doesn't exist
                $(`#order_details_${selectedProduct.id}`).append(`
                    <div class="mb-3 row">
                        <label for="subtotal-${selectedProduct.id}" class="col-sm-4 col-form-label">Subtotal</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" id="subtotal-${productCardId}" value="${subtotal.toFixed(2)}" readonly>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                `);
            }

            // Recalculate the total
            recalculateTotal();
        });

        function getMaxSelectionForProductCard(cardId, type) {
            let maxSelection = 0;

            if (type === 'froyo') {
                maxSelection = $(`#${cardId}`).data('max-froyo') || 1;  // Default to 1 if no value is found
            } else if (type === 'syrup') {
                maxSelection = $(`#${cardId}`).data('max-syrup') || 1;  // Default to 1 if no value is found
            } else if (type === 'topping') {
                maxSelection = $(`#${cardId}`).data('max-topping') || 1;  // Default to 1 if no value is found
            }

            return maxSelection;
        }

        // Handle unselect event for Select2
        $( oc + "_product" ).on("select2:unselect", function (e) {
            const unselectedItem = e.params.data;
            const productId = unselectedItem.id;

            // Remove the product card
            $(`#product-card-${productId}`).remove();
            const select2 = $(oc + "_product");
            const selectedItems = select2.val().filter(item => item != productId);
            select2.val(selectedItems).trigger("change");
            recalculateTotal();
        });

        // Function to recalculate the total price
        function recalculateTotal() {
            let total = 0;
            $("input[id^='subtotal-']").each(function () {
                total += parseFloat($(this).val()) || 0;
            });

            // Update or create total input
            const totalInput = $("#order-total");
            if (totalInput.length > 0) {
                totalInput.val(parseFloat(total).toFixed(2));
            } else {
                $(".order-details").after(`
                    <div class="col-md-12 col-lg-12">
                        <div class="mb-3 row">
                            <label for="order-total" class="col-sm-4 col-form-label">RM {{  __('order.total') }}</label>
                            <div class="col-sm-6">
                                <input type="number" class="form-control" id="order-total" value="${total}" readonly>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                `);
            }
        }

        // Handle remove button click
        orderDetailsContainer.on("click", ".remove-product-card", function () {
            const productId = $(this).data("id");

            // Remove the product card
            $(`#product-card-${productId}`).remove();

            // Deselect the product in the Select2 dropdown
            const select2 = $(oc + "_product");
            const selectedItems = select2.val().filter(item => item != productId);
            select2.val(selectedItems).trigger("change");
            recalculateTotal();

        });
        
        let userSelect2 = $( oc + '_user' ).select2( {
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
                            text: v.username,
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
        
        let vendingMachineSelect2 = $( oc + '_vending_machine' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.vending_machine.allVendingMachines' ) }}',
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

                    data.vending_machines.map( function( v, i ) {
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
        
        let productSelect2 = $( oc + '_product' ).select2( {
            language: '{{ App::getLocale() }}',
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

                    data.products.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id + '-' + Date.now(),
                            text: v.title,
                            maxFroyo: v.default_froyo_quantity,
                            maxSyrup: v.default_syrup_quantity,
                            maxTopping: v.default_topping_quantity,
                            price: v.price,
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

        // get order details
        getOrder();

        function getOrder() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.order.oneOrder' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    if ( response.user ) {
                        let option1 = new Option( response.user.username, response.user.id, true, true );
                        userSelect2.append( option1 );
                        userSelect2.trigger( 'change' );
                    }

                    if ( response.vending_machine ) {
                        let option1 = new Option( response.vending_machine.title, response.vending_machine.id, true, true );
                        vendingMachineSelect2.append( option1 );
                        vendingMachineSelect2.trigger( 'change' );
                    }

                    response.orderMetas.forEach((orderMeta, index) => {

                        const productId = orderMeta.product.id + '-' + Date.now();
                        const productName = orderMeta.product.title;
                        const productPrice = orderMeta.product.price;
                        const maxFroyo = orderMeta.product.default_froyo_quantity;
                        const maxSyrup = orderMeta.product.default_syrup_quantity;
                        const maxTopping = orderMeta.product.default_topping_quantity;
                        const subTotal = orderMeta.subtotal;

                        if ($(`#product-card-${productId}`).length === 0) {
                            const cardHtml = `
                                <div class="mb-4 item-card" id="product-card-${productId}" data-max-froyo="${maxFroyo}" data-max-syrup="${maxSyrup}" data-max-topping="${maxTopping}">
                                    <h5 class="card-title">${productName}</h5>
                                    <h6 class="card-title">You may choose: ${maxFroyo} Froyo(s), ${maxSyrup} Syrup(s), ${maxTopping} Topping(s) extra selection will be charged</h6>
                                    
                                    <div class="mb-3 row">
                                        <label for="froyo-${productId}" class="col-sm-4 col-form-label">{{ __( 'order.froyo' ) }}</label>
                                        <div class="col-sm-6">
                                            <select class="form-select select2-froyo" id="froyo-${productId}" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.froyo' ) ] ) }}" multiple="multiple">
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3 row">
                                        <label for="syrup-${productId}" class="col-sm-4 col-form-label">{{ __( 'order.syrup' ) }}</label>
                                        <div class="col-sm-6">
                                            <select class="form-select select2-syrup" id="syrup-${productId}" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.syrup' ) ] ) }}" multiple="multiple">
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3 row">
                                        <label for="topping-${productId}" class="col-sm-4 col-form-label">{{ __( 'order.topping' ) }}</label>
                                        <div class="col-sm-6">
                                            <select class="form-select select2-topping" id="topping-${productId}" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.topping' ) ] ) }}" multiple="multiple">
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="topping-${productId}" class="col-sm-4 col-form-label">{{ __( 'order.subtotal' ) }}</label>
                                        <div class="col-sm-6">
                                            <input type="number" class="form-control" id="subtotal-${productId}" value="${subTotal}" placeholder="Enter quantity">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-danger btn-sm remove-product-card" data-id="${productId}">
                                        {{ __( 'order.remove' ) }}
                                    </button>

                                </div>
                            `;

                            // Append the new product card to the container
                            orderDetailsContainer.append(cardHtml);
                    
                            $(`#froyo-${productId}`).select2( {
                                language: '{{ App::getLocale() }}',
                                theme: 'bootstrap-5',
                                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                                placeholder: $( this ).data( 'placeholder' ),
                                closeOnSelect: true,
                                ajax: {
                                    method: 'POST',
                                    url: '{{ route( 'admin.froyo.allFroyos' ) }}',
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

                                        data.froyos.map( function( v, i ) {
                                            processedResult.push( {
                                                id: v.id,
                                                text: v.title + ' RM (' + v.price + ')',
                                                price: v.price,
                                                productId : productId,
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

                            $(`#syrup-${productId}`).select2( {
                                language: '{{ App::getLocale() }}',
                                theme: 'bootstrap-5',
                                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                                placeholder: $( this ).data( 'placeholder' ),
                                closeOnSelect: true,
                                ajax: {
                                    method: 'POST',
                                    url: '{{ route( 'admin.syrup.allSyrups' ) }}',
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

                                        data.syrups.map( function( v, i ) {
                                            processedResult.push( {
                                                id: v.id,
                                                text: v.title + ' RM (' + v.price + ')',
                                                price: v.price,
                                                productId : productId,
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

                            $(`#topping-${productId}`).select2( {
                                language: '{{ App::getLocale() }}',
                                theme: 'bootstrap-5',
                                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                                placeholder: $( this ).data( 'placeholder' ),
                                closeOnSelect: true,
                                ajax: {
                                    method: 'POST',
                                    url: '{{ route( 'admin.topping.allToppings' ) }}',
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

                                        data.toppings.map( function( v, i ) {
                                            processedResult.push( {
                                                id: v.id,
                                                text: v.title + ' RM (' + v.price + ')',
                                                price: v.price,
                                                productId : productId,
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
                        }

                        orderMeta.froyo.forEach((froyoMeta, index) => {

                            let option = new Option(froyoMeta.title + ' RM(' + froyoMeta.price + ')', froyoMeta.id, true, true); 
                            $(`#froyo-${productId}`).append(option);

                            let data = {
                                id: froyoMeta.id,
                                price: froyoMeta.price,
                                productId: productId,
                            };

                            let $current_option_data = $(`#froyo-${productId}`).select2('data').find(function (currentOption) {
                                return currentOption.id == data['id']
                            });

                            if ($current_option_data) {
                                $current_option_data['price'] = data['price'];
                                $current_option_data['productId'] = data['productId'];
                            }

                        });

                        orderMeta.syrup.forEach((syrupMeta, index) => {

                            let option = new Option(syrupMeta.title + ' RM(' + syrupMeta.price + ')', syrupMeta.id, true, true); 
                            $(`#syrup-${productId}`).append(option);

                            let data = {
                                id: syrupMeta.id,
                                price: syrupMeta.price,
                                productId: productId,
                            };

                            let $current_option_data = $(`#syrup-${productId}`).select2('data').find(function (currentOption) {
                                return currentOption.id == data['id']
                            });

                            if ($current_option_data) {
                                $current_option_data['price'] = data['price'];
                                $current_option_data['productId'] = data['productId'];
                            }

                        });

                        orderMeta.topping.forEach((toppingMeta, index) => {

                            let option = new Option(toppingMeta.title + ' RM(' + toppingMeta.price + ')', toppingMeta.id, true, true); 
                            $(`#topping-${productId}`).append(option);

                            let data = {
                                id: toppingMeta.id,
                                price: toppingMeta.price,
                                productId: productId,
                            };

                            let $current_option_data = $(`#topping-${productId}`).select2('data').find(function (currentOption) {
                                return currentOption.id == data['id']
                            });

                            if ($current_option_data) {
                                $current_option_data['price'] = data['price'];
                                $current_option_data['productId'] = data['productId'];
                            }

                        });

                        let option = new Option(productName, productId, true, true); 
                        productSelect2.append(option);

                        let data = {
                            id: productId,
                            maxFroyo: maxFroyo,
                            maxSyrup: maxSyrup,
                            maxTopping: maxTopping,
                            price: productPrice,
                        };

                        let $current_option_data = $( oc + '_product' ).select2('data').find(function (currentOption) {
                            return currentOption.id == data['id']
                        });

                        if ($current_option_data) {
                            $current_option_data['maxFroyo'] = data['maxFroyo'];
                            $current_option_data['maxSyrup'] = data['maxSyrup'];
                            $current_option_data['maxTopping'] = data['maxTopping'];
                            $current_option_data['price'] = data['price'];
                        }

                    });

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

    } );
</script>