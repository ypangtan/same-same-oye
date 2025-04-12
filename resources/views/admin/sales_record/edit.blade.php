<?php
$sales_record_edit = 'sales_record_edit';
?>

<x-add-header title="{{ __( 'template.add_x', [ 'title' => __( 'template.sales_records' ) ] ) }}" />

    <x-form-card id="{{ $sales_record_edit }}" title="{{ __( 'template.general_info' ) }}">


        <x-input-field 
            id="{{ $sales_record_edit }}_customer_name"
            type="text"
            label="{{ __( 'sales_record.customer_name' ) }}"
        />

        <x-input-field 
            id="{{ $sales_record_edit }}_total_price"
            type="number"
            label="{{ __( 'sales_record.total_price' ) }}" 
        />

        <x-input-field 
            id="{{ $sales_record_edit }}_reference"
            type="text"
            label="{{ __( 'sales_record.reference' ) }}" 
        />
    </x-form-card>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let de = '#{{ $sales_record_edit }}',
                fileID = '';

        $( de + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.sales_record.index' ) }}';
        } );

        $( de + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            // formData.append( 'order_id', $( de + '_order_id' ).val() );
            formData.append( 'customer_name', $( de + '_customer_name' ).val() );
            // formData.append( 'facebook_name', $( de + '_facebook_name' ).val() );
            // formData.append( 'facebook_url', $( de + '_facebook_url' ).val() );
            // formData.append( 'live_id', $( de + '_live_id' ).val() );
            // formData.append( 'product_metas', $( de + '_product_metas' ).val() );
            formData.append( 'total_price', $( de + '_total_price' ).val() );
            // formData.append( 'payment_method', $( de + '_payment_method' ).val() );
            // formData.append( 'handler', $( de + '_handler' ).val() );
            // formData.append( 'remarks', $( de + '_remarks' ).val() );
            formData.append( 'reference', $( de + '_reference' ).val() );
            // formData.append( 'remarks', $( de + '_remarks' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.sales_record.updateSalesRecord' ) }}',
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
                            $( de + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getUser();

        function getUser() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.sales_record.oneSalesRecord' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    $( de + '_order_id' ).val( response.order_id );
                    $( de + '_customer_name' ).val( response.customer_name );
                    $( de + '_facebook_name' ).val( response.facebook_name );
                    $( de + '_facebook_url' ).val( response.facebook_url );
                    $( de + '_live_id' ).val( response.live_id );
                    $( de + '_product_metas' ).val( response.product_metas );
                    $( de + '_total_price' ).val( response.total_price );
                    $( de + '_payment_method' ).val( response.payment_method );
                    $( de + '_handler' ).val( response.handler );
                    $( de + '_remarks' ).val( response.remarks );
                    $( de + '_reference' ).val( response.reference );
                    $( de + '_remarks' ).val( response.remarks );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
        
    } );
</script>