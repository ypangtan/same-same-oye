<?php
$type = $data['type'] ?? null;
$parent_route = $data['parent_route'] ?? null;
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.items' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add items' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.module_parent.item.add' ) . '?type=' . $type . '&parent_route=' . $parent_route }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
        'type' => 'default',
        'id' => 'image',
        'title' => __( 'item.image' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'item.title' ) ] ),
        'id' => 'title',
        'title' => __( 'item.title' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'category.type' ) ] ),
        'id' => 'type',
        'title' => __( 'category.type' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'item.author' ) ] ),
        'id' => 'author',
        'title' => __( 'item.author' ),
    ],
    [
        'type' => 'select',
        'options' => $data['status'],
        'id' => 'status',
        'title' => __( 'datatables.status' ),
    ],
    [
        'type' => 'default',
        'id' => 'likes',
        'title' => __( 'item.likes' ),
    ],
    [
        'type' => 'default',
        'id' => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
];
?>

<x-data-tables id="item_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<div class="modal fade" id="item_likes_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="item_likes_modal_title">{{ __( 'item.likes' ) }}</h5>
                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <em class="icon ni ni-cross"></em>
                </a>
            </div>
            <div class="modal-body">
                <div class="listing-filter mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'datatables.created_date' ) ] ) }}" id="item_likes_date" style="background-color: #fff;">
                </div>
                <div class="card card-bordered card-preview">
                    <div class="card-inner">
                        <table class="table" style="width:100%" id="item_likes_table">
                            <thead><tr><th></th><th>No.</th><th>{{ __( 'wallet.user' ) }}</th><th>Email</th><th>Liked At</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

function escItemLikes( s ) {
    return s == null ? '' : String( s )
        .replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' ).replace( /"/g, '&quot;' );
}

