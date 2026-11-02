<?php
$subscription_voucher_url = 'subscription_voucher_url';
$voucher = $data['voucher'];
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'subscription_voucher.voucher_urls' ) }} - {{ $voucher->name }}</h3>
            <div class="nk-block-des text-soft">
                <p>{{ __( 'subscription_voucher.type_third_party' ) }}</p>
            </div>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

@can( 'add subscription_vouchers' )
<div class="card mb-4">
    <div class="card-inner">
        <h5 class="card-title mb-3">{{ __( 'subscription_voucher.add_urls' ) }}</h5>
        <div class="mb-3 row">
            <label for="{{ $subscription_voucher_url }}_urls" class="col-sm-3 col-form-label">{{ __( 'subscription_voucher.urls' ) }}</label>
            <div class="col-sm-9">
                <textarea class="form-control form-control-sm" id="{{ $subscription_voucher_url }}_urls" rows="6" placeholder="{{ __( 'subscription_voucher.urls_placeholder' ) }}&#10;https://example.com/voucher/AAA&#10;https://example.com/voucher/BBB"></textarea>
                <div class="invalid-feedback"></div>
            </div>
        </div>
        <div class="mb-3 row">
            <label for="{{ $subscription_voucher_url }}_max_users" class="col-sm-3 col-form-label">{{ __( 'subscription_voucher.max_users' ) }}</label>
            <div class="col-sm-9">
                <input type="number" min="1" step="1" class="form-control form-control-sm" id="{{ $subscription_voucher_url }}_max_users" value="1" style="max-width: 200px;">
                <div class="invalid-feedback"></div>
                <div class="form-note">{{ __( 'subscription_voucher.max_users_hint' ) }}</div>
            </div>
        </div>
        <div class="text-end">
            <button id="{{ $subscription_voucher_url }}_add" type="button" class="btn btn-primary">{{ __( 'subscription_voucher.add_urls' ) }}</button>
        </div>
    </div>
</div>
@endcan

@can( 'edit subscription_vouchers' )
<div class="d-flex flex-wrap align-items-center g-2 mb-2">
    <button type="button" class="btn btn-sm btn-outline-primary me-1" id="{{ $subscription_voucher_url }}_edit_selected">{{ __( 'subscription_voucher.edit_selected' ) }}</button>
    <button type="button" class="btn btn-sm btn-outline-success me-1 bulk-status" data-status="10">{{ __( 'subscription_voucher.activate_selected' ) }}</button>
    <button type="button" class="btn btn-sm btn-outline-warning me-1 bulk-status" data-status="20">{{ __( 'subscription_voucher.suspend_selected' ) }}</button>
    <button type="button" class="btn btn-sm btn-outline-danger" id="{{ $subscription_voucher_url }}_delete_selected">{{ __( 'subscription_voucher.delete_selected' ) }}</button>
</div>
@endcan

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
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'subscription_voucher.url' ) ] ),
        'id' => 'url',
        'title' => __( 'subscription_voucher.url' ),
    ],
    [
        'type' => 'default',
        'id' => 'max_users',
        'title' => __( 'subscription_voucher.max_users' ),
    ],
    [
        'type' => 'default',
        'id' => 'assigned_count',
        'title' => __( 'subscription_voucher.assigned' ),
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

<x-data-tables id="subscription_voucher_url_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<div class="modal fade" id="{{ $subscription_voucher_url }}_modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __( 'subscription_voucher.edit_selected' ) }}</h5>
                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close"><em class="icon ni ni-cross"></em></a>
            </div>
            <div class="modal-body">
                <div class="form-note mb-2">{{ __( 'subscription_voucher.max_users_hint' ) }}</div>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>{{ __( 'subscription_voucher.url' ) }}</th>
                            <th style="width: 160px;">{{ __( 'subscription_voucher.max_users' ) }}</th>
                        </tr>
                    </thead>
                    <tbody id="{{ $subscription_voucher_url }}_modal_rows"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="{{ $subscription_voucher_url }}_modal_submit">{{ __( 'template.save_changes' ) }}</button>
            </div>
        </div>
    </div>
