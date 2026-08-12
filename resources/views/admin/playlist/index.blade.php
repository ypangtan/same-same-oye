<style>
    td:has(span.highlight) { background-color: #fff56d; }
</style>

<?php
$type = $data['type'] ?? null;
$parent_route = $data['parent_route'] ?? null;
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.playlists' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add playlists' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.module_parent.playlist.add' ) . '?type=' . $type . '&parent_route=' . $parent_route }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- .nk-block-head-content -->
        @endcan
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<?php
$enableReorder = \Helper::needReorder( 'playlists' );

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
        'type' => 'date',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'template.publishing_date' ) ] ),
        'id' => 'publishing_date',
        'title' => __( 'template.publishing_date' ),
    ],
    [
        'type' => 'default',
        'id' => 'image',
        'title' => __( 'playlist.image' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'playlist.title' ) ] ),
        'id' => 'title',
        'title' => __( 'playlist.title' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'category.type' ) ] ),
        'id' => 'type',
        'title' => __( 'category.type' ),
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
        'title' => __( 'playlist.likes' ),
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

<x-data-tables id="playlist_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<div class="modal fade" id="playlist_likes_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="playlist_likes_modal_title">{{ __( 'playlist.likes' ) }}</h5>
                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <em class="icon ni ni-cross"></em>
                </a>
            </div>
            <div class="modal-body">
                <div class="mb-2" style="max-width: 320px;">
                    <input type="text" class="form-control form-control-sm" placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'item.title' ) ] ) }}" id="playlist_likes_search">
                </div>
                <div class="card card-bordered card-preview">
                    <div class="card-inner">
                        <table class="table" style="width:100%" id="playlist_likes_table">
                            <thead><tr><th></th><th>No.</th><th>{{ __( 'item.title' ) }}</th><th>{{ __( 'playlist.likes' ) }}</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="playlist_item_users_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <a href="#" class="btn btn-icon btn-sm me-1" id="playlist_item_users_modal_back" title="Back">
                    <em class="icon ni ni-arrow-left"></em>
                </a>
                <h5 class="modal-title" id="playlist_item_users_modal_title">{{ __( 'playlist.likes' ) }}</h5>
                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <em class="icon ni ni-cross"></em>
                </a>
            </div>
            <div class="modal-body">
                <div class="mb-2" style="max-width: 320px;">
                    <input type="text" class="form-control form-control-sm" placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'datatables.created_date' ) ] ) }}" id="playlist_item_users_date" style="background-color: #fff;">
                </div>
                <div class="card card-bordered card-preview">
                    <div class="card-inner">
                        <table class="table" style="width:100%" id="playlist_item_users_table">
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

