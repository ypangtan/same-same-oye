<?php
$subscription_group_member_edit = 'subscription_group_member_edit';
?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $subscription_group_member_edit }}_leader" class="col-sm-5 col-form-label">{{ __( 'subscription_group_member.leader' ) }} </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $subscription_group_member_edit }}_leader">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $subscription_group_member_edit }}_user" class="col-sm-5 col-form-label">{{ __( 'subscription_group_member.user' ) }} </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $subscription_group_member_edit }}_user">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $subscription_group_member_edit }}_cancel" type="button" class="btn btn-sm btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $subscription_group_member_edit }}_submit" type="button" class="btn btn-sm btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        getSubscriptionGroupMember();

        let ue = '#{{ $subscription_group_member_edit }}';

        $( ue + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.subscription_group_member.index' ) }}';
        } );

        $( ue + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'leader', $( ue + '_leader' ).val() ?? '' );
            formData.append( 'user', $( ue + '_user' ).val() ?? '' );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.subscription_group_member.updateSubscriptionGroupMember' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.subscription_group_member.index' ) }}';
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

        function getSubscriptionGroupMember() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.subscription_group_member.oneSubscriptionGroupMember' ) }}',
                type: 'POST',
                data: {
                    id: '{{ request( 'id' ) }}',
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {

                    if( response.user != null ){
                        name = ( response.user.calling_code ? response.user.calling_code : '+60' ) + ( response.user.phone_number ? response.user.phone_number : '-' ) + ' (' + ( response.user.email ? response.user.email : '-' ) + ')';
                        let option1 = new Option( name, response.user.encrypted_id, true, true );
                        userSelect2.append( option1 );
                        userSelect2.trigger( 'change' );
                    }
                    
                    if( response.leader != null ){
                        name = ( response.leader.calling_code ? response.leader.calling_code : '+60' ) + ( response.leader.phone_number ? response.leader.phone_number : '-' ) + ' (' + ( response.leader.email ? response.leader.email : '-' ) + ')';
                        let option1 = new Option( name, response.leader.encrypted_id, true, true );
                        leaderSelect2.append( option1 );
                        leaderSelect2.trigger( 'change' );
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

        let leaderSelect2 = $( ue + '_leader' ).select2( {
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

    } );
</script>