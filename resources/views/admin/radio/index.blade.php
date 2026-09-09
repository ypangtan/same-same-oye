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
                        <div class="stat-icon" id="radio_now_playing_dot" style="background:#e4e4e4;color:#777;overflow:hidden;padding:0;">
                            <em class="icon ni ni-music" id="radio_now_playing_icon"></em>
                            <img id="radio_now_playing_image" src="" alt="" hidden style="width:100%;height:100%;object-fit:cover;">
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
                <div class="card-body">
                    <div class="gap-3 d-flex align-items-center h-100">
                        <div class="stat-icon" style="background:#e3f2fd;color:#1565c0"><em class="icon ni ni-users"></em></div>
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
<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let listenerChart = new ApexCharts( document.querySelector( '#radio_listener_chart' ), {
            chart: { type: 'line', height: 260, toolbar: { show: false } },
            series: [ { name: '{{ __( "radio.live_listeners" ) }}', data: [] } ],
            xaxis: { type: 'category', labels: { rotate: -45 } },
            stroke: { curve: 'smooth', width: 2 },
            yaxis: { min: 0, forceNiceScale: true },
            noData: { text: '{{ __( "datatables.zeroRecords" ) }}' },
        } );
        listenerChart.render();

        function loadListenerGraph() {
            $.ajax( {
                url: '{{ route( 'admin.radio.listenerGraph' ) }}',
                type: 'POST',
                data: {
                    hours: $( '#radio_listener_graph_range' ).val(),
                    '_token': '{{ csrf_token() }}',
                },
                success: function( response ) {
                    listenerChart.updateOptions( { xaxis: { categories: response.labels } } );
                    listenerChart.updateSeries( [ { name: '{{ __( "radio.live_listeners" ) }}', data: response.data } ] );
                },
            } );
        }

        function loadNowPlaying() {
            $.ajax( {
                url: '{{ route( 'admin.radio.nowPlaying' ) }}',
                type: 'POST',
                data: { '_token': '{{ csrf_token() }}' },
                success: function( response ) {
                    $( '#radio_now_playing_title' ).text( response.title ?? '—' );
                    $( '#radio_listener_count' ).text( response.listeners );
                    $( '#radio_now_playing_dot' ).css( {
                        background: response.online ? '#e8f5e9' : '#e4e4e4',
                        color: response.online ? '#2e7d32' : '#777',
                    } );

                    if ( response.image ) {
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
