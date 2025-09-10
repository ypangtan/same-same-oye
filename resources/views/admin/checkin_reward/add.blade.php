<?php
$checkin_reward_create = 'checkin_reward_create';
$rewardTypes = $data['reward_types'];
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.checkin_rewards' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $checkin_reward_create }}_reward_type" class="col-sm-5 col-form-label">{{ __( 'checkin_reward.reward_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $checkin_reward_create }}_reward_type">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'checkin_reward.reward_type' ) ] ) }}</option>
                            @forEach( $rewardTypes as $key => $rewardType )
                                <option value="{{ $key }}">{{ $rewardType }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $checkin_reward_create }}_consecutive_days" class="col-sm-5 col-form-label">{{ __( 'checkin_reward.consecutive_days' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $checkin_reward_create }}_consecutive_days">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <section id="points" class="rule-section hidden mb-3 row">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3 row">
                                        <div class="col-sm-3">
                                            <h5>{{ __( 'checkin_reward.reward' ) }}</h5>
                                            <small>{!!__( 'checkin_reward.reward_description_points' )!!}</small>
                                        </div>
                                        <div class="col-sm">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-8">
                                                    <fieldset class="border p-2">
                                                        <legend class="mb-0 fs-6 float-none w-auto">{{ __( 'checkin_reward.points' ) }}</legend>
                                                        <div class="row">
                                                            <div class="col">
                                                                <input type="number" class="form-control form-control-sm" id="{{ $checkin_reward_create}}_points">
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <section id="voucher" class="rule-section hidden mb-3 row">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3 row">
                                        <div class="col-sm-3">
                                            <h5>{{ __( 'checkin_reward.reward' ) }}</h5>
                                            <small>{!!__( 'checkin_reward.reward_description_voucher' )!!}</small>
                                        </div>
                                        <div class="col-sm">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-8">
                                                    <fieldset class="border p-2">
                                                        <legend class="mb-0 fs-6 float-none w-auto">{{ __( 'voucher.get_quantity' ) }}</legend>
                                                        <div class="row">
                                                            <div class="col">
                                                                <input type="number" class="form-control form-control-sm" id="{{ $checkin_reward_create}}_voucher_quantity">
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                            <div class="col">
                                                                <select class="form-select form-select-sm" id="{{ $checkin_reward_create}}_voucher" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'checkin_reward.get_voucher' ) ] ) }}">
                                                                </select>
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="text-end">
                    <button id="{{ $checkin_reward_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $checkin_reward_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $checkin_reward_create }}',
                fileID = '';

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.checkin_reward.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'reward_type', $( fc + '_reward_type' ).val() );
            formData.append( 'consecutive_days', $( fc + '_consecutive_days' ).val() );
            formData.append( 'voucher_quantity', $( fc + '_voucher_quantity' ).val() );
            formData.append( 'voucher', $( fc + '_voucher' ).val() ?? '' );
            formData.append( 'points', $( fc + '_points' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.checkin_reward.createCheckinReward' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.checkin_reward.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( fc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        $( fc + '_reward_type' ).change( function() {
            $( '.rule-section' ).addClass( 'hidden' );

            switch ( parseInt( $( this ).val() ) ) {
                case 2:
                    $( '#voucher' ).removeClass( 'hidden' );
                    $( '#points' ).addClass( 'hidden' );
                    break;

                default:
                    $( '#points' ).removeClass( 'hidden' );
                    $( '#voucher' ).addClass( 'hidden' );
                    break;
            }
        } );

        $( fc + '_voucher' ).select2( {
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: true,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.voucher.allVouchers' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        title: params.term, // search term
                        start: params.page ? params.page : 0,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.vouchers.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.title,
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