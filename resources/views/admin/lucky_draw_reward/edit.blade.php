<?php
$lucky_draw_reward_edit = 'lucky_draw_reward_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.lucky_draw_rewards' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                
                <div class="mb-3 row">
                    <label for="{{ $lucky_draw_reward_edit }}_customer_member_id" class="col-sm-5 form-label">{{ __( 'lucky_draw_reward.customer_member_id' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $lucky_draw_reward_edit }}_customer_member_id">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $lucky_draw_reward_edit }}_name" class="col-sm-5 form-label">{{ __( 'lucky_draw_reward.name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $lucky_draw_reward_edit }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $lucky_draw_reward_edit }}_quantity" class="col-sm-5 form-label">{{ __( 'lucky_draw_reward.quantity' ) }}</label>
                    <div class="col-sm-7">
                        <input class="form-control" id="{{ $lucky_draw_reward_edit }}_quantity">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $lucky_draw_reward_edit }}_reference_id" class="col-sm-5 form-label">{{ __( 'lucky_draw_reward.reference_id' ) }}</label>
                    <div class="col-sm-7">
                        <div id="{{ $lucky_draw_reward_edit }}_reference_id" style="display: flex;row-gap: 12px;flex-wrap: wrap;">
                        </div>
                        <div class="invalid-feedback"></div>
                        <div class="text-end mt-3">
                            <button class="btn btn-md btn-primary btn_add">+</button>
                        </div>
                    </div>
                </div>
                
                <div class="text-end">
                    <button id="{{ $lucky_draw_reward_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $lucky_draw_reward_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $lucky_draw_reward_edit }}'
            template_reference = $( `
            <div class="d-flex justify-content-between align-items-center w-100">
                <input type="text" class="form-control {{ $lucky_draw_reward_edit }}_reference_id" name="{{ $lucky_draw_reward_edit }}_reference_id">
                <span class="close btn_reduce" type="button"><em class="icon ni ni-cross"></em></span>
            </div>
        ` );

        getRewards();

        $( document ).on( 'click', '.btn_reduce', function() {
            $( this ).parent().remove();
        } );

        $( document ).on( 'focus' , '.lucky_draw_reward_edit_reference_id', function() {
            $( '.lucky_draw_reward_edit_reference_id' ).removeClass( 'is-invalid' ).parent().parent().nextAll( 'div.invalid-feedback' ).text( '' );
        } );

        $( '.btn_add' ).click( function() {
            template = template_reference.clone();
            $( fe + '_reference_id' ).append( template );
        } );

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.lucky_draw_reward.index' ) }}';
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let refenrence_id = [];

            $.each( $( fe + '_reference_id' ).find( 'input' ), function( key, value ) {
                if ( $( value ).val() ) {
                    refenrence_id.push( $( value ).val() );
                }
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ Request( 'id' ) }}' );
            formData.append( 'customer_member_id', $( fe + '_customer_member_id' ).val() );
            formData.append( 'name', $( fe + '_name' ).val() );
            formData.append( 'quantity', $( fe + '_quantity' ).val() );
            formData.append( 'reference_id', JSON.stringify( refenrence_id ) );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.lucky_draw_reward.updateLuckyDrawReward' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.lucky_draw_reward.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            if( key == 'reference_id' ) {
                                $( fe + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                                $( fe + '_' + key ).find( '.lucky_draw_reward_create_reference_id' ).addClass( 'is-invalid' )
                            }else {
                                $( fe + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                            }
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        function getRewards() {
            
            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.lucky_draw_reward.oneLuckyDrawReward' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( fe + '_customer_member_id' ).val( response.customer_member_id );
                    $( fe + '_name' ).val( response.name );
                    $( fe + '_quantity' ).val( response.quantity );

                    $reference = response.reference_id.split( ',' );
                    $.each( $reference, function( key, value ) {
                        
                        template = template_reference.clone();
                        $( fe + '_reference_id' ).append( template );
                        template.find( '.lucky_draw_reward_edit_reference_id' ).val( value );
                    } );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
        
    } );
</script>