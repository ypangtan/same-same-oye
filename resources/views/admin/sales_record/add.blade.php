<?php
$sales_record_create = 'sales_record_create';
?>

<x-add-header title="{{ __( 'template.add_x', [ 'title' => __( 'template.sales_records' ) ] ) }}" />

    <x-form-card id="{{ $sales_record_create }}" title="{{ __( 'template.general_info' ) }}">

        <x-input-field 
            id="{{ $sales_record_create }}_customer_name"
            type="text"
            label="{{ __( 'sales_record.customer_name' ) }}"
        />

        <x-input-field 
            id="{{ $sales_record_create }}_total_price"
            type="number"
            label="{{ __( 'sales_record.total_price' ) }}" 
        />

        <x-input-field 
            id="{{ $sales_record_create }}_reference"
            type="text"
            label="{{ __( 'sales_record.reference' ) }}" 
        />
    </x-form-card>

    
<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let dc = '#{{ $sales_record_create }}',
                fileID = '';

        $( dc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.sales_record.index' ) }}';
        } );

        $( dc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'order_id', $( dc + '_order_id' ).val() );
            formData.append( 'customer_name', $( dc + '_customer_name' ).val() );
            // formData.append( 'facebook_name', $( dc + '_facebook_name' ).val() );
            // formData.append( 'facebook_url', $( dc + '_facebook_url' ).val() );
            // formData.append( 'live_id', $( dc + '_live_id' ).val() );
            // formData.append( 'product_metas', $( dc + '_product_metas' ).val() );
            formData.append( 'total_price', $( dc + '_total_price' ).val() );
            // formData.append( 'payment_method', $( dc + '_payment_method' i
            formData.append( 'reference', $( dc + '_reference' ).val() );
            // formData.append( 'remarks', $( dc + '_remarks' ).val() );
            
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.sales_record.createSalesRecord' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.sales_record.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( dc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

    } );
</script>