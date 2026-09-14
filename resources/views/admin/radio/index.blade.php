<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.radio_queue' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add radios' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.radio.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- .nk-block-head-content -->
        @endcan
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="nk-block mb-4">
    <div class="row g-3">
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="gap-3 d-flex align-items-center h-100">
                        <div class="stat-icon" id="radio_now_playing_dot" style="background:#e4e4e4;color:#777;overflow:hidden;padding:0;width:50px;height:50px;min-width:50px;flex:0 0 50px;display: flex;align-content: center;justify-content: center;flex-wrap: wrap;">
                            <em class="icon ni ni-music" id="radio_now_playing_icon"></em>
                            <img id="radio_now_playing_image" src="" alt="" hidden width="50" height="50" style="width:50px;height:50px;object-fit:cover;">
                        </div>
                        <div>
                            <div class="stat-value" id="radio_now_playing_title" style="font-size:1rem;">—</div>
                            <div class="stat-label">{{ __( 'radio.now_playing' ) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card stat-card h-100">
                <div class="card-body" id="radio_listeners_open" role="button" tabindex="0" aria-haspopup="dialog" aria-controls="radio_listeners_modal" style="cursor:pointer;">
                    <div class="gap-3 d-flex align-items-center h-100">
                        <div class="stat-icon" style="background:#daebff;color:#1565c0;overflow:hidden;padding:0;width:50px;height:50px;min-width:50px;flex:0 0 50px;display: flex;align-content: center;justify-content: center;flex-wrap: wrap;"><em class="icon ni ni-users"></em></div>
                        <div>
                            <div class="stat-value" id="radio_listener_count">—</div>
                            <div class="stat-label">{{ __( 'radio.live_listeners' ) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-inner">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="card-title mb-0">{{ __( 'radio.listener_graph' ) }}</h6>
            <select id="radio_listener_graph_range" class="form-select form-select-sm w-auto">
                <option value="24">{{ __( 'radio.last_24_hours' ) }}</option>
                <option value="168">{{ __( 'radio.last_7_days' ) }}</option>
            </select>
        </div>
        <div id="radio_listener_chart"></div>
    </div>
</div>

<script src="{{ asset( 'admin/js/apexcharts.min.js' ) }}"></script>
<div class="modal fade" id="radio_listeners_modal" tabindex="-1" aria-labelledby="radio_listeners_heading" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="radio_listeners_heading">{{ __( 'radio.live_listeners' ) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="radio_listeners_message" role="status" class="text-soft small"></p>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <input type="text" class="form-control form-control-sm" style="max-width:220px;" id="radio_listeners_search_ip" placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'radio.listener_ip' ) ] ) }}" />
                </div>
                <div class="card card-bordered card-preview">
                    <div class="card-inner">
                        <table class="table" id="radio_listeners_table" style="width:100%;">
                            <thead><tr><th>No.</th><th>{{ __( 'radio.listener_ip' ) }}</th><th>{{ __( 'radio.listener_connected_at' ) }}</th></tr></thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        const listenersElement = document.getElementById( 'radio_listeners_modal' );
        const listenersModal = new bootstrap.Modal( listenersElement );
        let listenersDataTable, listenersRequest, listenersTimer;

        function initListenersTable() {
            if ( listenersDataTable ) return;
            listenersDataTable = $( '#radio_listeners_table' ).DataTable( {
                data: [],
                columns: [
                    { data: null, orderable: false, render: function( data, type, row, meta ) { return meta.row + 1; } },
                    { data: 'ip' },
                    {
                        data: 'connected_at',
                        render: function( data ) {
                            return data ? new Intl.DateTimeFormat( 'en-GB', {
                                timeZone: 'Asia/Kuala_Lumpur', dateStyle: 'medium', timeStyle: 'medium', hourCycle: 'h23',
                            } ).format( new Date( data ) ) : '-';
                        },
                    },
                ],
                order: [[ 2, 'desc' ]],
                lengthMenu: [5, 10, 25, 50],
                pageLength: 10,
                searching: true,
                autoWidth: false,
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
                dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 text-end'l>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'mt-2 col-sm-12 col-md-5'i><'mt-2 col-sm-12 col-md-7 text-end'p>>",
                buttons: [
                    { extend: 'copyHtml5', text: '<i class="fa fa-copy"></i>', className: 'btn btn-light', titleAttr: 'Copy All', exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'excelHtml5', text: '<i class="fa fa-file-excel"></i>', className: 'btn btn-success', titleAttr: 'Export to EXCEL', exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'csvHtml5', text: '<i class="fa fa-file-csv"></i>', className: 'btn btn-info', titleAttr: 'Export to CSV', exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'pdfHtml5', text: '<i class="fa fa-file-pdf"></i>', className: 'btn btn-danger', titleAttr: 'Export to PDF', exportOptions: { modifier: { page: 'all' } } },
                ],
            } );

            $( '#radio_listeners_search_ip' ).on( 'keyup', function() {
                listenersDataTable.column( 1 ).search( this.value ).draw();
            } );
        }

        function loadListeners() {
            if ( listenersRequest ) return;
            listenersRequest = $.ajax( {
                url: '{{ route( 'admin.radio.listeners' ) }}', type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function( response ) {
                    const summary = response.summary;
                    let message = @json( __( 'radio.listeners_empty' ) );
                    if ( !summary.enabled ) message = @json( __( 'radio.listeners_disabled' ) );
                    else if ( !summary.fresh ) message = @json( __( 'radio.ip_stale' ) );
                    else if ( response.listeners.length ) message = '';
                    $( '#radio_listeners_message' ).text( message );
                    listenersDataTable.clear();
                    if ( summary.fresh ) listenersDataTable.rows.add( response.listeners );
                    listenersDataTable.draw( false );
                },
                error: function( xhr, status ) {
                    if ( status !== 'abort' ) $( '#radio_listeners_message' ).text( @json( __( 'radio.listeners_failed' ) ) );
                },
                complete: function() { listenersRequest = null; },
            } );
        }
        $( '#radio_listeners_open' ).on( 'click', function() { listenersModal.show(); } ).on( 'keydown', function( event ) {
            if ( event.key === 'Enter' || event.key === ' ' ) { event.preventDefault(); listenersModal.show(); }
        } );
        listenersElement.addEventListener( 'shown.bs.modal', function() {
            initListenersTable();
            loadListeners();
            listenersTimer = setInterval( loadListeners, 5000 );
        } );
        listenersElement.addEventListener( 'hidden.bs.modal', function() {
            clearInterval( listenersTimer );
            if ( listenersRequest ) listenersRequest.abort();
            document.getElementById( 'radio_listeners_open' ).focus();
        } );

        function graphTime( value, full = false ) {
            const date = new Date( Number( value ) );
            if ( !Number.isFinite( date.getTime() ) ) return '';
            const options = { timeZone: 'Asia/Kuala_Lumpur', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' };
            if ( full || $( '#radio_listener_graph_range' ).val() === '168' ) {
                options.day = '2-digit';
                options.month = 'short';
            }
            return new Intl.DateTimeFormat( 'en-GB', options ).format( date );
        }

        let graphRequest;
        let listenerChart = new ApexCharts( document.querySelector( '#radio_listener_chart' ), {
            chart: { type: 'area', height: 300, toolbar: { show: false }, zoom: { enabled: false }, animations: { enabled: false } },
            series: [ { name: '{{ __( "radio.live_listeners" ) }}', data: [] } ],
            colors: [ '#3b82f6' ],
            xaxis: {
                type: 'numeric', tickAmount: 6,
                labels: { rotate: 0, hideOverlappingLabels: true, formatter: value => graphTime( value ), style: { colors: '#8091a7', fontSize: '11px' } },
                axisBorder: { show: false }, axisTicks: { show: false },
                tooltip: { enabled: false },
            },
            stroke: { curve: 'stepline', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.25, opacityTo: 0.03 } },
            markers: { size: 0, hover: { size: 5 } },
            grid: { borderColor: '#edf0f5', strokeDashArray: 4 },
            yaxis: { min: 0, max: 1, tickAmount: 1, labels: { formatter: value => Math.round( value ).toLocaleString() } },
            tooltip: { x: { formatter: value => graphTime( value, true ) }, y: { formatter: value => Math.round( value ).toLocaleString() } },
            dataLabels: { enabled: false },
            responsive: [ { breakpoint: 576, options: { chart: { height: 250 }, xaxis: { tickAmount: 3 } } } ],
            noData: { text: '{{ __( "datatables.zeroRecords" ) }}' },
        } );
        listenerChart.render();

        function loadListenerGraph() {
            if ( graphRequest ) graphRequest.abort();
            graphRequest = $.ajax( {
                url: '{{ route( 'admin.radio.listenerGraph' ) }}',
                type: 'POST',
                data: {
                    hours: $( '#radio_listener_graph_range' ).val(),
                    '_token': '{{ csrf_token() }}',
                },
                success: function( response ) {
                    const points = response.data.map( ( value, index ) => ({ x: response.timestamps[index], y: Number( value ) }) );
                    const peak = Math.max( 1, ...response.data.map( Number ) );
                    const step = Math.max( 1, Math.ceil( peak / 4 ) );
                    const maximum = Math.ceil( peak / step ) * step;
                    listenerChart.updateOptions( {
                        yaxis: { min: 0, max: maximum, tickAmount: maximum / step },
                        series: [ { name: '{{ __( "radio.live_listeners" ) }}', data: points } ],
                    } );
                },
            } );
        }

        function loadNowPlaying() {
            $.ajax( {
                url: '{{ route( 'admin.radio.nowPlaying' ) }}',
                type: 'POST',
                data: { '_token': '{{ csrf_token() }}' },
                success: function( response ) {
                    $( '#radio_now_playing_title' ).text( response.title || '-' );
                    $( '#radio_listener_count' ).text( response.listeners );
                    
                    $( '#radio_now_playing_dot' ).css( {
                        background: response.online ? '#e8f5e9' : '#e4e4e4',
                        color: response.online ? '#2e7d32' : '#777',
                    } );

                    if ( response.title && response.image ) {
                        $( '#radio_now_playing_image' ).attr( 'src', response.image ).prop( 'hidden', false );
                        $( '#radio_now_playing_icon' ).prop( 'hidden', true );
                    } else {
                        $( '#radio_now_playing_image' ).prop( 'hidden', true ).attr( 'src', '' );
                        $( '#radio_now_playing_icon' ).prop( 'hidden', false );
                    }
                },
            } );
        }

        loadListenerGraph();
        loadNowPlaying();

        $( '#radio_listener_graph_range' ).change( loadListenerGraph );

        // Live-ish panel: refresh now-playing/listener count every 15s, graph every 5 min.
        setInterval( loadNowPlaying, 15000 );
        setInterval( loadListenerGraph, 300000 );
    } );
