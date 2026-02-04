<?php
$subscription_plan_edit = 'subscription_plan_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.subscription_plans' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                
                <div class="mb-3 row">
                    <label for="{{ $subscription_plan_edit }}_name" class="col-sm-5 col-form-label">{{ __( 'subscription_plan.name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $subscription_plan_edit }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $subscription_plan_edit }}_description" class="col-sm-5 col-form-label">{{ __( 'subscription_plan.description' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $subscription_plan_edit }}_description">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $subscription_plan_edit }}_price" class="col-sm-5 col-form-label">{{ __( 'subscription_plan.price' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $subscription_plan_edit }}_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row d-none">
                    <label for="{{ $subscription_plan_edit }}_duration_in_years" class="col-sm-5 col-form-label">{{ __( 'subscription_plan.duration_in_years' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $subscription_plan_edit }}_duration_in_years">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $subscription_plan_edit }}_duration_in_months" class="col-sm-5 col-form-label">{{ __( 'subscription_plan.duration_in_months' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $subscription_plan_edit }}_duration_in_months">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $subscription_plan_edit }}_duration_in_days" class="col-sm-5 col-form-label">{{ __( 'subscription_plan.duration_in_days' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $subscription_plan_edit }}_duration_in_days">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $subscription_plan_edit }}_ios_product_id" class="col-sm-5 col-form-label">{{ __( 'subscription_plan.ios_product_id' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $subscription_plan_edit }}_ios_product_id">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $subscription_plan_edit }}_android_product_id" class="col-sm-5 col-form-label">{{ __( 'subscription_plan.android_product_id' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $subscription_plan_edit }}_android_product_id">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row d-none">
                    <label for="{{ $subscription_plan_edit }}_huawei_product_id" class="col-sm-5 col-form-label">{{ __( 'subscription_plan.huawei_product_id' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $subscription_plan_edit }}_huawei_product_id">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $subscription_plan_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $subscription_plan_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $subscription_plan_edit }}',
                fileID = '';

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.subscription_plan.index' ) }}';
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();
            
            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'name', $( fe + '_name' ).val() );
            formData.append( 'description', $( fe + '_description' ).val() );
            formData.append( 'price', $( fe + '_price' ).val() );
            formData.append( 'duration_in_days', $( fe + '_duration_in_days' ).val() ?? 0 );
            formData.append( 'duration_in_months', $( fe + '_duration_in_months' ).val() ?? 0 );
            formData.append( 'duration_in_years', $( fe + '_duration_in_years' ).val() ?? 0 );
            formData.append( 'ios_product_id', $( fe + '_ios_product_id' ).val() );
            formData.append( 'android_product_id', $( fe + '_android_product_id' ).val() );
            formData.append( 'huawei_product_id', $( fe + '_huawei_product_id' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.subscription_plan.updateSubscriptionPlan' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.subscription_plan.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( fe + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getPlan();
        function getPlan() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.subscription_plan.oneSubscriptionPlan' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    
                    $( fe + '_name' ).val( response.name );
                    $( fe + '_description' ).val( response.description );
                    $( fe + '_price' ).val( response.price );
                    $( fe + '_duration_in_days' ).val( response.duration_in_days );
                    $( fe + '_duration_in_months' ).val( response.duration_in_months );
                    $( fe + '_ios_product_id' ).val( response.ios_product_id );
                    $( fe + '_android_product_id' ).val( response.android_product_id );
                    $( fe + '_huawei_product_id' ).val( response.huawei_product_id );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
    } );
</script>