function escPlaylistLikes( s ) {
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
    dt_table_name = '#playlist_table',
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
            url: '{{ route( 'admin.playlist.allPlaylists' ) }}',
            data: {
                'type': '{{ $type }}',
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'playlists',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'publishing_date' },
            { data: 'image_url' },
            { data: 'name' },
            { data: null },
            { data: 'status' },
            { data: 'total_likes' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "publishing_date" ) }}' ),
                width: '10%',
                orderable: false,
                render: function( data, type, row, meta ) {
                    if ( !data ) return '-';
                    var klNow = new Date( new Date().toLocaleString( 'en-US', { timeZone: 'Asia/Kuala_Lumpur' } ) );
                    var today = new Date( klNow.getFullYear(), klNow.getMonth(), klNow.getDate() );
                    var p = data.split( '-' ); var publishDate = new Date( p[0], p[1] - 1, p[2] );
                    if ( publishDate > today ) {
                        return '<span class="highlight">' + data + '</span>';
                    }
                    return data;
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                render: function( data, type, row, meta ) {
                    return statusMapper[data];
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "likes" ) }}' ),
                className: 'text-center',
                render: function( data, type, row, meta ) {
                    return '<a href="#" class="dt-view-likes" data-id="' + row['encrypted_id'] + '" data-title="' + escPlaylistLikes( row['name'] ) + '">' + ( data ?? 0 ) + ' <em class="icon ni ni-heart-fill" style="color:#e85347;"></em></a>';
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @canany( [ 'edit playlists', 'delete playlists' ] )
                    let edit, status = '', view = '', dt_delete = '';

                    @can( 'edit playlists' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete playlists' )
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

    if ( parseInt( '{{ $enableReorder }}' ) == 1 ) {

        dt_table_config.rowReorder = {
            selector: '.dt-reorder',
            dataSrc: 'id',
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
                return `<div class="dt-reorder"style="width: 20px" data-id="${data}" />
                    <i class="align-middle feather" icon-name="move" style="color: #5f5f5f;"></i>
                </div>`;
            },
        } );
    }

    document.addEventListener( 'DOMContentLoaded', function() {

        $( '#type' ).val( '{{ $type }}' ).addClass( 'd-none' );
        window['type'] = '{{ $type }}';

        $( '#created_date, #publishing_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.playlist.edit' ) }}?id=' + $( this ).data( 'id' ) + '&type=' + '{{ $type }}' + '&parent_route=' + '{{ $parent_route }}';
        } );

        var playlistLikesModal = new bootstrap.Modal( document.getElementById( 'playlist_likes_modal' ) );
        var playlistItemUsersModal = new bootstrap.Modal( document.getElementById( 'playlist_item_users_modal' ) );

        var PL_DT_DOM = "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 text-end'l>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'mt-2 col-sm-12 col-md-5'i><'mt-2 col-sm-12 col-md-7 text-end'p>>";

        var PL_DT_LANG = {
            'lengthMenu': '{{ __( "datatables.lengthMenu" ) }}',
            'zeroRecords': '{{ __( "datatables.zeroRecords" ) }}',
            'info': '{{ __( "datatables.info" ) }}',
            'infoEmpty': '{{ __( "datatables.infoEmpty" ) }}',
            'infoFiltered': '{{ __( "datatables.infoFiltered" ) }}',
            'paginate': {
                'previous': '{{ __( "datatables.previous" ) }}',
                'next': '{{ __( "datatables.next" ) }}',
            },
        };

        function plExportFileName( label ) {
            var d = new Date();
            function p( n ) { return ( n < 10 ? '0' : '' ) + n; }
            var stamp = String( d.getFullYear() ).slice( 2 ) + p( d.getMonth() + 1 ) + p( d.getDate() ) +
                '_' + p( d.getHours() ) + p( d.getMinutes() ) + p( d.getSeconds() );
            return ( label || 'Playlist' ).replace( /\s+/g, '_' ) + '_' + stamp;
        }

        // Shared export-button + select-all/only-selected wiring for both modal tables below,
        // scoped per table via `key` so their button/checkbox ids never collide.
        function plMakeButtons( key, label ) {
            var chkId = 'pl-export-selected-' + key,
                copyCls = 'pl-copy-' + key, excelCls = 'pl-excel-' + key, csvCls = 'pl-csv-' + key, pdfCls = 'pl-pdf-' + key,
                fileName = function() { return plExportFileName( label ); };

            function exportOpts() {
                var rowNum = 0;
                return {
                    modifier: { page: 'all' },
                    orthogonal: 'export',
                    rows: function( idx, data, node ) {
                        if ( $( '#' + chkId ).is( ':checked' ) ) {
                            return $( node ).find( '.select-row' ).is( ':checked' );
                        }
                        return true;
                    },
                    columns: ':not(:first-child)',
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

            return {
                chkId: chkId,
                buttons: [
                    { extend: 'copyHtml5',  className: 'd-none ' + copyCls,  exportOptions: exportOpts() },
                    { text: '<i class="fa fa-copy"></i>',       className: 'btn btn-light',   titleAttr: 'Copy',           action: visibleAction( copyCls ) },
                    { extend: 'excelHtml5', className: 'd-none ' + excelCls, title: fileName, exportOptions: exportOpts() },
                    { text: '<i class="fa fa-file-excel"></i>', className: 'btn btn-success', titleAttr: 'Export to EXCEL', action: visibleAction( excelCls ) },
                    { extend: 'csvHtml5',   className: 'd-none ' + csvCls,   title: fileName, exportOptions: exportOpts() },
                    { text: '<i class="fa fa-file-csv"></i>',   className: 'btn btn-info',    titleAttr: 'Export to CSV',   action: visibleAction( csvCls ) },
                    { extend: 'pdfHtml5',   className: 'd-none ' + pdfCls,   title: fileName, exportOptions: exportOpts(), orientation: 'landscape', customize: pdfCustomize },
                    { text: '<i class="fa fa-file-pdf"></i>',   className: 'btn btn-danger',  titleAttr: 'Export to PDF',   action: visibleAction( pdfCls ) },
                ],
            };
        }

        function plInitComplete( chkId ) {
            return function() {
                var api = this.api();
                var $container = $( api.table().container() );
                $container.find( '.export-check-wrapper' ).remove();
                $container.find( '.dt-buttons' ).append(
                    '<div class="my-3 export-check-wrapper">' +
                    '<input type="checkbox" id="' + chkId + '">' +
                    '<label for="' + chkId + '" class="ms-1">Export ONLY selected rows</label>' +
                    '</div>'
                );

                var $selectAllTh = $( api.table().header() ).find( 'th' ).eq( 0 ).addClass( 'text-center' );
                if ( !$selectAllTh.find( '.select-all-rows' ).length ) {
                    $selectAllTh.html( '<input type="checkbox" class="select-all-rows">' );
                }
                $selectAllTh.off( 'change.selectAll' ).on( 'change.selectAll', '.select-all-rows', function() {
                    $( api.table().body() ).find( '.select-row' ).prop( 'checked', $( this ).is( ':checked' ) );
                } );
            };
        }

        function plDrawCallback() {
            var api = this.api();
            var info = api.page.info();
            api.column( 1, { page: 'current' } ).nodes().each( function( cell, i ) {
                cell.innerHTML = info.start + i + 1;
            } );
            $( api.table().header() ).find( '.select-all-rows' ).prop( 'checked', false );
        }

        var playlistLikesDT = null;

        function buildPlaylistLikesTable( items, label ) {
            if ( $.fn.DataTable.isDataTable( '#playlist_likes_table' ) ) {
                $( '#playlist_likes_table' ).DataTable().destroy();
                $( '#playlist_likes_table tbody' ).empty();
            }

            var b = plMakeButtons( 'plikes', label );

            playlistLikesDT = $( '#playlist_likes_table' ).DataTable( {
                data: items || [],
                columns: [
                    { data: null },
                    { data: null },
                    { data: 'title' },
                    { data: 'like_count' },
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
                            return type === 'display' ? escPlaylistLikes( data ) : data;
                        },
                    },
                    {
                        targets: 3,
                        className: 'text-center',
                        render: function( data, type, row ) {
                            if ( type !== 'display' ) return data ?? 0;
                            return '<a href="#" class="dt-view-item-likes" data-id="' + row.id + '" data-title="' + escPlaylistLikes( row.title ) + '">' + ( data ?? 0 ) + ' <em class="icon ni ni-heart-fill" style="color:#e85347;"></em></a>';
                        },
                    },
                ],
                order: [[ 3, 'desc' ]],
                pageLength: 10,
                lengthMenu: [ 5, 10, 25, 50, 100 ],
                searching: true,
                ordering: true,
                scrollX: true,
                dom: PL_DT_DOM,
                buttons: b.buttons,
                language: PL_DT_LANG,
                createdRow: function( row ) { $( row ).addClass( 'nk-tb-item' ); },
                initComplete: plInitComplete( b.chkId ),
                drawCallback: plDrawCallback,
            } );
        }

        $( '#playlist_likes_search' ).on( 'keyup', function() {
            if ( playlistLikesDT ) playlistLikesDT.column( 2 ).search( this.value ).draw();
        } );

        var playlistItemUsersDT = null,
            playlistItemUsersId = null,
            playlistItemUsersLabel = '{{ __( 'playlist.likes' ) }}',
            playlistItemUsersDateRange = '';

        function buildPlaylistItemUsersTable( likes, label ) {
            if ( $.fn.DataTable.isDataTable( '#playlist_item_users_table' ) ) {
                $( '#playlist_item_users_table' ).DataTable().destroy();
                $( '#playlist_item_users_table tbody' ).empty();
            }

            var b = plMakeButtons( 'plusers', label );

            playlistItemUsersDT = $( '#playlist_item_users_table' ).DataTable( {
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
                            return type === 'display' ? escPlaylistLikes( data ) : data;
                        },
                    },
                    {
                        targets: 3,
                        render: function( data, type ) {
                            return type === 'display' ? escPlaylistLikes( data ) : data;
                        },
                    },
                ],
                order: [[ 4, 'desc' ]],
                pageLength: 10,
                lengthMenu: [ 5, 10, 25, 50, 100 ],
                searching: true,
                ordering: true,
                scrollX: true,
                dom: PL_DT_DOM,
                buttons: b.buttons,
                language: PL_DT_LANG,
                createdRow: function( row ) { $( row ).addClass( 'nk-tb-item' ); },
                initComplete: plInitComplete( b.chkId ),
                drawCallback: plDrawCallback,
            } );
        }

        function loadPlaylistItemUsers() {
            $.ajax( {
                url: '{{ route( 'admin.item.itemLikes' ) }}',
                type: 'POST',
                data: {
                    'id': playlistItemUsersId,
                    'date_range': playlistItemUsersDateRange,
                    '_token': '{{ csrf_token() }}',
                },
                success: function( response ) {
                    buildPlaylistItemUsersTable( response.likes || [], playlistItemUsersLabel );
                },
                error: function() {
                    buildPlaylistItemUsersTable( [], playlistItemUsersLabel );
                },
            } );
        }

        $( '#playlist_item_users_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr ) {
                playlistItemUsersDateRange = dateStr;
                loadPlaylistItemUsers();
            },
        } );

        // Level 1: playlist's like count -> list of its items with each item's total likes.
        $( document ).on( 'click', '.dt-view-likes', function( e ) {
            e.preventDefault();

            var id = $( this ).data( 'id' ),
                title = $( this ).data( 'title' );

            $( '#playlist_likes_modal_title' ).text( title ? title + ' — ' + '{{ __( 'playlist.likes' ) }}' : '{{ __( 'playlist.likes' ) }}' );
            $( '#playlist_likes_search' ).val( '' );
            playlistLikesModal.show();

            $.ajax( {
                url: '{{ route( 'admin.playlist.playlistLikes' ) }}',
                type: 'POST',
                data: {
                    'id': id,
                    '_token': '{{ csrf_token() }}',
                },
                success: function( response ) {
                    buildPlaylistLikesTable( response.items || [], title || '{{ __( 'playlist.likes' ) }}' );
                },
                error: function() {
                    buildPlaylistLikesTable( [], title || '{{ __( 'playlist.likes' ) }}' );
                },
            } );
        } );

        // Level 2: an item's like count (inside the level 1 modal) -> list of users who liked that item.
        $( document ).on( 'click', '.dt-view-item-likes', function( e ) {
            e.preventDefault();

            var id = $( this ).data( 'id' ),
                title = $( this ).data( 'title' );

            playlistItemUsersId = id;
            playlistItemUsersLabel = title || '{{ __( 'playlist.likes' ) }}';
            playlistItemUsersDateRange = '';

            var dateFp = $( '#playlist_item_users_date' )[0]._flatpickr;
            if ( dateFp ) dateFp.clear();

            $( '#playlist_item_users_modal_title' ).text( title ? title + ' — ' + '{{ __( 'playlist.likes' ) }}' : '{{ __( 'playlist.likes' ) }}' );

            playlistLikesModal.hide();
            playlistItemUsersModal.show();

            loadPlaylistItemUsers();
        } );

        $( '#playlist_item_users_modal_back' ).on( 'click', function( e ) {
            e.preventDefault();
            playlistItemUsersModal.hide();
        } );

        // Closing the user-list modal (via the back arrow, the X, or the backdrop) returns to the item list.
        document.getElementById( 'playlist_item_users_modal' ).addEventListener( 'hidden.bs.modal', function() {
            playlistLikesModal.show();
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.playlist.updatePlaylistStatus' ) }}',
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
                url: '{{ route( 'admin.playlist.deletePlaylist' ) }}',
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

        $( 'category' ).select2({

            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: true,

            ajax: { 
                url: '{{ route( 'admin.category.allCategories' ) }}',
                type: "post",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        item: params.term, // search term
                        designation: 1,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.categories.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.name,
                        } );
                    } );

                    return {
                        results: processedResult,
                        pagination: {
                            more: ( params.page * 10 ) < data.recordsFiltered
                        }
                    };

                },
                cache: true
            },
        });
    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>