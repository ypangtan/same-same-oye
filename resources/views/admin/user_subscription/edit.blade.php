<?php
$user_subscription_edit = 'user_subscription_edit';
?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $user_subscription_edit }}_user" class="col-sm-5 col-form-label">{{ __( 'user_subscription.user' ) }} </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $user_subscription_edit }}_user">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_subscription_edit }}_end_date" class="col-sm-5 col-form-label">{{ __( 'user_subscription.end_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $user_subscription_edit }}_end_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $user_subscription_edit }}_plan_name" class="col-sm-5 col-form-label">{{ __( 'user_subscription.plan_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $user_subscription_edit }}_plan_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $user_subscription_edit }}_cancel" type="button" class="btn btn-sm btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $user_subscription_edit }}_submit" type="button" class="btn btn-sm btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        getUserSubscription();

        let ue = '#{{ $user_subscription_edit }}';

        $( ue + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.user_subscription.index' ) }}';
        } );

        $( ue + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'user', $( ue + '_user' ).val() ?? '' );
            formData.append( 'end_date', $( ue + '_end_date' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.user_subscription.updateUserSubscription' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.user_subscription.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( ue + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();       
                    }
                }
            } );
        } );

        function getUserSubscription() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.user_subscription.oneUserSubscription' ) }}',
                type: 'POST',
                data: {
                    id: '{{ request( 'id' ) }}',
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {

                    if( response.user != null ){
                        name = ( response.user.calling_code ? response.user.calling_code : '+60' ) + ( response.user.phone_number ? response.user.phone_number : '-' ) + ' (' + ( response.user.email ? response.user.email : '-' ) + ')',
                        let option1 = new Option( name, response.user.encrypted_id, true, true );
                        userSelect2.append( option1 );
                        userSelect2.trigger( 'change' );
                    }
                    $( ue + '_end_date' ).val( response.end_date );
                    if( response.type != 2 ) {
                        $( ue + '_submit' ).addClass( 'd-none' );

                        if( response.plan ) {
                            $( '.plan_block' ).removeClass( 'd-none' );
                            $( ue + '_plan_name' ).val( response.plan.name );
                        }
                    }

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

        let userSelect2 = $( ue + '_user' ).select2( {
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            allowClear: false,
            closeOnSelect: true,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.user.allUsers' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        mixed_search: params.term, // search term
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
                            id: v.encrypted_id,
                            text: ( v.calling_code ? v.calling_code : '+60' ) + ( v.phone_number ? v.phone_number : '-' ) + ' (' + ( v.email ? v.email : '-' ) + ')',
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

        $( ue + '_end_date' ).flatpickr( {
            mode: 'date',
            disableMobile: true,
        } );
    } );
</script>