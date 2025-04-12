<?php
$bundle_create = 'bundle_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.bundles' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $bundle_create }}_title" class="col-sm-5 col-form-label">{{ __( 'bundle.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $bundle_create }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $bundle_create }}_description" class="col-sm-5 col-form-label">{{ __( 'bundle.description' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $bundle_create }}_description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'bundle.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $bundle_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.bundle_products' ) }}</h5>
                <div class="col-sm-12 mb-3 row">
                    <label for="{{ $bundle_create }}_product" class="form-label">{{ __( 'template.products' ) }}</label>
                    <select class="form-select" id="{{ $bundle_create }}_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.products' ) ] ) }}" multiple="multiple">
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $bundle_create }}_promotion_enabled" class="col-sm-5 col-form-label">{{ __( 'product.promotion_enabled' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $bundle_create }}_promotion_enabled" name="{{ $bundle_create }}_featured" value="1" onchange="this.nextElementSibling.textContent = this.checked ? '{{ __('Enabled') }}' : '{{ __('Disabled') }}';">
                            <label class="form-check-label" for="{{ $bundle_create }}_featured">{{ __('Disabled') }}</label>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $bundle_create }}_price" class="col-sm-5 col-form-label">{{ __( 'bundle.price' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $bundle_create }}_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $bundle_create }}_promotion_price" class="col-sm-5 col-form-label">{{ __( 'bundle.promotion_price' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $bundle_create }}_promotion_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $bundle_create }}_promotion_start" class="col-sm-5 col-form-label">{{ __( 'bundle.promotion_start' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $bundle_create }}_promotion_start">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $bundle_create }}_promotion_end" class="col-sm-5 col-form-label">{{ __( 'bundle.promotion_end' ) }}</label>
                    <div class="col-sm-7">
                        <input type="nunber" class="form-control" id="{{ $bundle_create }}_promotion_end">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <button id="{{ $bundle_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                &nbsp;
                <button id="{{ $bundle_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $bundle_create }}',
                fileID = '';

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.bundle.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'title', $( fc + '_title' ).val() );
            formData.append( 'description', $( fc + '_description' ).val() );
            formData.append( 'image', fileID );
            formData.append( 'products', $(fc + '_product').val() );

            formData.append( 'price', $(fc + '_price').val() );
            formData.append( 'promotion_enabled', $(fc + '_promotion_enabled').is(':checked') ? 1 : 0 );
            formData.append( 'price', $(fc + '_price').val() );
            formData.append( 'promotion_price', $(fc + '_promotion_price').val() );
            formData.append( 'promotion_start', $(fc + '_promotion_start').val() );
            formData.append( 'promotion_end', $(fc + '_promotion_end').val() );
            
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.bundle.createBundle' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.bundle.index' ) }}';
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

        $( fc + '_promotion_start' ).flatpickr( {
            disableMobile: true,
        } );

        $( fc + '_promotion_end' ).flatpickr( {
            disableMobile: true,
        } );


    } );
</script>