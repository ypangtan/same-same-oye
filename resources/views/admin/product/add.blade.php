<?php
$product_create = 'product_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.products' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                
                <div class="mb-3 row">
                    <label for="{{ $product_create }}_title" class="col-sm-5 form-label">{{ __( 'product.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $product_create }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $product_create }}_code" class="col-sm-5 form-label">{{ __( 'product.code' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $product_create }}_code">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_create }}_description" class="col-sm-5 form-label">{{ __( 'product.description' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" name="{{ $product_create }}_description" id="{{ $product_create }}_description" rows="5"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_create }}_price" class="col-sm-5 form-label">{{ __( 'product.price' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $product_create }}_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_create }}_discount_price" class="col-sm-5 form-label">{{ __( 'product.discount_price' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $product_create }}_discount_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            
                <div class="mb-3">
                    <label>{{ __( 'product.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $product_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>           
                <div class="text-end">
                    <button id="{{ $product_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $product_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $product_create }}',
                fileID = '';

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.product.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'code', $( fc + '_code' ).val() );
            formData.append( 'title', $( fc + '_title' ).val() );
            formData.append( 'description', $( fc + '_description' ).val() );
            formData.append( 'price', $( fc + '_price' ).val() );
            formData.append( 'discount_price', $( fc + '_discount_price' ).val() );
            formData.append( 'image', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.product.createProduct' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
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