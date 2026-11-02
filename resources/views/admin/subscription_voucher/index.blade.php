<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.subscription_vouchers' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.subscription_voucher.codes' ) }}" class="btn btn-outline-primary">{{ __( 'subscription_voucher.voucher_codes' ) }}</a>
                        </li>
                        @can( 'add subscription_vouchers' )
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.subscription_voucher.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </div>
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
        'type' => 'date',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'datatables.created_date' ) ] ),
        'id' => 'created_date',
        'title' => __( 'datatables.created_date' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'subscription_voucher.name' ) ] ),
        'id' => 'name',
        'title' => __( 'subscription_voucher.name' ),
    ],
    [
        'type' => 'select',
        'options' => $data['type'],
        'id' => 'type',
        'title' => __( 'subscription_voucher.type' ),
    ],
    [
        'type' => 'default',
        'id' => 'duration_in_months',
        'title' => __( 'subscription_voucher.duration_in_months' ),
    ],
    [
        'type' => 'default',
        'id' => 'quantity',
        'title' => __( 'subscription_voucher.issued' ) . ' / ' . __( 'subscription_voucher.quantity' ),
    ],
    [
        'type' => 'default',
        'id' => 'claimed_count',
        'title' => __( 'subscription_voucher.claimed_count' ),
    ],
    [
        'type' => 'default',
        'id' => 'claim_period',
        'title' => __( 'subscription_voucher.start_date' ) . ' - ' . __( 'subscription_voucher.end_date' ),
    ],
    [
        'type' => 'select',
        'options' => $data['status'],
        'id' => 'status',
        'title' => __( 'datatables.status' ),
    ],
    [
        'type' => 'default',
        'id' => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
];
?>

<x-data-tables id="subscription_voucher_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = {
        '10': {
            'text': '{{ __( 'datatables.activated' ) }}',
            'color': 'badge rounded-pill bg-success',
        },
        '20': {
            'text': '{{ __( 'datatables.suspended' ) }}',
            'color': 'badge rounded-pill bg-danger',
        },
    },
    typeMapper = @json( $data['type'] ),
    isThirdParty = function( row ) {
        return row.type == {{ \App\Models\SubscriptionVoucher::TYPE_THIRD_PARTY }};
    },
    dt_table,
    dt_table_name = '#subscription_voucher_table',
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
            url: '{{ route( 'admin.subscription_voucher.allSubscriptionVouchers' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'subscription_vouchers',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'name' },
            { data: 'type' },
            { data: 'duration_in_months' },
            { data: 'quantity' },
            { data: 'claimed_count' },
            { data: 'start_date' },
            { data: 'status' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "name" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return $( '<div>' ).text( data ?? '-' ).html();
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "type" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return typeMapper[data] ?? '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "duration_in_months" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return isThirdParty( row ) ? '-' : data + ' {{ __( 'subscription_voucher.months' ) }}';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "quantity" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    // Third party: users sent / urls added
                    if ( isThirdParty( row ) ) {
                        return row.issued_count + ' <small class="text-soft">( ' + row.url_count + ' {{ __( 'subscription_voucher.urls' ) }} )</small>';
                    }
                    return row.issued_count + ' / ' + data;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "claimed_count" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return isThirdParty( row ) ? '-' : data;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "claim_period" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    if ( isThirdParty( row ) ) {
                        return '-';
                    }
                    return ( row.start_date ?? '-' ) + '<br>' + ( row.end_date ?? '-' );
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
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                width: '1%',
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    let edit = '', status = '',
                        codes = isThirdParty( row ) ?
                        '<li class="dt-urls" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-link"></em><span>{{ __( 'subscription_voucher.manage_urls' ) }}</span></a></li>' :
                        '<li class="dt-codes" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-ticket"></em><span>{{ __( 'subscription_voucher.view_codes' ) }}</span></a></li>';

                    @can( 'edit subscription_vouchers' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    status = row['status'] == 10 ?
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>' :
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>';
                    @endcan

                    let html =
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+codes+`
                                    `+edit+`
                                    `+status+`
                                </ul>
                            </div>
                        </div>
                        `;
                        return html;
                },
            },
        ],
    },
    table_no = 0,
    timeout = null;

    document.addEventListener( 'DOMContentLoaded', function() {

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.subscription_voucher.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-codes', function() {
            window.location.href = '{{ route( 'admin.subscription_voucher.codes' ) }}?voucher=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-urls', function() {
            window.location.href = '{{ route( 'admin.subscription_voucher.urls' ) }}?voucher=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.subscription_voucher.updateSubscriptionVoucherStatus' ) }}',
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
            } );
        } );
    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
