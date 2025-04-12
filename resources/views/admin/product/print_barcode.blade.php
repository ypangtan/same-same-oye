<?php
$generate_barcodes = 'generate_barcodes';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.generate_barcodes' ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.print_x', [ 'title' => Str::singular( __( 'template.product_barcodes' ) ) ] ) }}</h5>
                <div class="col-sm-12 mb-3 row">
                    <label for="{{ $generate_barcodes }}_product" class="form-label">{{ __( 'template.products' ) }}</label>
                    <select class="form-select" id="{{ $generate_barcodes }}_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.products' ) ] ) }}" multiple="multiple">
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
                @if( 1 == 2 )
                <div class="col-sm-12 mb-3 row">
                    <label for="{{ $generate_barcodes }}_width" class="form-label">{{ __( 'template.width' ) }}</label>
                    <input type="number" id="{{ $generate_barcodes }}_width" class="form-control" value="2" min="1" step="0.1">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-sm-12 mb-3 row">
                    <label for="{{ $generate_barcodes }}_height" class="form-label">{{ __( 'template.height' ) }}</label>
                    <input type="number" id="{{ $generate_barcodes }}_height" class="form-control" value="30" min="1" step="1">
                    <div class="invalid-feedback"></div>
                </div>
                @endif
                <!-- Preview Section -->
                <div id="{{ $generate_barcodes }}_preview" class="col-sm-12 mb-3 row h-auto">
                    <label class="form-label">{{ __( 'template.preview' ) }}</label>
                    <div id="{{ $generate_barcodes }}_preview_content"></div>
                </div>
            </div>
            <div class="text-end">
                <button id="{{ $generate_barcodes }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                &nbsp;
                <button id="{{ $generate_barcodes }}_submit" type="button" class="btn btn-primary">{{ __( 'template.generate_barcodes' ) }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $generate_barcodes }}',
                fileID = '';

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.bundle.index' ) }}';
        } );

        function updateBarcodePreview() {
            $.ajax({
                url: '{{ route('admin.product.previewBarcode') }}', // Add a route to handle previews
                type: 'POST',
                data: {
                    product: $(fc + '_product').val(),
                    width: 2,
                    height: 30,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    const previewsContainer = document.getElementById('generate_barcodes_preview_content');
                    
                    // Clear the container before inserting new previews
                    previewsContainer.innerHTML = ''; 

                    response.barcodes.forEach(barcode => {
                        const previewHtml = `
                            <div style="margin-bottom: 20px;">
                                <h3>${barcode.product_name}</h3>
                                <p><strong>Price:</strong> ${barcode.product_price}</p>
                                <div>${barcode.barcodeHtml}</div>
                            </div>
                        `;
                        previewsContainer.innerHTML += previewHtml;
                    });
                },
                error: function () {
                    $(fc + '_preview_content').html('<p>{{ __( "template.error_loading_preview" ) }}</p>');
                }
            });
        }

        $(fc + '_product, ' + fc + '_width, ' + fc + '_height').change(updateBarcodePreview);

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'product', $(fc + '_product').val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.product.generateBarcodes' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );

                    const blob = new Blob([response], { type: 'application/pdf' });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'barcode.pdf';
                    link.click();

                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.product.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {

                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) 
                        {
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
            language: '{{ App::getLocale() }}',
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
    } );
</script>