<?php
$user_edit = 'user_edit';
$age_groups = $data['age_groups'] ?? [];
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
                {{-- <div class="mb-3 row">
                    <label for="{{ $user_edit }}_referral" class="col-sm-5 col-form-label">{{ __( 'user.referral' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-control select2" id="{{ $user_edit }}_referral" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'template.users' ) ] ) }}"></select>
                    </div>
                </div> --}}
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
                            <select class="form-select flex-shrink-0" id="{{ $user_edit }}_calling_code" style="max-width: 100px;">
                                <option value="+60" selected>+60</option>
                                <option value="+65">+65</option>
                            </select>
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
                <div class="mb-3 row">
                    <label for="{{ $user_edit }}_membership" class="col-sm-5 col-form-label">{{ __( 'user.membership' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $user_edit }}_membership" >
                            <option value="0">{{ __( 'user.free' ) }}</option>
                            <option value="1">{{ __( 'user.paid' ) }}</option>
                            <option value="2">{{ __( 'user.trial' ) }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_edit }}_nationality" class="col-sm-5 col-form-label">{{ __( 'user.nationality' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select form-select-md" id="{{ $user_edit }}_nationality" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'user.nationality' ) ] ) }}"></select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $user_edit }}_age_group" class="col-sm-5 col-form-label">{{ __( 'user.age_group' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $user_edit }}_age_group" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'user.age_group' ) ] ) }}</option>
                            @foreach( $age_groups as $age_group )
                                <option value="{{ $age_group }}">{{ $age_group }}</option>
                            @endforeach
                        </select>
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
            // formData.append( 'referral_id', $( de + '_referral' ).val() ?? '' );
            formData.append( 'email', $( de + '_email' ).val() );
            formData.append( 'first_name', $( de + '_first_name' ).val() );
            formData.append( 'last_name', $( de + '_last_name' ).val() );
            
            formData.append( 'calling_code', $( de + '_calling_code' ).val() );
            formData.append( 'phone_number', $( de + '_phone_number' ).val() );
            formData.append( 'password', $( de + '_password' ).val() );
            formData.append( 'date_of_birth', $( de + '_date_of_birth' ).val() );
            formData.append( 'nationality', $( de + '_nationality' ).val() ?? '' );
            formData.append( 'age_group', $( de + '_age_group' ).val() ?? '' );
            formData.append( 'membership', $( de + '_membership' ).val() ?? '0' );
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
                    $( de + '_membership').val( response.membership );

                    $( de + '_first_name' ).val( response.first_name );
                    $( de + '_last_name' ).val( response.last_name );
                    // $( de + '_username' ).val( response.username );
                    $( de + '_calling_code' ).val( response.calling_code );
                    $( de + '_phone_number' ).val( response.phone_number );
                    $( de + '_age_group' ).val( response.age_group );
                    if( response.date_of_birth != null ) {
                        dateOfBirth.setDate( response.date_of_birth );
                    }
                    
                    if( response.nationality != null ){
                        let option1 = new Option( response.nationality, response.nationality, true, true );
                        nationalitySelect2.append( option1 );
                        nationalitySelect2.trigger( 'change' );
                    }

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
        
        nationalitySelect2 = $( de + '_nationality' ).select2({

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