</div>

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
    escapeHtml = function( text ) {
        return $( '<div>' ).text( text ?? '' ).html();
    },
    dt_table,
    dt_table_name = '#subscription_voucher_url_table',
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
            url: '{{ route( 'admin.subscription_voucher.allSubscriptionVoucherUrls' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
                'subscription_voucher': '{{ $voucher->encrypted_id }}',
            },
            dataSrc: 'subscription_voucher_urls',
        },
        lengthMenu: [[25, 50, 100],[25, 50, 100]],
        order: [[ 1, 'asc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'url' },
            { data: 'max_users' },
            { data: 'assigned_count' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "url" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    let url = escapeHtml( data );
                    return '<a href="' + url + '" target="_blank" rel="noopener" style="word-break: break-all;">' + url + '</a>';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "max_users" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return data == null ? '{{ __( 'subscription_voucher.unlimited' ) }}' : data;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "assigned_count" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return data;
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

                    @can( 'edit subscription_vouchers' )
                    // Url already sent to users can't be edited
                    let edit = row['assigned_count'] > 0 ? '' :
                        '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>',
                        status = row['status'] == 10 ?
                        '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>' :
                        '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>',
                        // Url already sent to users can't be deleted
                        remove = row['assigned_count'] > 0 ? '' :
                        '<li class="dt-delete" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-trash"></em><span>{{ __( 'template.delete' ) }}</span></a></li>';

                    return `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
                                    `+status+`
                                    `+remove+`
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

        let svu = '#{{ $subscription_voucher_url }}',
            editModal = new bootstrap.Modal( document.getElementById( '{{ $subscription_voucher_url }}_modal' ) );

        function showError( message ) {
            $( '#modal_danger .caption-text' ).html( message );
            modalDanger.toggle();
        }

        function showSuccess( message ) {
            $( '#modal_success .caption-text' ).html( message );
            modalSuccess.toggle();
        }

        function selectedIds() {
            return $( dt_table_name + ' .select-row:checked' ).map( function() {
                return $( this ).data( 'id' );
            } ).get();
        }

        function rowsByIds( ids ) {
            return dt_table.rows().data().toArray().filter( function( row ) {
                return ids.includes( row.encrypted_id );
            } );
        }

        // Share the table's .select-row checkboxes ( also used by export ), select all at column header
        $( dt_table_name + ' thead th' ).eq( parseInt( '{{ Helper::columnIndex( $columns, "select_row" ) }}' ) )
            .addClass( 'text-center' )
            .html( '<input type="checkbox" class="select-all-rows">' );

        // Bind on document, header may be cloned by DataTables outside the table
        function rowCheckboxes() {
            return $( dt_table.rows( { page: 'current' } ).nodes() ).find( '.select-row' );
        }

        $( document ).on( 'change', '.select-all-rows', function() {
            let checked = $( this ).is( ':checked' );
            rowCheckboxes().prop( 'checked', checked );
            $( '.select-all-rows' ).prop( 'checked', checked );
        } );

        $( document ).on( 'change', dt_table_name + ' .select-row', function() {
            let rows = rowCheckboxes();
            $( '.select-all-rows' ).prop( 'checked', rows.length > 0 && rows.length == rows.filter( ':checked' ).length );
        } );

        // Selection only on current page, reset after redraw
        $( dt_table_name ).on( 'draw.dt', function() {
            $( '.select-all-rows' ).prop( 'checked', false );
        } );

        // Multi add
        $( svu + '_add' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.subscription_voucher.createSubscriptionVoucherUrls' ) }}',
                type: 'POST',
                data: {
                    'subscription_voucher': '{{ $voucher->encrypted_id }}',
                    'urls': $( svu + '_urls' ).val(),
                    'max_users': $( svu + '_max_users' ).val(),
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( svu + '_urls' ).val( '' );
                    dt_table.draw( false );
                    showSuccess( response.message );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        $.each( error.responseJSON.errors, function( key, value ) {
                            $( svu + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        showError( error.responseJSON.message );
                    }
                }
            } );
        } );

        // Multi edit: open modal with selected rows
        function openEditModal( ids ) {

            let rows = rowsByIds( ids );
            if ( rows.length == 0 ) {
                showError( '{{ __( 'subscription_voucher.select_at_least_one' ) }}' );
                return;
            }

            // Skip assigned urls, they can't be edited
            rows = rows.filter( function( row ) {
                return row.assigned_count == 0;
            } );
            if ( rows.length == 0 ) {
                showError( '{{ __( 'subscription_voucher.url_assigned_cannot_edit' ) }}' );
                return;
            }

            let html = '';
            rows.forEach( function( row, index ) {
                html += '<tr data-id="' + row.encrypted_id + '">'
                    + '<td><input type="text" class="form-control form-control-sm modal-url" id="{{ $subscription_voucher_url }}_items_' + index + '_url" value="' + escapeHtml( row.url ) + '"><div class="invalid-feedback"></div></td>'
                    + '<td><input type="number" min="1" step="1" class="form-control form-control-sm modal-max-users" id="{{ $subscription_voucher_url }}_items_' + index + '_max_users" value="' + ( row.max_users ?? '' ) + '" placeholder="{{ __( 'subscription_voucher.unlimited' ) }}"><div class="invalid-feedback"></div></td>'                    + '</tr>';
            } );

            $( svu + '_modal_rows' ).html( html );
            editModal.show();
        }

        $( svu + '_edit_selected' ).click( function() {
            openEditModal( selectedIds() );
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            openEditModal( [ $( this ).data( 'id' ) ] );
        } );

        $( svu + '_modal_submit' ).click( function() {

            resetInputValidation();

            let items = [];
            $( svu + '_modal_rows tr' ).each( function() {
                items.push( {
                    'id': $( this ).data( 'id' ),
                    'url': $( this ).find( '.modal-url' ).val(),
                    'max_users': $( this ).find( '.modal-max-users' ).val(),
                } );
            } );

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.subscription_voucher.updateSubscriptionVoucherUrls' ) }}',
                type: 'POST',
                data: {
                    'items': items,
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    editModal.hide();
                    dt_table.draw( false );
                    showSuccess( response.message );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        // items.0.url => #subscription_voucher_url_items_0_url
                        $.each( error.responseJSON.errors, function( key, value ) {
                            $( svu + '_' + key.replace( /\./g, '_' ) ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        showError( error.responseJSON.message );
                    }
                }
            } );
        } );

        // Status, single or multi
        function updateStatus( ids, status ) {

            if ( ids.length == 0 ) {
                showError( '{{ __( 'subscription_voucher.select_at_least_one' ) }}' );
                return;
            }

            $.ajax( {
                url: '{{ route( 'admin.subscription_voucher.updateSubscriptionVoucherUrlStatus' ) }}',
                type: 'POST',
                data: {
                    'ids': ids,
                    'status': status,
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    dt_table.draw( false );
                    showSuccess( response.message );
                },
                error: function( error ) {
                    showError( error.responseJSON.message );
                },
            } );
        }

        $( '.bulk-status' ).click( function() {
            updateStatus( selectedIds(), $( this ).data( 'status' ) );
        } );

        $( document ).on( 'click', '.dt-status', function() {
            updateStatus( [ $( this ).data( 'id' ) ], $( this ).data( 'status' ) );
        } );

        // Delete, single or multi
        function deleteUrls( ids ) {

            if ( ids.length == 0 ) {
                showError( '{{ __( 'subscription_voucher.select_at_least_one' ) }}' );
                return;
            }

            if ( !confirm( '{{ __( 'subscription_voucher.delete_urls_confirm' ) }}' ) ) {
                return;
            }

            $.ajax( {
                url: '{{ route( 'admin.subscription_voucher.deleteSubscriptionVoucherUrls' ) }}',
                type: 'POST',
                data: {
                    'ids': ids,
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    dt_table.draw( false );
                    showSuccess( response.message );
                },
                error: function( error ) {
                    showError( error.responseJSON.message );
                },
            } );
        }

        $( svu + '_delete_selected' ).click( function() {
            deleteUrls( selectedIds() );
        } );

        $( document ).on( 'click', '.dt-delete', function() {
            deleteUrls( [ $( this ).data( 'id' ) ] );
        } );
    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
