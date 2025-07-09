<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.wallets' ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<?php
$wallet_topup = 'wallet_topup';
$wallet_deduct = 'wallet_deduct';

$multiSelect = 1;
?>

<?php

$columns = [
    [
        'type' => 'default',
        'id' => 'select_row',
        'title' => 'No.',
    ],
    [
        'type' => 'default',
        'id' => 'dt_no',
        'title' => 'No.',
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'wallet.user' ) ] ),
        'id' => 'user',
        'title' => __( 'wallet.user' ),
    ],
    [
        'type' => 'default',
        'id' => 'wallet',
        'title' => __( 'wallet.wallet' ),
    ],
    [
        'type' => 'default',
        'id' => 'balance',
        'title' => __( 'wallet.balance' ),
    ],
    [
        'type' => 'default',
        'id' => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
];

if ( $multiSelect ) {
    array_unshift( $columns,  [
        'type' => 'default',
        'id' => 'dt_multiselect',
        'title' => '',
        'multi_select' => 'yes',
    ] );
}
?>

<div class="card">
    <div class="card-body">
        <x-data-tables id="wallet_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />
    </div>
</div>
</div>

<div class="modal fade" id="wallet_multi_topup_modal">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{!! __( 'wallet.adjust_balance_x', [ 'title' => __( 'wallet.topup' ) ] ) !!}</h5>
            </div>
            <div class="modal-body">
                <div class="mb-3 row">
                    <strong>Selected User:</strong>
                    <div id="users"></div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $wallet_topup }}_multi_amount" class="col-sm-5 col-form-label">{{ __( 'wallet.amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $wallet_topup }}_multi_amount">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $wallet_topup }}_multi_remark" class="col-sm-5 col-form-label">{{ __( 'wallet.remark' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $wallet_topup }}_multi_remark">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button type="button" class="btn btn-sm btn-primary" id="{{ $wallet_topup }}_multi_submit">{{ __( 'template.confirm' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="wallet_topup_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{!! __( 'wallet.adjust_balance_x', [ 'title' => __( 'wallet.topup' ) ] ) !!}</h5>
                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <em class="icon ni ni-cross"></em>
                </a>
            </div>
            <div class="modal-body">
                <div class="mb-3 row">
                    <label for="{{ $wallet_topup }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'wallet.user' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $wallet_topup }}_phone_number" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $wallet_topup }}_balance" class="col-sm-5 col-form-label">{{ __( 'wallet.balance' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $wallet_topup }}_balance" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $wallet_topup }}_amount" class="col-sm-5 col-form-label">{{ __( 'wallet.amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $wallet_topup }}_amount">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $wallet_topup }}_balance_after_submit" class="col-sm-5 col-form-label">{{ __( 'wallet.balance_after_submit' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $wallet_topup }}_balance_after_submit" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $wallet_topup }}_remark" class="col-sm-5 col-form-label">{{ __( 'wallet.remark' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $wallet_topup }}_remark">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <input type="hidden" id="{{ $wallet_topup }}_id">
            <div class="modal-footer">
                <div class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button type="button" class="btn btn-sm btn-primary" id="{{ $wallet_topup }}_submit">{{ __( 'template.confirm' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="wallet_deduct_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{!! __( 'wallet.adjust_balance_x', [ 'title' => __( 'wallet.deduct' ) ] ) !!}</h5>
                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <em class="icon ni ni-cross"></em>
                </a>
            </div>
            <div class="modal-body">
                <div class="mb-3 row">
                    <label for="{{ $wallet_deduct }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'wallet.user' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $wallet_deduct }}_phone_number" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $wallet_deduct }}_balance" class="col-sm-5 col-form-label">{{ __( 'wallet.balance' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $wallet_deduct }}_balance" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $wallet_deduct }}_amount" class="col-sm-5 col-form-label">{{ __( 'wallet.amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $wallet_deduct }}_amount">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $wallet_deduct }}_balance_after_submit" class="col-sm-5 col-form-label">{{ __( 'wallet.balance_after_submit' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $wallet_deduct }}_balance_after_submit" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $wallet_deduct }}_remark" class="col-sm-5 col-form-label">{{ __( 'wallet.remark' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $wallet_deduct }}_remark">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <input type="hidden" id="{{ $wallet_deduct }}_id">
            <div class="modal-footer">
                <div class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button type="button" class="btn btn-sm btn-primary" id="{{ $wallet_deduct }}_submit">{{ __( 'template.confirm' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    window['columns'] = @json( $columns );
    window['ids'] = [];
        
    @foreach ( $columns as $column )
    @if ( $column['type'] != 'default' )
    window['{{ $column['id'] }}'] = '';
    @endif
    @endforeach

    var walletMapper = {
        '1': '{{ __( 'wallet.wallet_1' ) }}',
        '2': '{{ __( 'wallet.wallet_2' ) }}',
        },
        dt_table,
        dt_table_name = '#wallet_table',
        dt_table_config = {
            language: {
                'lengthMenu': '{{ __( "datatables.lengthMenu" ) }}',
                'zeroRecords': '{{ __( "datatables.zeroRecords" ) }}',
                'info': '{{ __( "datatables.info" ) }}',
                'infoEmpty': '{{ __( "datatables.infoEmpty" ) }}',
                'infoFiltered': '{{ __( "datatables.infoFiltered" ) }}',
                'paginate': {
                    'previous': '{{ __( "datatables.previous" ) }}',
                    'next': '{{ __( "datatables.next" ) }}',
                },
                'select': {
                    'rows': {
                        0: "",
                        _: "{{ __( "datatables.rows" ) }}",
                    }
                },
            },
            ajax: {
                url: '{{ route( 'admin.wallet.allWallets' ) }}',
                data: {
                    '_token': '{{ csrf_token() }}',
                },
                dataSrc: 'wallets',
            },
            lengthMenu: [
                [ 10, 25, 50, 999999 ],
                [ 10, 25, 50, '{{ __( 'datatables.all' ) }}' ]
            ],
            order: false,
            columns: [
                { data: null },
                { data: null },
                { data: 'user' },
                { data: 'type' },
                { data: 'listing_balance' },
                { data: 'encrypted_id' },
            ],
            columnDefs: [
                {
                    // Add checkboxes to the first column
                    targets: 1,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        return `<input type="checkbox" class="select-row" data-id="${row.encrypted_id}">`;
                    },
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                    orderable: false,
                    
                    render: function (data, type, row, meta) {
                        // Calculate the row number dynamically based on the page info
                        const pageInfo = dt_table.page.info();
                        return pageInfo.start + meta.row + 1; // Adjust for 1-based numbering
                    },
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "user" ) }}' ),
                    orderable: false,
                    render: function( data, type, row, meta ) {
                        return ( data.first_name ?? '-' ) + ' ' + ( data.last_name ?? '' ) + '<br>' + '+60' + data.phone_number;
                    },
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "wallet" ) }}' ),
                    orderable: false,
                    render: function( data, type, row, meta ) {
                        return walletMapper[data];
                    },
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "balance" ) }}' ),
                    orderable: false,
                    className: 'text-end',
                    render: function( data, type, row, meta ) {
                        return data;
                    },
                },
                {
                    targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                    orderable: false,
                    width: '10%',
                    className: 'text-center',
                    render: function( data, type, row, meta ) {

                        @can( 'edit wallets' )

                        let edit = '';

                        edit += '<li class="dropdown-item click-action dt-topup" data-id="' + data + '">{{ __( 'wallet.topup' ) }}</li>';
                        edit += '<li class="dropdown-item click-action dt-deduct" data-id="' + data + '">{{ __( 'wallet.deduct' ) }}</li>';
                        let html = 
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                             <ul class="link-list-opt">
                                `+edit+`
                                `+status+`
                            </ul>
                        </div>
                        `;
                        return html;
                        @else
                        return '<i class="text-secondary" icon-name="more-horizontal" data-bs-toggle="dropdown"></i>';
                        @endcan
                    },
                },
            ],
            select: {
                style: 'multi',
            },
        },
        table_no = 0,
        timeout = null;

if ( parseInt( '{{ $multiSelect }}' ) == 1 ) {

    dt_table_config.select.style = 'multi';
    dt_table_config.select['selector'] = 'td:first-child > input';

    dt_table_config.order[0] = [ 2, 'desc' ],
    dt_table_config.columns.unshift( {
        data: 'id'
    } );
    dt_table_config.columnDefs.unshift( {
        targets: 0,
        orderable: false,
        render: function( data, type, row, meta ) {
            return '<input class="dt-multiselect" type="checkbox" style="width: 100%" data-id="' + data + '" />';
        },
    } );
}

document.addEventListener( 'DOMContentLoaded', function() {

    let wt = '#{{ $wallet_topup }}',
        wd = '#{{ $wallet_deduct }}',
        wmtm = new bootstrap.Modal( document.getElementById( 'wallet_multi_topup_modal' ) ),
        wtm = new bootstrap.Modal( document.getElementById( 'wallet_topup_modal' ) ),
        wdm = new bootstrap.Modal( document.getElementById( 'wallet_deduct_modal' ) ),
        multiTopupHTML = '',
        priceFormatter = new Intl.NumberFormat( 'en', {
            maximumFractionDigits: 2, 
            minimumFractionDigits: 2, 
        } );

    multiTopupHTML += 
    `
    <button id="multiselect_topup" type="button" class="btn btn-sm btn-outline-primary">{{ __( 'wallet.topup' ) }}</button>
    `;

    $( '.multiselect-action > div' ).append( multiTopupHTML );

    $( document ).on( 'click', '#multiselect_topup', function() {

        console.log( window['ids'] );

        let users = [];

        $( 'input.dt-multiselect' ).each( function( i, v ) {

            if ( $( v ).is( ':checked' ) ) {
                users.push( $( v ).parent().next().next().html() );
            }
        } );

        $( '#users' ).html( users.join( '<br>' ) );

        wmtm.show();
    } );

    $( wt + '_multi_submit' ).click( function() {

        multiSubmit( wt );
    } );

    $( document ).on( 'click', '.dt-topup', function() {

        let id = $( this ).data( 'id' );

        getBalance( id, wt );
    } );

    $( document ).on( 'click', '.dt-deduct', function() {

        let id = $( this ).data( 'id' );

        getBalance( id, wd );
    } );

    $( wt + '_submit' ).click( function() {

        submit( wt );    
    } );

    $( wd + '_submit' ).click( function() {

        submit( wd );
    } );

    $( wt + '_amount' ).on( 'change keyup', function() {

        let amount = parseFloat( $( this ).val() ),
            currentBalance = parseFloat( $( wt + '_balance' ).val().replace( ',', '' ) );

        if ( isNaN( amount ) ) {
            amount = 0;
        }

        $( wt + '_balance_after_submit' ).val( priceFormatter.format( currentBalance + amount ) );
    } );

    $( wd + '_amount' ).on( 'change keyup', function() {

        let amount = parseFloat( $( this ).val() ),
            currentBalance = parseFloat( $( wd + '_balance' ).val().replace( ',', '' ) );

        if ( isNaN( amount ) ) {
            amount = 0;
        }

        $( wd + '_balance_after_submit' ).val( priceFormatter.format( currentBalance - amount ) );
    } );

    function getBalance( id, scope ) {

        $.ajax( {
            url: '{{ route( 'admin.wallet.oneWallet' ) }}',
            type: 'POST',
            data: { id, '_token': '{{ csrf_token() }}', },
            success: function( response ) {
                
                $( scope + '_id' ).val( response.encrypted_id );
                $( scope + '_phone_number' ).val( response.user.phone_number );
                $( scope + '_balance' ).val( response.listing_balance );
                $( scope + '_balance_after_submit' ).val( response.listing_balance );

                scope == '#wallet_topup' ? wtm.show() : wdm.show();
            },
        } );
    }

    function submit( scope ) {

        $.ajax( {
            url: '{{ route( 'admin.wallet.updateWallet' ) }}',
            type: 'POST',
            data: {
                'id': $( scope + '_id' ).val(),
                'amount': $( scope + '_amount' ).val(),
                'remark': $( scope + '_remark' ).val(),
                'action': scope == '#wallet_topup' ? 'topup' : 'deduct',
                '_token': '{{ csrf_token() }}',
            },
            success: function( response ) {
                
                scope == '#wallet_topup' ? wtm.hide() : wdm.hide();
                
                $( 'body' ).loading( 'stop' );
                $( '#modal_success .caption-text' ).html( response.message );
                modalSuccess.toggle();

                dt_table.draw( false );
            },
            error: function( error ) {
                $( 'body' ).loading( 'stop' );

                if ( error.status === 422 ) {
                    let errors = error.responseJSON.errors;
                    $.each( errors, function( key, value ) {
                        $( scope + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                    } );
                } else {
                    scope == '#wallet_topup' ? wtm.hide() : wdm.hide();
                    $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                    modalDanger.toggle();
                }
            }
        } );
    }

    function multiSubmit( scope ) {

        $.ajax( {
            url: '{{ route( 'admin.wallet.updateWalletMultiple' ) }}',
            type: 'POST',
            data: {
                'ids': window['ids'],
                'amount': $( scope + '_multi_amount' ).val(),
                'remark': $( scope + '_multi_remark' ).val(),
                'action': scope == '#wallet_topup' ? 'topup' : 'deduct',
                '_token': '{{ csrf_token() }}',
            },
            success: function( response ) {
                
                scope == '#wallet_topup' ? wmtm.hide() : wdm.hide();
                
                $( 'body' ).loading( 'stop' );
                $( '#modal_success .caption-text' ).html( response.message );
                modalSuccess.toggle();

                dt_table.draw( false );
            },
            error: function( error ) {
                $( 'body' ).loading( 'stop' );

                if ( error.status === 422 ) {
                    let errors = error.responseJSON.errors;
                    $.each( errors, function( key, value ) {
                        console.log( scope + '_multi_' + key );
                        $( scope + '_multi_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                    } );
                } else {
                    scope == '#wallet_topup' ? wmtm.hide() : wdm.hide();
                    $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                    modalDanger.toggle();
                }
            }
        } );
    }
} );

</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>