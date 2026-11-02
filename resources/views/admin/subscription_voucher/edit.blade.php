<?php
$subscription_voucher_edit = 'subscription_voucher_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.subscription_vouchers' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
        <div class="nk-block-head-content">
            <a href="{{ route( 'admin.subscription_voucher.codes' ) }}?voucher={{ request( 'id' ) }}" class="btn btn-outline-primary" id="{{ $subscription_voucher_edit }}_codes_link">{{ __( 'subscription_voucher.view_codes' ) }}</a>
            <a href="{{ route( 'admin.subscription_voucher.urls' ) }}?voucher={{ request( 'id' ) }}" class="btn btn-outline-primary" id="{{ $subscription_voucher_edit }}_urls_link" style="display: none;">{{ __( 'subscription_voucher.manage_urls' ) }}</a>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_edit }}_name" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.name' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $subscription_voucher_edit }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_edit }}_type" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.type' ) }}</label>
                    <div class="col-sm-8">
                        <select class="form-select form-select-sm" id="{{ $subscription_voucher_edit }}_type" disabled>
                            @foreach ( $data['type'] as $key => $type )
                            <option value="{{ $key }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        <div class="form-note">{{ __( 'subscription_voucher.type_not_editable' ) }}</div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_edit }}_description" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.description' ) }}</label>
                    <div class="col-sm-8">
                        <textarea class="form-control form-control-sm" id="{{ $subscription_voucher_edit }}_description" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="subscription-only">
                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_edit }}_duration_in_months" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.duration_in_months' ) }}</label>
                    <div class="col-sm-8">
                        <input type="number" min="1" step="1" class="form-control form-control-sm" id="{{ $subscription_voucher_edit }}_duration_in_months">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_edit }}_quantity" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.quantity' ) }}</label>
                    <div class="col-sm-8">
                        <input type="number" min="1" step="1" class="form-control form-control-sm" id="{{ $subscription_voucher_edit }}_quantity">
                        <div class="form-note">{{ __( 'subscription_voucher.quantity_hint' ) }} <span id="{{ $subscription_voucher_edit }}_usage"></span></div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_edit }}_universal_code" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.universal_code' ) }}</label>
                    <div class="col-sm-8">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="{{ $subscription_voucher_edit }}_has_universal_code">
                            <label class="form-check-label" for="{{ $subscription_voucher_edit }}_has_universal_code">{{ __( 'subscription_voucher.has_universal_code' ) }}</label>
                        </div>
                        <input type="text" class="form-control form-control-sm" id="{{ $subscription_voucher_edit }}_universal_code" placeholder="{{ __( 'subscription_voucher.universal_code_placeholder' ) }}" style="display: none; text-transform: uppercase;">
                        <div class="invalid-feedback"></div>
                        <div class="form-note">{{ __( 'subscription_voucher.universal_code_hint' ) }}</div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_edit }}_start_date" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.start_date' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $subscription_voucher_edit }}_start_date" placeholder="{{ __( 'subscription_voucher.date_placeholder' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_edit }}_end_date" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.end_date' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $subscription_voucher_edit }}_end_date" placeholder="{{ __( 'subscription_voucher.date_placeholder' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                </div><!-- .subscription-only -->

                <div class="text-end">
                    <button id="{{ $subscription_voucher_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $subscription_voucher_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let sve = '#{{ $subscription_voucher_edit }}',
            datePickers = {};

        [ '_start_date', '_end_date' ].forEach( function( field ) {
            datePickers[field] = flatpickr( sve + field, {
                dateFormat: 'Y-m-d H:i',
                enableTime: true,
                time_24hr: true,
                disableMobile: true,
                allowInput: true,
            } );
        } );

        // Universal code: admin key in, leave empty to auto generate
        $( sve + '_has_universal_code' ).change( function() {
            $( sve + '_universal_code' ).toggle( $( this ).is( ':checked' ) );
        } );

        $( sve + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.subscription_voucher.index' ) }}';
        } );

        $( sve + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'name', $( sve + '_name' ).val() );
            formData.append( 'description', $( sve + '_description' ).val() );
            formData.append( 'duration_in_months', $( sve + '_duration_in_months' ).val() );
            formData.append( 'quantity', $( sve + '_quantity' ).val() );
            formData.append( 'has_universal_code', $( sve + '_has_universal_code' ).is( ':checked' ) ? 1 : 0 );
            formData.append( 'universal_code', $( sve + '_universal_code' ).val() );
            formData.append( 'start_date', $( sve + '_start_date' ).val() );
            formData.append( 'end_date', $( sve + '_end_date' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.subscription_voucher.updateSubscriptionVoucher' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.subscription_voucher.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( sve + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getSubscriptionVoucher();

        function getSubscriptionVoucher() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.subscription_voucher.oneSubscriptionVoucher' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( sve + '_name' ).val( response.name ?? '' );
                    $( sve + '_type' ).val( response.type );

                    let isThirdParty = response.type == {{ \App\Models\SubscriptionVoucher::TYPE_THIRD_PARTY }};
                    $( '.subscription-only' ).toggle( !isThirdParty );
                    $( sve + '_codes_link' ).toggle( !isThirdParty );
                    $( sve + '_urls_link' ).toggle( isThirdParty );

                    $( sve + '_description' ).val( response.description ?? '' );
                    $( sve + '_duration_in_months' ).val( response.duration_in_months );
                    $( sve + '_quantity' ).val( response.quantity );
                    $( sve + '_has_universal_code' ).prop( 'checked', !!response.universal_code ).trigger( 'change' );
                    $( sve + '_universal_code' ).val( response.universal_code ?? '' );
                    $( sve + '_usage' ).text( '( {{ __( 'subscription_voucher.issued' ) }}: ' + response.issued_count + ', {{ __( 'subscription_voucher.claimed_count' ) }}: ' + response.claimed_count + ' )' );

                    if ( response.start_date ) {
                        datePickers['_start_date'].setDate( response.start_date );
                    }
                    if ( response.end_date ) {
                        datePickers['_end_date'].setDate( response.end_date );
                    }

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
    } );
</script>