</script>

<?php
$enableReorder = 1;

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
        'type' => 'default',
        'id' => 'image',
        'title' => __( 'radio.image' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'radio.title' ) ] ),
        'id' => 'title',
        'title' => __( 'radio.title' ),
    ],
    [
        'type' => 'default',
        'id' => 'duration',
        'title' => __( 'radio.duration' ),
    ],
    [
        'type' => 'default',
        'id' => 'status',
        'title' => __( 'radio.status' ),
    ],
    [
        'type' => 'default',
        'id' => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
];

if ( $enableReorder == 1 ) {
    array_unshift( $columns,  [
        'type' => 'default',
        'id' => 'dt_reorder',
        'title' => '',
        'reorder' => 'yes',
    ] );
}
?>

<x-data-tables id="radio_queue_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = {
        10: '{{ __( "datatables.pending" ) }}',
        20: '{{ __( "radio.reserved" ) }}',
        25: '{{ __( "radio.now_playing" ) }}',
    },
    dt_table,
    dt_table_name = '#radio_queue_table',
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
            url: '{{ route( 'admin.radio.allItems' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'radio_queue_items',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'image_url', defaultContent: '' },
            { data: 'title' },
            { data: 'display_duration' },
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

                render: function (data, type, row, meta) {
                    const pageInfo = dt_table.page.info();
                    return pageInfo.start + meta.row + 1;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),

                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "image" ) }}' ),
                orderable: false,
                searchable: false,
                width: '50px',
                render: function( data, type ) {
                    if ( type !== 'display' ) return '';
                    if ( !data ) return '-';
                    return $( '<img>' ).attr( {
                        src: data,
                        alt: '',
                        width: 50,
                        height: 50,
                        loading: 'lazy',
                    } ).css( {
                        width: '50px',
                        height: '50px',
                        objectFit: 'cover',
                        borderRadius: '4px',
                    } )[0].outerHTML;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "title" ) }}' ),

                render: function( data, type, row, meta ) {
                    return data ?? '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "duration" ) }}' ),
                orderable: false,

                render: function( data, type, row, meta ) {
                    return data ?? '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return statusMapper[data] ?? '-';
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,

                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @can( 'delete radios' )
                    let html =
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    <li class="dt-delete" data-id="` + row['encrypted_id'] + `"><a href="#"><em class="icon ni ni-trash"></em><span>{{ __( 'datatables.delete' ) }}</span></a></li>
                                </ul>
                            </div>
                        </div>
                        `;
                    return html;
                    @else
                    return '-';
                    @endcan
                },
            },
        ],
    },
    table_no = 0,
    timeout = null,
    reorderPath = '{{ route( 'admin.radio.reorder' ) }}';

if ( parseInt( '{{ $enableReorder }}' ) == 1 ) {

    dt_table_config.rowReorder = {
        selector: '.dt-reorder',
        dataSrc: 'position',
        update: false,
    };

    dt_table_config.order[0] = [ 3, 'desc' ],
    dt_table_config.columns.unshift( {
        data: 'encrypted_id'
    } );
    dt_table_config.columnDefs.unshift( {
        targets: 0,
        orderable: false,
        render: function( data, type, row, meta ) {
            return `<div class="dt-reorder" style="width: 20px" data-id="${data}" />
                <i class="align-middle feather" icon-name="move" style="color: #5f5f5f;"></i>
            </div>`;
        },
    } );
}

document.addEventListener( 'DOMContentLoaded', function() {

    $( '#created_date' ).flatpickr( {
        mode: 'range',
        disableMobile: true,
        onClose: function( selected, dateStr, instance ) {
            window[$( instance.element ).data('id')] = $( instance.element ).val();
            dt_table.draw();
        }
    } );

    $( document ).on( 'click', '.dt-delete', function() {
        $( 'body' ).loading( {
            message: '{{ __( 'template.loading' ) }}'
        } );

        $.ajax( {
            url: '{{ route( 'admin.radio.deleteItem' ) }}',
            type: 'POST',
            data: {
                'id': $( this ).data( 'id' ),
                '_token': '{{ csrf_token() }}'
            },
            success: function( response ) {
                dt_table.draw( false );
                $( '#modal_success .caption-text' ).html( response.message );
                modalSuccess.toggle();
                $( 'body' ).loading( 'stop' );
            },
        } );
    } );
} );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
