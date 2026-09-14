<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.radio_history' ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<?php
$columns = [
    [
        'type' => 'default',
        'id' => 'dt_no',
        'title' => 'No.',
    ],
    [
        'type' => 'date',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'radio.played_at' ) ] ),
        'id' => 'played_date',
        'title' => __( 'radio.played_at' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'radio.title' ) ] ),
        'id' => 'title',
        'title' => __( 'radio.title' ),
    ],
];
?>

<x-data-tables id="radio_history_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var dt_table,
    dt_table_name = '#radio_history_table',
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
            url: '{{ route( 'admin.radio.allHistory' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'radio_queue_items',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 1, 'desc' ]],
        columns: [
            { data: null },
            { data: 'played_at' },
            { data: 'title' },
        ],
        columnDefs: [
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                orderable: false,

                render: function (data, type, row, meta) {
                    const pageInfo = dt_table.page.info();
                    return pageInfo.start + meta.row + 1;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "played_date" ) }}' ),

                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "title" ) }}' ),

                render: function( data, type, row, meta ) {
                    return data ?? '-' ;
                },
            },
        ],
    },
    table_no = 0,
    timeout = null;

document.addEventListener( 'DOMContentLoaded', function() {

    $( '#played_date' ).flatpickr( {
        mode: 'range',
        disableMobile: true,
        onClose: function( selected, dateStr, instance ) {
            window[$( instance.element ).data('id')] = $( instance.element ).val();
            dt_table.draw();
        }
    } );
} );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>

<div class="nk-block-head nk-block-head-sm mt-4">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h4 class="nk-block-title">{{ __( 'radio.listener_ips' ) }}</h4>
        </div>
    </div>
</div>

<div class="listing-filter">
    <input type="text" class="form-control form-control-sm" style="max-width:220px;" id="listener_ip_search" placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'radio.listener_ip' ) ] ) }}" />
    <input type="text" class="form-control form-control-sm" style="max-width:220px;background-color:#fff;" id="listener_start_search" placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'radio.listener_start' ) ] ) }}" />
    <input type="text" class="form-control form-control-sm" style="max-width:220px;background-color:#fff;" id="listener_end_search" placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'radio.listener_end' ) ] ) }}" />
</div>

