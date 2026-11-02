<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.subscription_voucher_notifications' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add subscription_voucher_notifications' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.subscription_voucher_notification.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- .nk-block-head-content -->
        @endcan
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
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'datatables.title' ) ] ),
        'id' => 'title',
        'title' => __( 'datatables.title' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'subscription_voucher.voucher' ) ] ),
        'id' => 'subscription_voucher',
        'title' => __( 'subscription_voucher.voucher' ),
    ],
    [
        'type' => 'default',
        'id' => 'publishing_date',
        'title' => __( 'template.publishing_date' ),
    ],
    [
        'type' => 'default',
        'id' => 'claimed_count',
        'title' => __( 'subscription_voucher.claimed_count' ) . ' / ' . __( 'subscription_voucher.recipients' ),
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

<x-data-tables id="voucher_notification_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = {
        '1': {
            'text': '{{ __( 'datatables.scheduled' ) }}',
            'color': 'badge rounded-pill bg-warning',
        },
        '10': {
            'text': '{{ __( 'subscription_voucher.published' ) }}',
            'color': 'badge rounded-pill bg-success',
        },
        '20': {
            'text': '{{ __( 'subscription_voucher.unpublished' ) }}',
            'color': 'badge rounded-pill bg-danger',
        },
    },
    dt_table,
    dt_table_name = '#voucher_notification_table',
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
            url: '{{ route( 'admin.subscription_voucher_notification.allVoucherNotifications' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'notifications',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'en_title' },
            { data: 'subscription_voucher' },
            { data: 'publishing_date' },
            { data: 'claimed_count' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "title" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return $( '<div>' ).text( data ?? '-' ).html();
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "subscription_voucher" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return data ? $( '<div>' ).text( data.name ).html() : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "publishing_date" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return data ?? '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "claimed_count" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    // Third party has no claim
                    if ( row.subscription_voucher && row.subscription_voucher.type == {{ \App\Models\SubscriptionVoucher::TYPE_THIRD_PARTY }} ) {
                        return '{{ __( 'subscription_voucher.sent' ) }}: ' + row.recipient_count;
                    }
                    return data + ' / ' + row.recipient_count;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return statusMapper[data] ? '<span class="' + statusMapper[data].color + '">' + statusMapper[data].text + '</span>' : '-';
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                width: '1%',
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @can( 'edit subscription_voucher_notifications' )
                    let edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>',
                        // Scheduled: wait for publishing date, no publish / unpublish
                        status = row['status'] == 1 ? '' : row['status'] == 10 ?
                        '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'subscription_voucher.unpublish' ) }}</span></a></li>' :
                        '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'subscription_voucher.publish' ) }}</span></a></li>';

                    return `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
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

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.subscription_voucher_notification.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.subscription_voucher_notification.updateVoucherNotificationStatus' ) }}',
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
