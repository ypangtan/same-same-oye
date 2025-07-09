<?php $sales_record_create = 'sales_record_create'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.sales_records' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add users' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.sales_record.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
                        </li>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importSalesModal">
                            {{ __( 'template.import_x', [ 'title' => Str::singular( __( 'template.sales_records' ) ) ] ) }}
                        </button>
                    </ul>
                </div>
            </div>
        </div><!-- .nk-block-head-content -->
        @endcan
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<!-- Modal -->
<div class="modal fade" tabindex="-1" id="importSalesModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importSalesModalLabel">
                            {{ __( 'template.import_x', [ 'title' => Str::singular( __( 'template.sales_records' ) ) ] ) }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __( 'template.close' ) }}"></button>
                    </div>
                    <div class="modal-body">

                        <a href="{{ asset('admin/sample_excel/IFEI_sales_record_template.xlsx') }}" class="btn btn-primary mb-2" download>
                            {{ __( 'template.download_excel_template' ) }}
                        </a>

                        <div class="mb-3">
                            <label>{{ __( 'template.sales_records' ) }}</label>

                            <div class="dropzone mb-3" id="{{ $sales_record_create }}_image" style="min-height: 0px;">
                                <div class="dz-message needsclick">
                                    <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                                </div>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="cancel" type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                    </div>
            </div><!-- .modal-body -->
        </div>
    </div>
</div>

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
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'sales_record.customer_name' ) ] ),
        'id' => 'customer_name',
        'title' => __( 'sales_record.customer_name' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'sales_record.reference' ) ] ),
        'id' => 'reference',
        'title' => __( 'sales_record.reference' ),
    ],
    [
        'type' => 'default',
        'id' => 'total_price',
        'title' => __( 'sales_record.total_price' ),
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

<x-data-tables id="user_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );
    
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#user_table',
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
            url: '{{ route( 'admin.sales_record.allSalesRecords' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'sales_records',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'customer_name' },
            { data: 'reference' },
            { data: 'total_price' },
            { data: 'status' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "customer_name" ) }}' ),
                className: 'text-center',
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "reference" ) }}' ),
                className: 'text-center',
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "total_price" ) }}' ),
                className: 'text-center',
                
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
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @canany( [ 'edit users', 'delete users' ] )
                    let edit, status = '';

                    @can( 'edit users' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete users' )
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
                                    `+edit+`
                                    `+status+`
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

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.sales_record.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.sales_record.updateSalesRecordStatus' ) }}',
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
        let fc = '#{{ $sales_record_create }}', fileID = '';

        $(fc + '_cancel').click(() => window.location.href = '{{ route('admin.module_parent.banner.index') }}');

        // ✅ Prevent Dropzone from being attached multiple times
        if (Dropzone.instances.length > 0) {
            Dropzone.instances.forEach(dz => dz.destroy()); // Destroy existing Dropzones before initializing
        }

        // ✅ Ensure Dropzone is initialized once
        if (!$(fc + '_image').hasClass("dz-clickable")) {
            Dropzone.autoDiscover = false;
            let myDropzone = new Dropzone(fc + '_image', {
                url: "{{ route('admin.sales_record.importSalesRecords') }}",
                maxFiles: 1,
                acceptedFiles: ".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                addRemoveLinks: true,
                params: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(file, response) {
                    if (response.status == 200) {
                        myDropzone.removeFile(file);

                        const modalEl = document.getElementById('importSalesModal');
                        let importModal = bootstrap.Modal.getInstance(modalEl);

                        if (importModal) {
                            importModal.toggle();
                        }

                        if (typeof dt_table !== 'undefined') {
                            dt_table.draw(false);
                        }
                    }
                },
                error: function( file, response ) {
                    console.log('Dropzone error response:', response);

                    $('body').loading('stop');

                    let message = 'An unexpected error occurred.';

                    try {
                        if (typeof response === 'string') {
                            // Laravel might return an HTML string (500 error page)
                            message = response;
                        } else if (typeof response === 'object') {
                            if (response.errors && Array.isArray(response.errors)) {
                                message = response.errors.join('<br>');
                            } else if (response.message) {
                                message = response.message;
                            }
                        }
                    } catch (e) {
                        console.warn('Failed to parse Dropzone error response:', e);
                    }

                    $('#modal_danger .caption-text').html(message);
                    modalDanger.toggle();
                }

            });
        }

    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>