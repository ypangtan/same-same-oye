<?php
$setting = 'setting';
?>

<div class="card">
    <div class="card-body">
        <div class="row gy-3">
            <div class="col-md-2">                
                <div class="list-group" role="tablist">
                    <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#ms" role="tab">{{ __( 'setting.bonus_settings' ) }}</a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#bgs" role="tab">{{ __( 'setting.birthday_gift_settings' ) }}</a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#rgs" role="tab">{{ __( 'setting.referral_gift_settings' ) }}</a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#avs" role="tab">{{ __( 'setting.app_version_settings' ) }}</a>
                </div>
            </div>
            <div class="col-md-10">
                <div class="tab-content p-2">
                    <div class="tab-pane fade show active" id="ms" role="tabpanel">
                        <h5 class="card-title mb-0">{{ __( 'setting.bonus_settings' ) }}</h5>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3 row">
                                    <label for="{{ $setting }}_convertion_rate" class="col-sm-5 col-form-label">{{ __( 'setting.points_convertion' ) }} (RM 1 SPEND = <span id="convertion_rate_preview"></span> Points)</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_convertion_rate">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="{{ $setting }}_register_bonus" class="col-sm-5 col-form-label">{{ __( 'setting.register_bonus' ) }}</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_register_bonus">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row d-none">
                                    <label for="{{ $setting }}_referral_register_bonus_points" class="col-sm-5 col-form-label">{{ __( 'setting.referral_register_bonus_points' ) }}</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_referral_register_bonus_points">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row d-none">
                                    <label for="{{ $setting }}_referral_spending_bonus_points" class="col-sm-5 col-form-label">{{ __( 'setting.referral_spending_bonus_points' ) }} (RM 1 SPEND = <span id="referral_spending_bonus_points_preview"></span> Points)</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_referral_spending_bonus_points">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row d-none">
                                    <label for="{{ $setting }}_taxes" class="col-sm-5 col-form-label">{{ __( 'setting.taxes' ) }}</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_taxes">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button class="btn btn-sm btn-primary" id="bs_save">{{ __( 'template.save_changes' ) }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="bgs" role="tabpanel">
                        <h5 class="card-title mb-0">{{ __( 'setting.birthday_gift_settings' ) }}</h5>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="{{ $setting }}_birthday_enable">
                                        <label class="form-check-label" for="{{ $setting }}_birthday_enable">{{ __( 'setting.enable_gift' ) }}</label>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="{{ $setting }}_birthday_reward_type" class="col-sm-5 col-form-label">{{ __( 'setting.reward_type' ) }}</label>
                                    <div class="col-sm-7">
                                        <select class="form-select" id="{{ $setting }}_birthday_reward_type">
                                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'setting.reward_type' ) ] ) }}</option>
                                            @forEach( $data['reward_types'] as $key => $rewardType )
                                                <option value="{{ $key }}">{{ $rewardType }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row d-none">
                                    <label for="{{ $setting }}_birthday_voucher" class="col-sm-5 col-form-label">{{ __( 'setting.voucher' ) }}</label>
                                    <div class="col-sm-7">
                                        <select class="form-select form-select-sm" id="{{ $setting }}_birthday_voucher" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'setting.voucher' ) ] ) }}">
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row d-none">
                                    <label for="{{ $setting }}_birthday_reward_value" class="col-sm-5 col-form-label">{{ __( 'setting.reward_value' ) }}</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_birthday_reward_value">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button class="btn btn-sm btn-primary" id="birthday_gift_save">{{ __( 'template.save_changes' ) }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="rgs" role="tabpanel">
                        <h5 class="card-title mb-0">{{ __( 'setting.referral_gift_settings' ) }}</h5>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="{{ $setting }}_referral_enable">
                                        <label class="form-check-label" for="{{ $setting }}_referral_enable">{{ __( 'setting.enable_gift' ) }}</label>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="{{ $setting }}_referral_reward_type" class="col-sm-5 col-form-label">{{ __( 'setting.reward_type' ) }}</label>
                                    <div class="col-sm-7">
                                        <select class="form-select" id="{{ $setting }}_referral_reward_type">
                                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'setting.reward_type' ) ] ) }}</option>
                                            @forEach( $data['reward_types'] as $key => $rewardType )
                                                <option value="{{ $key }}">{{ $rewardType }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row d-none">
                                    <label for="{{ $setting }}_referral_voucher" class="col-sm-5 col-form-label">{{ __( 'setting.voucher' ) }}</label>
                                    <div class="col-sm-7">
                                        <select class="form-select form-select-sm" id="{{ $setting }}_referral_voucher" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'setting.voucher' ) ] ) }}">
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row d-none">
                                    <label for="{{ $setting }}_referral_reward_value" class="col-sm-5 col-form-label">{{ __( 'setting.reward_value' ) }}</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_referral_reward_value">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3 row d-none">
                                    <label for="{{ $setting }}_referral_expiry_day" class="col-sm-5 col-form-label">{{ __( 'setting.expiry_day' ) }} ( Days )</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_referral_expiry_day">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button class="btn btn-sm btn-primary" id="referral_gift_save">{{ __( 'template.save_changes' ) }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="avs" role="tabpanel">
                        <h5 class="card-title mb-0">{{ __( 'setting.app_version_settings' ) }}</h5>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="{{ $setting }}_force_logout">
                                        <label class="form-check-label" for="{{ $setting }}_force_logout">{{ __( 'setting.enable_force_logout' ) }}</label>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="{{ $setting }}_version" class="col-sm-5 col-form-label">{{ __( 'setting.app_version' ) }}</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="{{ $setting }}_version">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button class="btn btn-sm btn-primary" id="app_version_save">{{ __( 'template.save_changes' ) }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        getSettings();
        getGiftSettings();
        getAppVersionSettings();

        let s = '#{{ $setting }}';

        $( s + '_referral_reward_type' ).change( function() {
            if( $( this ).val() == 1 ) {
                $( s + '_referral_voucher' ).parent().parent().addClass( 'd-none' );
                $( s + '_referral_expiry_day' ).parent().parent().addClass( 'd-none' );

                $( s + '_referral_reward_value' ).parent().parent().removeClass( 'd-none' );
            } else {
                $( s + '_referral_voucher' ).parent().parent().removeClass( 'd-none' );
                $( s + '_referral_expiry_day' ).parent().parent().removeClass( 'd-none' );

                $( s + '_referral_reward_value' ).parent().parent().addClass( 'd-none' );
            }
        } );

        $( s + '_birthday_reward_type' ).change( function() {
            if( $( this ).val() == 1 ) {
                $( s + '_birthday_voucher' ).parent().parent().addClass( 'd-none' );

                $( s + '_birthday_reward_value' ).parent().parent().removeClass( 'd-none' );
            } else {
                $( s + '_birthday_voucher' ).parent().parent().removeClass( 'd-none' );

                $( s + '_birthday_reward_value' ).parent().parent().addClass( 'd-none' );
            }
        } );

        $(s+'_convertion_rate').on('keyup', function () {
            $( '#convertion_rate_preview').text( $(this).val() );
        });

        $(s+'_referral_spending_bonus_points').on('keyup', function () {
            $( '#referral_spending_bonus_points_preview').text( $(this).val() );
        });

        $( '#bs_save' ).on( 'click', function() {

            resetInputValidation();

            $.ajax( {
                url: '{{ route( 'admin.setting.updateBonusSetting' ) }}',
                type: 'POST',
                data: {
                    convertion_rate: $( s + '_convertion_rate' ).val(),
                    referral_register_bonus_points: $( s + '_referral_register_bonus_points' ).val(),
                    register_bonus: $( s + '_register_bonus' ).val(),
                    referral_spending_bonus_points: $( s + '_referral_spending_bonus_points' ).val(),
                    taxes: $( s + '_taxes' ).val(),
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.show();
                },
                error: function( error ) {
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( s + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.show();       
                    }
                }
            } );
        } );

        $( '#birthday_gift_save' ).on( 'click', function() {

            resetInputValidation();

            $.ajax( {
                url: '{{ route( 'admin.setting.updateBirthdayGiftSetting' ) }}',
                type: 'POST',
                data: {
                    reward_type: $( s + '_birthday_reward_type' ).val(),
                    reward_value: $( s + '_birthday_reward_value' ).val(),
                    voucher: $( s + '_birthday_voucher' ).val() ?? '',
                    enable: $( s + '_birthday_enable' ).is( ':checked' ) ? 10 : 20,
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.show();
                },
                error: function( error ) {
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( s + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.show();       
                    }
                }
            } );
        } );

        $( '#referral_gift_save' ).on( 'click', function() {

            resetInputValidation();

            $.ajax( {
                url: '{{ route( 'admin.setting.updateReferralGiftSetting' ) }}',
                type: 'POST',
                data: {
                    reward_type: $( s + '_referral_reward_type' ).val(),
                    reward_value: $( s + '_referral_reward_value' ).val(),
                    expiry_day: $( s + '_referral_expiry_day' ).val(),
                    voucher: $( s + '_referral_voucher' ).val() ?? '',
                    enable: $( s + '_referral_enable' ).is( ':checked' ) ? 10 : 20,
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.show();
                },
                error: function( error ) {
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( s + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.show();       
                    }
                }
            } );
        } );

        $( '#app_version_save' ).on( 'click', function() {

            resetInputValidation();

            $.ajax( {
                url: '{{ route( 'admin.setting.updateAppVersionSetting' ) }}',
                type: 'POST',
                data: {
                    version: $( s + '_version' ).val(),
                    force_logout: $( s + '_force_logout' ).is( ':checked' ) ? 10 : 20,
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.show();
                },
                error: function( error ) {
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( s + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.show();       
                    }
                }
            } );
        } );

        function getSettings() {

            $.ajax( {
                url: '{{ route( 'admin.setting.bonusSettings' ) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    if ( response ) {
                        response.forEach(item => {
                            if (item.option_name === "CONVERTION_RATE") {
                                $( s + '_convertion_rate').val( item.option_value );
                                $( '#convertion_rate_preview').text( item.option_value );
                            }

                            if (item.option_name === "REFERRAL_REGISTER") {
                                $( s + '_referral_register_bonus_points').val( item.option_value );
                            }

                            if (item.option_name === "REFERRAL_SPENDING") {
                                $( s + '_referral_spending_bonus_points').val( item.option_value );
                                $( '#referral_spending_bonus_points_preview').text( item.option_value );
                            }

                            if (item.option_name === "REGISTER_BONUS") {
                                $( s + '_register_bonus').val( item.option_value );
                            }

                            if (item.option_name === "TAXES") {
                                $( s + '_taxes').val( item.option_value );
                            }
                        });
                    }
                },
            } );
        }

        function getGiftSettings() {

            $.ajax( {
                url: '{{ route( 'admin.setting.giftSettings' ) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    if( response.birthday ) {
                        $( s + '_birthday_' + 'reward_type' ).val( response?.birthday?.reward_type ?? '' ).trigger( 'change' );
                        $( s + '_birthday_' + 'reward_value' ).val( response?.birthday?.reward_value ?? '' );
                        if ( response?.birthday?.status == 10 ) {
                            $( s + '_birthday_' + 'enable' ).prop('checked', true);
                        }
                        if( response.birthday && response.birthday.voucher ){
                            let option2 = new Option( response.birthday.voucher.title, response.birthday.voucher.id, true, true );
                            birthdaySelect2.append( option2 )
                        }
                    }
                    
                    if ( response.referral ) {
                        $( s + '_referral_' + 'reward_type' ).val( response?.referral?.reward_type ?? '' ).trigger( 'change' );
                        $( s + '_referral_' + 'expiry_day' ).val( response?.referral?.expiry_day ?? '' );
                        $( s + '_referral_' + 'reward_value' ).val( response?.referral?.reward_value ?? '' );
                        if ( response?.referral?.status == 10 ) {
                            $( s + '_referral_' + 'enable' ).prop('checked', true);
                        }

                        if( response.referral && response.referral.voucher ){
                            let option = new Option( response.referral.voucher.title, response.referral.voucher.id, true, true );
                            referralSelect2.append( option )
                        }
                    }
                },
            } );
        }

        function getAppVersionSettings() {

            $.ajax( {
                url: '{{ route( 'admin.setting.lastestAppVersion' ) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    if ( response.data ) {
                        $( s + '_version').val( response.data.version );
                        if ( response?.data.force_logout == 10 ) {
                            $( s + '_force_logout' ).prop('checked', true);
                        }
                    }
                },
            } );
        }

        let birthdaySelect2 = $( s + '_birthday_voucher' ).select2( {
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
                        status: 10,
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

        let referralSelect2 = $( s + '_referral_voucher' ).select2( {
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
                        status: 10,
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

