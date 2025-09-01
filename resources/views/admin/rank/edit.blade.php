<?php
$rank_edit = 'rank_edit';
?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $rank_edit }}_name" class="col-sm-5 col-form-label">{{ __( 'rank.title' ) }} (English)</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $rank_edit }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $rank_edit }}_description" class="col-sm-5 col-form-label">{{ __( 'rank.description' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $rank_edit }}_description">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $rank_edit }}_target_spending" class="col-sm-5 col-form-label">{{ __( 'rank.target_spending' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control form-control-sm" id="{{ $rank_edit }}_target_spending">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $rank_edit }}_reward_value" class="col-sm-5 col-form-label">{{ __( 'rank.reward_value' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control form-control-sm" id="{{ $rank_edit }}_reward_value">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $rank_edit }}_priority" class="col-sm-5 col-form-label">{{ __( 'rank.priority' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control form-control-sm" id="{{ $rank_edit }}_priority">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $rank_edit }}_cancel" type="button" class="btn btn-sm btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $rank_edit }}_submit" type="button" class="btn btn-sm btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        getRank();

        let ue = '#{{ $rank_edit }}';

        $( ue + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.rank.index' ) }}';
        } );

        $( ue + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'name', $( ue + '_name' ).val() );
            formData.append( 'description', $( ue + '_description' ).val() );
            formData.append( 'target_spending', $( ue + '_target_spending' ).val() );
            formData.append( 'reward_value', $( ue + '_reward_value' ).val() );
            formData.append( 'priority', $( ue + '_priority' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.rank.updateRank' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.rank.index' ) }}';
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

        function getRank() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.rank.oneRank' ) }}',
                type: 'POST',
                data: {
                    id: '{{ request( 'id' ) }}',
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {

                    $( ue + '_name' ).val( response.title );
                    $( ue + '_description' ).val( response.description );
                    $( ue + '_target_spending' ).val( response.target_spending );
                    $( ue + '_reward_value' ).val( response.reward_value );
                    $( ue + '_priority' ).val( response.priority );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
    } );
</script>