<div class="card card-bordered card-preview">
    <div class="card-inner">
        <table class="table" id="listener_ips_table" style="width:100%;">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>{{ __( 'radio.listener_ip' ) }}</th>
                    <th>{{ __( 'radio.listener_start' ) }}</th>
                    <th>{{ __( 'radio.listener_end' ) }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
document.addEventListener( 'DOMContentLoaded', function() {

    window.listener_ip = '';
    window.listener_start = '';
    window.listener_end = '';

    const formatMyt = function( data ) {
        return data ? new Intl.DateTimeFormat( 'en-GB', {
            timeZone: 'Asia/Kuala_Lumpur', dateStyle: 'medium', timeStyle: 'medium', hourCycle: 'h23',
        } ).format( new Date( data ) ) : @json( __( 'radio.listener_still_online' ) );
    };

    const listenerIpsDataTable = $( '#listener_ips_table' ).DataTable( {
        language: {
            lengthMenu: '{{ __( "datatables.lengthMenu" ) }}',
            zeroRecords: '{{ __( "datatables.zeroRecords" ) }}',
            info: '{{ __( "datatables.info" ) }}',
            infoEmpty: '{{ __( "datatables.infoEmpty" ) }}',
            infoFiltered: '{{ __( "datatables.infoFiltered" ) }}',
            paginate: {
                previous: '{{ __( "datatables.previous" ) }}',
                next: '{{ __( "datatables.next" ) }}',
            },
        },
        ajax: {
            type: 'POST',
            url: '{{ route( 'admin.radio.allListenerSessions' ) }}',
            data: function( d ) {
                d._token = '{{ csrf_token() }}';
                d.ip = window.listener_ip;
                d.connected_date = window.listener_start;
                d.disconnected_date = window.listener_end;
            },
        },
        lengthMenu: [5, 10, 25, 50, 100],
        pageLength: 10,
        processing: true,
        serverSide: true,
        searching: false,
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null, orderable: false, render: function( data, type, row, meta ) { return listenerIpsDataTable.page.info().start + meta.row + 1; } },
            { data: 'ip' },
            { data: 'connected_at', render: function( data ) { return formatMyt( data ); } },
            { data: 'disconnected_at', render: function( data ) { return formatMyt( data ); } },
        ],
        dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 text-end'l>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'mt-2 col-sm-12 col-md-5'i><'mt-2 col-sm-12 col-md-7 text-end'p>>",
        buttons: [
            {
                extend: 'copyHtml5', className: 'd-none listener-ips-buttons-copy',
                exportOptions: { modifier: { page: 'all' } },
            },
            {
                text: '<i class="fa fa-copy"></i>', className: 'btn btn-light', titleAttr: 'Copy All',
                action: function( e, dt ) {
                    dt.page.len( -1 ).draw();
                    dt.one( 'draw', function() {
                        $( '.listener-ips-buttons-copy' ).click();
                        setTimeout( function() { dt.page.len( 10 ).draw(); }, 1000 );
                    } );
                },
            },
            {
                extend: 'excelHtml5', className: 'd-none listener-ips-buttons-excel',
                exportOptions: { modifier: { page: 'all' } },
            },
            {
                text: '<i class="fa fa-file-excel"></i>', className: 'btn btn-success', titleAttr: 'Export to EXCEL',
                action: function( e, dt ) {
                    dt.page.len( -1 ).draw();
                    dt.one( 'draw', function() {
                        $( '.listener-ips-buttons-excel' ).click();
                        setTimeout( function() { dt.page.len( 10 ).draw(); }, 1000 );
                    } );
                },
            },
            {
                extend: 'csvHtml5', className: 'd-none listener-ips-buttons-csv',
                exportOptions: { modifier: { page: 'all' } },
            },
            {
                text: '<i class="fa fa-file-csv"></i>', className: 'btn btn-info', titleAttr: 'Export to CSV',
                action: function( e, dt ) {
                    dt.page.len( -1 ).draw();
                    dt.one( 'draw', function() {
                        $( '.listener-ips-buttons-csv' ).click();
                        setTimeout( function() { dt.page.len( 10 ).draw(); }, 1000 );
                    } );
                },
            },
            {
                extend: 'pdfHtml5', className: 'd-none listener-ips-buttons-pdf',
                exportOptions: { modifier: { page: 'all' } },
            },
            {
                text: '<i class="fa fa-file-pdf"></i>', className: 'btn btn-danger', titleAttr: 'Export to PDF',
                action: function( e, dt ) {
                    dt.page.len( -1 ).draw();
                    dt.one( 'draw', function() {
                        $( '.listener-ips-buttons-pdf' ).click();
                        setTimeout( function() { dt.page.len( 10 ).draw(); }, 1000 );
                    } );
                },
            },
        ],
    } );

    $( '#listener_ip_search' ).on( 'keydown keypress', function( e ) {
        clearTimeout( window.listenerIpTimeout );
        window.listenerIpTimeout = setTimeout( function() {
            window.listener_ip = $( '#listener_ip_search' ).val();
            listenerIpsDataTable.draw();
        }, 500 );
    } );

    $( '#listener_start_search' ).flatpickr( {
        mode: 'range', disableMobile: true,
        onClose: function( selected, dateStr ) {
            window.listener_start = dateStr;
            listenerIpsDataTable.draw();
        },
    } );

    $( '#listener_end_search' ).flatpickr( {
        mode: 'range', disableMobile: true,
        onClose: function( selected, dateStr ) {
            window.listener_end = dateStr;
            listenerIpsDataTable.draw();
        },
    } );
} );
</script>
