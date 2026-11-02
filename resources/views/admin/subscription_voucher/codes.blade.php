<?php
$subscription_voucher_code = 'subscription_voucher_code';
$voucher = $data['voucher'];
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'subscription_voucher.voucher_codes' ) }}{{ $voucher ? ' - ' . $voucher->name : '' }}</h3>
            @if ( $voucher )
            <div class="nk-block-des text-soft">
                <p>{{ $voucher->duration_in_months }} {{ __( 'subscription_voucher.months' ) }} · {{ __( 'subscription_voucher.remaining' ) }}: {{ $voucher->remainingQuantity() }} / {{ $voucher->quantity }}{!! $voucher->universal_code ? ' · ' . e( __( 'subscription_voucher.universal_code' ) ) . ': <span class="fw-bold" style="font-family: monospace;">' . e( $voucher->universal_code ) . '</span>' : '' !!}</p>
            </div>
            @endif
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<?php
$columns = [
    [
        'type' => 'default',
        'id' => 'select_row',
        'title' => '',
    ],
    [
        'type' => 'default',
        'id' => 'dt_no',
        'title' => 'No.',
    ],
    [
        'type' => 'default',
        'id' => 'created_date',
        'title' => __( 'datatables.created_date' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'subscription_voucher.code' ) ] ),
        'id' => 'code',
        'title' => __( 'subscription_voucher.code' ),
    ],
    [
        'type' => 'default',
        'id' => 'voucher',
        'title' => __( 'subscription_voucher.voucher' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'subscription_voucher.user' ) ] ),
        'id' => 'user',
        'title' => __( 'subscription_voucher.user' ),
    ],
    [
        'type' => 'select',
        'options' => $data['status'],
        'id' => 'status',
        'title' => __( 'datatables.status' ),
    ],
    [
        'type' => 'default',
        'id' => 'claimed_at',
        'title' => __( 'subscription_voucher.claimed_at' ),
    ],
    [
        'type' => 'default',
        'id' => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
];
?>

<x-data-tables id="subscription_voucher_code_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = {
        '1': {
            'text': '{{ __( 'subscription_voucher.available' ) }}',
            'color': 'badge rounded-pill bg-info',
        },
        '10': {
            'text': '{{ __( 'subscription_voucher.claimed' ) }}',
            'color': 'badge rounded-pill bg-success',
        },
        '20': {
            'text': '{{ __( 'subscription_voucher.voided' ) }}',
            'color': 'badge rounded-pill bg-danger',
        },
    },
    dt_table,
    dt_table_name = '#subscription_voucher_code_table',
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
            }
        },
        ajax: {
            url: '{{ route( 'admin.subscription_voucher.allSubscriptionVoucherCodes' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
                // Fixed filter when opened from a voucher
                'subscription_voucher': '{{ request( 'voucher' ) }}',
            },
            dataSrc: 'subscription_voucher_codes',
        },
        lengthMenu: [[10, 25, 50],[10, 25, 50]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'code' },
            { data: 'voucher' },
            { data: 'user' },
            { data: 'status' },
            { data: 'claimed_at' },
            { data: 'encrypted_id' },
        ],
        columnDefs: [
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "select_row" ) }}' ),
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return `<input type="checkbox" class="select-row" data-id="${row.encrypted_id}">`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                orderable: false,
                width: '1%',
                render: function (data, type, row, meta) {
                    const pageInfo = dt_table.page.info();
                    return pageInfo.start + meta.row + 1;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "code" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return '<span class="fw-bold" style="font-family: monospace;">' + data + '</span>';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "voucher" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return data ? $( '<div>' ).text( data.name ).html() : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "user" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    if ( !data ) {
                        return '<span class="text-soft">-</span>';
                    }
                    return ( data.calling_code ? data.calling_code : '+60' ) + ( data.phone_number ? data.phone_number : '-' ) + '<br><small>' + ( data.email ? data.email : '-' ) + '</small>';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return '<span class="' + statusMapper[data].color + '">' + statusMapper[data].text + '</span>';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "claimed_at" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                width: '1%',
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @can( 'edit subscription_vouchers' )
                    // Claimed code can't be voided
                    if ( row['status'] == 10 ) {
                        return '-';
                    }

                    let status = row['status'] == 1 ?
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'subscription_voucher.void' ) }}</span></a></li>' :
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="1"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'subscription_voucher.restore' ) }}</span></a></li>';

                    return `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+status+`
                                </ul>
                            </div>
                        </div>
                        `;
                    @else
                    return '-';
                    @endcan
                },
            },
        ],
    },
    table_no = 0,
    timeout = null;

    document.addEventListener( 'DOMContentLoaded', function() {

        let svcd = '#{{ $subscription_voucher_code }}';

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.subscription_voucher.updateSubscriptionVoucherCodeStatus' ) }}',
                type: 'POST',
                data: {
                    'id': $( this ).data( 'id' ),
                    'status': $( this ).data( 'status' ),
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    dt_table.draw( false );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                    modalDanger.toggle();
                },
            } );
        } );

    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
