<?php
$subscription_voucher_create = 'subscription_voucher_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.subscription_vouchers' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_create }}_name" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.name' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $subscription_voucher_create }}_name" placeholder="e.g. Free 1 Month">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_create }}_type" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.type' ) }}</label>
                    <div class="col-sm-8">
                        <select class="form-select form-select-sm" id="{{ $subscription_voucher_create }}_type">
                            @foreach ( $data['type'] as $key => $type )
                            <option value="{{ $key }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        <div class="form-note">{{ __( 'subscription_voucher.type_hint' ) }}</div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_create }}_description" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.description' ) }}</label>
                    <div class="col-sm-8">
                        <textarea class="form-control form-control-sm" id="{{ $subscription_voucher_create }}_description" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="subscription-only">
                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_create }}_duration_in_months" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.duration_in_months' ) }}</label>
                    <div class="col-sm-8">
                        <input type="number" min="1" step="1" class="form-control form-control-sm" id="{{ $subscription_voucher_create }}_duration_in_months" value="1">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_create }}_quantity" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.quantity' ) }}</label>
                    <div class="col-sm-8">
                        <input type="number" min="1" step="1" class="form-control form-control-sm" id="{{ $subscription_voucher_create }}_quantity">
                        <div class="form-note">{{ __( 'subscription_voucher.quantity_hint' ) }}</div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_create }}_universal_code" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.universal_code' ) }}</label>
                    <div class="col-sm-8">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="{{ $subscription_voucher_create }}_has_universal_code">
                            <label class="form-check-label" for="{{ $subscription_voucher_create }}_has_universal_code">{{ __( 'subscription_voucher.has_universal_code' ) }}</label>
                        </div>
                        <input type="text" class="form-control form-control-sm" id="{{ $subscription_voucher_create }}_universal_code" placeholder="{{ __( 'subscription_voucher.universal_code_placeholder' ) }}" style="display: none; text-transform: uppercase;">
                        <div class="invalid-feedback"></div>
                        <div class="form-note">{{ __( 'subscription_voucher.universal_code_hint' ) }}</div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_create }}_start_date" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.start_date' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $subscription_voucher_create }}_start_date" placeholder="{{ __( 'subscription_voucher.date_placeholder' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $subscription_voucher_create }}_end_date" class="col-sm-4 col-form-label">{{ __( 'subscription_voucher.end_date' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $subscription_voucher_create }}_end_date" placeholder="{{ __( 'subscription_voucher.date_placeholder' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                </div><!-- .subscription-only -->

                <div class="text-end">
                    <button id="{{ $subscription_voucher_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $subscription_voucher_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let svc = '#{{ $subscription_voucher_create }}';

        [ '_start_date', '_end_date' ].forEach( function( field ) {
            flatpickr( svc + field, {
                dateFormat: 'Y-m-d H:i',
                enableTime: true,
                time_24hr: true,
                disableMobile: true,
                allowInput: true,
            } );
        } );

        // Third party: no duration / quantity / claim period
        $( svc + '_type' ).change( function() {
            $( '.subscription-only' ).toggle( $( this ).val() == '{{ \App\Models\SubscriptionVoucher::TYPE_SUBSCRIPTION }}' );
        } );

        // Universal code: admin key in, leave empty to auto generate
        $( svc + '_has_universal_code' ).change( function() {
            $( svc + '_universal_code' ).toggle( $( this ).is( ':checked' ) );
        } );

        $( svc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.subscription_voucher.index' ) }}';
        } );

        $( svc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'name', $( svc + '_name' ).val() );
            formData.append( 'type', $( svc + '_type' ).val() );
            formData.append( 'description', $( svc + '_description' ).val() );
            formData.append( 'duration_in_months', $( svc + '_duration_in_months' ).val() );
            formData.append( 'quantity', $( svc + '_quantity' ).val() );
            formData.append( 'has_universal_code', $( svc + '_has_universal_code' ).is( ':checked' ) ? 1 : 0 );
            formData.append( 'universal_code', $( svc + '_universal_code' ).val() );
            formData.append( 'start_date', $( svc + '_start_date' ).val() );
            formData.append( 'end_date', $( svc + '_end_date' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.subscription_voucher.createSubscriptionVoucher' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    // Third party: go add urls next
                    let redirect = response.third_party_url ?? '{{ route( 'admin.module_parent.subscription_voucher.index' ) }}';
                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = redirect;
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( svc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
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
