<?php
$user_create = 'user_create';
$age_groups = $data['age_groups'] ?? [];
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.users' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                {{-- <div class="mb-3 row">
                    <label for="{{ $user_create }}_referral" class="col-sm-5 col-form-label">{{ __( 'user.referral' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-control select2" id="{{ $user_create }}_referral" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'template.users' ) ] ) }}"></select>
                    </div>
                </div> --}}
                <div class="mb-3 row">
                    <label for="{{ $user_create }}_date_of_birth" class="col-sm-5 col-form-label">{{ __( 'user.date_of_birth' ) }}</label>
                    <div class="col-sm-7">
                        <input type="date" class="form-control" id="{{ $user_create }}_date_of_birth">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @if( 1 == 2 )
                <div class="mb-3 row">
                    <label for="{{ $user_create }}_account_type" class="col-sm-5 col-form-label">{{ __( 'user.account_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $user_create }}_account_type" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'user.account_type' ) ] ) }}</option>
                            <option value="1">{{ __( 'user.personal' ) }}</option>
                            <option value="2">{{ __( 'user.company' ) }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_create }}_username" class="col-sm-5 col-form-label">{{ __( 'user.username' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $user_create }}_username">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @endif
                <div class="mb-3 row">
                    <label for="{{ $user_create }}_email" class="col-sm-5 col-form-label">{{ __( 'user.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $user_create }}_email">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_create }}_first_name" class="col-sm-5 col-form-label">{{ __( 'user.first_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $user_create }}_first_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_create }}_last_name" class="col-sm-5 col-form-label">{{ __( 'user.last_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $user_create }}_last_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_create }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'user.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <div class="input-group">
                            <select class="form-select flex-shrink-0" id="{{ $user_create }}_calling_code" style="max-width: 100px;">
                                <option value="+60" selected>+60</option>
                                <option value="+65">+65</option>
                            </select>
                            <input type="text" class="form-control" id="{{ $user_create }}_phone_number">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>                    
                </div>                
                
                <div class="mb-3 row">
                    <label for="{{ $user_create }}_password" class="col-sm-5 col-form-label">{{ __( 'user.password' ) }}</label>
                    <div class="col-sm-7">
                        <input type="password" class="form-control" id="{{ $user_create }}_password" autocomplete="new-password">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_create }}_membership" class="col-sm-5 col-form-label">{{ __( 'user.membership' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="{{ $user_create }}_membership">
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_create }}_nationality" class="col-sm-5 col-form-label">{{ __( 'user.nationality' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select form-select-md" id="{{ $user_create }}_nationality" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'user.nationality' ) ] ) }}"></select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $user_create }}_age_group" class="col-sm-5 col-form-label">{{ __( 'user.age_group' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $user_create }}_age_group" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'user.age_group' ) ] ) }}</option>
                            @foreach( $age_groups as $age_group )
                                <option value="{{ $age_group }}">{{ $age_group }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $user_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $user_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let dc = '#{{ $user_create }}',
                fileID = '';

        $( dc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.user.index' ) }}';
        } );

        let dateOfBirth = $( dc + '_date_of_birth' ).flatpickr( {
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( dc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            // formData.append( 'username', $( dc + '_username' ).val() );
            // formData.append( 'referral_id', $( dc + '_referral' ).val() ?? '' );
            formData.append( 'email', $( dc + '_email' ).val() );
            formData.append( 'first_name', $( dc + '_first_name' ).val() );
            formData.append( 'last_name', $( dc + '_last_name' ).val() );
            formData.append( 'address_1', $( dc + '_address_1' ).val() );
            formData.append( 'address_2', $( dc + '_address_2' ).val() );
            formData.append( 'city', $( dc + '_city' ).val() );
            formData.append( 'state', $( dc + '_state' ).val() );
            formData.append( 'postcode', $( dc + '_postcode' ).val() );
            formData.append( 'phone_number', $( dc + '_phone_number' ).val() );
            formData.append( 'calling_code', $( dc + '_calling_code' ).val() );
            formData.append( 'password', $( dc + '_password' ).val() );
            formData.append( 'date_of_birth', $( dc + '_date_of_birth' ).val() );
            formData.append( 'nationality', $( dc + '_nationality' ).val() ?? '' );
            formData.append( 'age_group', $( dc + '_age_group' ).val() ?? '' );
            formData.append( 'membership', $( dc + '_membership' ).is( ':checked' ) ? 1 : 0 );
            // formData.append( 'account_type', $( dc + '_account_type' ).val() );
            
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.user.createUser' ) }}',
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
                            $( dc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );
        
        $( dc + '_nationality' ).select2({

            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: true,

            ajax: { 
                url: '{{ route( 'admin.country.allCountries' ) }}',
                type: "post",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        nationality: params.term, // search term
                        designation: 1,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.countries.map( function( v, i ) {
                        processedResult.push( {
                            id: v.nationality,
                            text: v.nationality,
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

        });

    } );
</script>