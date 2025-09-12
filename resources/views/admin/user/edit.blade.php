<?php
$user_edit = 'user_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.users' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $user_edit }}_referral" class="col-sm-5 col-form-label">{{ __( 'user.referral' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-control select2" id="{{ $user_edit }}_referral" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'template.users' ) ] ) }}"></select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_edit }}_date_of_birth" class="col-sm-5 col-form-label">{{ __( 'user.date_of_birth' ) }}</label>
                    <div class="col-sm-7">
                        <input type="date" class="form-control" id="{{ $user_edit }}_date_of_birth">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @if( 1 == 2 )

                <div class="mb-3 row">
                    <label for="{{ $user_edit }}_account_type" class="col-sm-5 col-form-label">{{ __( 'user.account_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $user_edit }}_account_type" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'user.account_type' ) ] ) }}</option>
                            <option value="1">{{ __( 'user.personal' ) }}</option>
                            <option value="2">{{ __( 'user.company' ) }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_edit }}_username" class="col-sm-5 col-form-label">{{ __( 'user.username' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $user_edit }}_username">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @endif
                <div class="mb-3 row">
                    <label for="{{ $user_edit }}_email" class="col-sm-5 col-form-label">{{ __( 'user.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $user_edit }}_email">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_edit }}_first_name" class="col-sm-5 col-form-label">{{ __( 'user.first_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $user_edit }}_first_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_edit }}_last_name" class="col-sm-5 col-form-label">{{ __( 'user.last_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $user_edit }}_last_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_edit }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'user.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <div class="input-group">
                            <button class="flex-shrink-0 inline-flex items-center input-group-text" type="button">
                                +60
                            </button>
                            <input type="text" class="form-control" id="{{ $user_edit }}_phone_number">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>                    
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_edit }}_password" class="col-sm-5 col-form-label">{{ __( 'user.password' ) }}</label>
                    <div class="col-sm-7">
                        <input type="password" class="form-control" id="{{ $user_edit }}_password" autocomplete="new-password" placeholder="{{ __( 'template.leave_blank' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row d-none">
                    <label for="{{ $user_edit }}_address_1" class="col-sm-5 col-form-label">{{ __( 'customer.address_1' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $user_edit }}_address_1" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $user_edit }}_address_2" class="col-sm-5 col-form-label">{{ __( 'customer.address_2' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $user_edit }}_address_2" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $user_edit }}_city" class="col-sm-5 col-form-label">{{ __( 'customer.city' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $user_edit }}_city" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $user_edit }}_state" class="col-sm-5 col-form-label">{{ __( 'customer.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $user_edit }}_state" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'customer.state' ) ] ) }}</option>
                            <option value="Johor">Johor</option>
                            <option value="Kedah">Kedah</option>
                            <option value="Kelantan">Kelantan</option>
                            <option value="Malacca">Malacca</option>
                            <option value="Negeri Sembilan">Negeri Sembilan</option>
                            <option value="Pahang">Pahang</option>
                            <option value="Penang">Penang</option>
                            <option value="Perlis">Perlis</option>
                            <option value="Sabah">Sabah</option>
                            <option value="Sarawak">Sarawak</option>
                            <option value="Selangor">Selangor</option>
                            <option value="Terengganu">Terengganu</option>
                            <option value="Kuala Lumpur">Kuala Lumpur</option>
                            <option value="Labuan">Labuan</option>
                            <option value="Putrajaya">Putrajaya</option>
                            <option value="Perak">Perak</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $user_edit }}_postcode" class="col-sm-5 col-form-label">{{ __( 'customer.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $user_edit }}_postcode" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $user_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $user_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let de = '#{{ $user_edit }}',
                fileID = '';

        $( de + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.user.index' ) }}';
        } );

        let dateOfBirth = $( de + '_date_of_birth' ).flatpickr( {
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( de + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            // formData.append( 'username', $( de + '_username' ).val() );
            formData.append( 'referral_id', $( de + '_referral' ).val() ?? '' );
            formData.append( 'email', $( de + '_email' ).val() );
            formData.append( 'first_name', $( de + '_first_name' ).val() );
            formData.append( 'last_name', $( de + '_last_name' ).val() );
            
            formData.append( 'phone_number', $( de + '_phone_number' ).val() );
            formData.append( 'password', $( de + '_password' ).val() );
            formData.append( 'address_1', $( de + '_address_1' ).val() );
            formData.append( 'address_2', $( de + '_address_2' ).val() );
            formData.append( 'city', $( de + '_city' ).val() );
            formData.append( 'state', $( de + '_state' ).val() );
            formData.append( 'postcode', $( de + '_postcode' ).val() );
            formData.append( 'date_of_birth', $( de + '_date_of_birth' ).val() );
            // formData.append( 'account_type', $( de + '_account_type' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.user.updateUser' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.user.index' ) }}';
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
                url: '{{ route( 'admin.user.oneUser' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    $( de + '_email' ).val( response.email );

                    $( de + '_first_name' ).val( response.first_name );
                    $( de + '_last_name' ).val( response.last_name );
                    // $( de + '_username' ).val( response.username );
                    $( de + '_phone_number' ).val( response.phone_number );
                    $( de + '_address_1' ).val( response.address_1 );
                    $( de + '_address_2' ).val( response.address_2 );
                    $( de + '_city' ).val( response.city );
                    $( de + '_state' ).val( response.state );
                    $( de + '_postcode' ).val( response.postcode );
                    // $( de + '_account_type' ).val( response.account_type );
                    dateOfBirth.setDate( response.date_of_birth );
                    
                    if( response.referral != null ){
                        let option1 = new Option( response.referral.email, response.referral.encrypted_id, true, true );
                        userSelect2.append( option1 );
                        userSelect2.trigger( 'change' );
                    }

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
        
        
        userSelect2 = $( de + '_referral' ).select2({

            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: true,

            ajax: { 
                url: '{{ route( 'admin.user.allUsers' ) }}',
                type: "post",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        user: params.term, // search term
                        designation: 1,
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
                            text: v.email,
                            first_name: v.first_name,
                            last_name: v.last_name,
                            phone_number: v.phone_number,
                        } );
                    } );

                    return {
                        results: processedResult,
                        pagination: {
                            more: ( params.page * 10 ) < data.recordsFiltered
                        }
                    };

                },
                cache: true
            },
            templateResult: function (data) {
                if (data.loading) return data.text;

                firstname = data?.first_name ?? '-';
                lastname = data?.last_name ?? '-';
                fullname = ( firstname ? firstname : '' ) + ' ' + ( lastname ? lastname : '' );
                const $container = $(`
                    <div class="d-flex align-items-center">
                        <span>${ fullname ? fullname : '-' }</span>
                        ( <span>${data.phone_number}</span> )
                    </div>
                `);
                return $container;
            },

            templateSelection: function (data) {
                return data.text || '';
            }

        });
    } );
</script>