window['columns'] = @json( $columns );
    
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#item_table',
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
            url: '{{ route( 'admin.item.allItems' ) }}',
            data: {
                'type': '{{ $type }}',
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'items',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'image_url' },
            { data: 'title' },
            { data: null },
            { data: 'author' },
            { data: 'status' },
            { data: 'like_count' },
            { data: 'encrypted_id' },
        ],
        columnDefs: [
            {
                // Add checkboxes to the first column
                targets: 0,
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "type" ) }}' ),
                visible: false,
                render: function( data, type, row, meta ) {
                    return '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "image" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? `<image src='${data}' width="75px">` : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "title" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ?? '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "author" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                render: function( data, type, row, meta ) {
                    return statusMapper[data];
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "likes" ) }}' ),
                className: 'text-center',
                render: function( data, type, row, meta ) {
                    return '<a href="#" class="dt-view-likes" data-id="' + row['encrypted_id'] + '" data-title="' + escItemLikes( row['title'] ) + '">' + ( data ?? 0 ) + ' <em class="icon ni ni-heart-fill" style="color:#e85347;"></em></a>';
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @canany( [ 'edit items', 'delete items' ] )
                    let edit, status = '', view = '', dt_delete = '';

                    @can( 'edit items' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete items' )
                    status = row['status'] == 10 ? 
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>' : 
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>';
                    
                    dt_delete = '<li class="dt-delete" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-trash"></em><span>{{ __( 'datatables.delete' ) }}</span></a></li>';
                    @endcan
                    
                    let html = 
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
                                    `+status+`
                                    `+dt_delete+`
                                </ul>
                            </div>
                        </div>
                        `;
                        return html;
                    @else
                    return '-';
                    @endcanany
                },
            },
        ],
    },
    table_no = 0,
    timeout = null;

    document.addEventListener( 'DOMContentLoaded', function() {

        $( '#type' ).val( '{{ $type }}' ).addClass( 'd-none' );
        window['type'] = '{{ $type }}';

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.item.edit' ) }}?id=' + $( this ).data( 'id' ) + '&type=' + '{{ $type }}' + '&parent_route=' + '{{ $parent_route }}';
        } );

        var itemLikesModal = new bootstrap.Modal( document.getElementById( 'item_likes_modal' ) );
        var itemLikesDT = null;
        var itemLikesId = null;
        var itemLikesLabel = '{{ __( 'item.likes' ) }}';
        var itemLikesDateRange = '';

        function itemLikesExportFileName( label ) {
            var d = new Date();
            function p( n ) { return ( n < 10 ? '0' : '' ) + n; }
            var stamp = String( d.getFullYear() ).slice( 2 ) + p( d.getMonth() + 1 ) + p( d.getDate() ) +
                '_' + p( d.getHours() ) + p( d.getMinutes() ) + p( d.getSeconds() );
            return ( label || 'Item_Likes' ).replace( /\s+/g, '_' ) + '_' + stamp;
        }

        function itemLikesButtons( label ) {
            var fileName = function() { return itemLikesExportFileName( label ); };

            function exportOpts() {
                var rowNum = 0;
                return {
                    modifier: { page: 'all' },
                    orthogonal: 'export',
                    rows: function( idx, data, node ) {
                        if ( $( '#item_likes_export_selected' ).is( ':checked' ) ) {
                            return $( node ).find( '.select-row' ).is( ':checked' );
                        }
                        return true;
                    },
                    columns: ':not(:first-child):not(:last-child)',
                    format: {
                        header: function( data, column ) {
                            if ( column === 1 ) rowNum = 0;
                            return data;
                        },
                        body: function( data, row, column ) {
                            return column === 1 ? ++rowNum : data;
                        },
                    },
                };
            }

            function visibleAction( cls ) {
                return function() { $( '.' + cls ).click(); };
            }

            function pdfCustomize( doc ) {
                var tableIndex = doc.content.findIndex( function( item ) { return item.table; } );
                if ( tableIndex > -1 ) {
                    var colCount = doc.content[ tableIndex ].table.body[0].length;
                    doc.content[ tableIndex ].table.widths = Array( colCount ).fill( '*' );
                }
            }

            return [
                { extend: 'copyHtml5',  className: 'd-none item-likes-copy',  exportOptions: exportOpts() },
                { text: '<i class="fa fa-copy"></i>',       className: 'btn btn-light',   titleAttr: 'Copy',           action: visibleAction( 'item-likes-copy' ) },
                { extend: 'excelHtml5', className: 'd-none item-likes-excel', title: fileName, exportOptions: exportOpts() },
                { text: '<i class="fa fa-file-excel"></i>', className: 'btn btn-success', titleAttr: 'Export to EXCEL', action: visibleAction( 'item-likes-excel' ) },
                { extend: 'csvHtml5',   className: 'd-none item-likes-csv',   title: fileName, exportOptions: exportOpts() },
                { text: '<i class="fa fa-file-csv"></i>',   className: 'btn btn-info',    titleAttr: 'Export to CSV',   action: visibleAction( 'item-likes-csv' ) },
                { extend: 'pdfHtml5',   className: 'd-none item-likes-pdf',   title: fileName, exportOptions: exportOpts(), orientation: 'landscape', customize: pdfCustomize },
                { text: '<i class="fa fa-file-pdf"></i>',   className: 'btn btn-danger',  titleAttr: 'Export to PDF',   action: visibleAction( 'item-likes-pdf' ) },
            ];
        }

        function buildItemLikesTable( likes, label ) {
            if ( $.fn.DataTable.isDataTable( '#item_likes_table' ) ) {
                $( '#item_likes_table' ).DataTable().destroy();
                $( '#item_likes_table tbody' ).empty();
            }

            itemLikesDT = $( '#item_likes_table' ).DataTable( {
                data: likes || [],
                columns: [
                    { data: null },
                    { data: null },
                    { data: 'user' },
                    { data: 'email' },
                    { data: 'liked_at' },
                ],
                columnDefs: [
                    {
                        targets: 0, orderable: false, searchable: false, className: 'text-center',
                        render: function() { return '<input type="checkbox" class="select-row">'; },
                    },
                    {
                        targets: 1, orderable: false, searchable: false,
                        render: function() { return ''; },
                    },
                    {
                        targets: 2,
                        render: function( data, type ) {
                            return type === 'display' ? escItemLikes( data ) : data;
                        },
                    },
                    {
                        targets: 3,
                        render: function( data, type ) {
                            return type === 'display' ? escItemLikes( data ) : data;
                        },
                    },
                ],
                order: [[ 4, 'desc' ]],
                pageLength: 10,
                lengthMenu: [ 5, 10, 25, 50, 100 ],
                searching: true,
                ordering: true,
                scrollX: true,
                dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 text-end'l>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'mt-2 col-sm-12 col-md-5'i><'mt-2 col-sm-12 col-md-7 text-end'p>>",
                buttons: itemLikesButtons( label ),
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
                },
                createdRow: function( row ) { $( row ).addClass( 'nk-tb-item' ); },
                initComplete: function() {
                    var api = this.api();
                    var $container = $( api.table().container() );
                    $container.find( '.export-check-wrapper' ).remove();
                    $container.find( '.dt-buttons' ).append(
                        '<div class="my-3 export-check-wrapper">' +
                        '<input type="checkbox" id="item_likes_export_selected">' +
                        '<label for="item_likes_export_selected" class="ms-1">Export ONLY selected rows</label>' +
                        '</div>'
                    );

                    var $selectAllTh = $( api.table().header() ).find( 'th' ).eq( 0 ).addClass( 'text-center' );
                    if ( !$selectAllTh.find( '.select-all-rows' ).length ) {
                        $selectAllTh.html( '<input type="checkbox" class="select-all-rows">' );
                    }
                    $selectAllTh.off( 'change.selectAll' ).on( 'change.selectAll', '.select-all-rows', function() {
                        $( api.table().body() ).find( '.select-row' ).prop( 'checked', $( this ).is( ':checked' ) );
                    } );
                },
                drawCallback: function() {
                    var api = this.api();
                    var info = api.page.info();
                    api.column( 1, { page: 'current' } ).nodes().each( function( cell, i ) {
                        cell.innerHTML = info.start + i + 1;
                    } );
                    $( api.table().header() ).find( '.select-all-rows' ).prop( 'checked', false );
                },
            } );
        }

        function loadItemLikes() {
            $.ajax( {
                url: '{{ route( 'admin.item.itemLikes' ) }}',
                type: 'POST',
                data: {
                    'id': itemLikesId,
                    'date_range': itemLikesDateRange,
                    '_token': '{{ csrf_token() }}',
                },
                success: function( response ) {
                    buildItemLikesTable( response.likes || [], itemLikesLabel );
                },
                error: function() {
                    buildItemLikesTable( [], itemLikesLabel );
                },
            } );
        }

        $( '#item_likes_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr ) {
                itemLikesDateRange = dateStr;
                loadItemLikes();
            },
        } );

        $( document ).on( 'click', '.dt-view-likes', function( e ) {
            e.preventDefault();

            var id = $( this ).data( 'id' ),
                title = $( this ).data( 'title' );

            itemLikesId = id;
            itemLikesLabel = title || '{{ __( 'item.likes' ) }}';
            itemLikesDateRange = '';

            var dateFp = $( '#item_likes_date' )[0]._flatpickr;
            if ( dateFp ) dateFp.clear();

            $( '#item_likes_modal_title' ).text( title ? title + ' — ' + '{{ __( 'item.likes' ) }}' : '{{ __( 'item.likes' ) }}' );
            itemLikesModal.show();

            loadItemLikes();
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.item.updateItemStatus' ) }}',
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

        $( document ).on( 'click', '.dt-delete', function() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.item.deleteItem' ) }}',
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