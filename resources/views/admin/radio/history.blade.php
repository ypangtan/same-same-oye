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
