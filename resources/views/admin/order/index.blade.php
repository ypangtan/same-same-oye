<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.orders' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add orders' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.order.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
                        </li>
                        <li class="nk-block-tools-opt">
                            <a href="#" class="btn btn-primary dt-generate-order">{{ __( 'template.generate_test_order' ) }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- .nk-block-head-content -->
        @endcan
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<?php
$order_view = 'order_view';

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
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'order.reference' ) ] ),
        'id' => 'reference',
        'title' => __( 'order.reference' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'order.user' ) ] ),
        'id' => 'user',
        'title' => __( 'order.user' ),
    ],
    [
        'type' => 'default',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'order.total' ) ] ),
        'id' => 'total_price',
        'title' => __( 'order.total' ) . ' (' . __( 'order.rm' ) . ')',
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

<x-data-tables id="order_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<div class="modal fade" id="modal_order_view" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{ __( 'order.order_details' ) }}</h5>
        </div>
        <div class="modal-body">
            <div class="mb-3 row d-flex justify-content-between">
                <label class="col-sm-5 col-form-label">Vending Machine</label>
                <div class="col-sm-7">
                    <input type="text" class="form-control-plaintext" id="{{ $order_view }}_fullname" readonly>
                </div>
            </div>
            <div class="mb-3 row d-flex justify-content-between">
                <label class="col-sm-5 col-form-label">Reference</label>
                <div class="col-sm-7">
                    <input type="text" class="form-control-plaintext" id="{{ $order_view }}_email" readonly>
                </div>
            </div>
            
            <div class="mb-3 row d-flex justify-content-between bundle">
                <label class="col-sm-5 col-form-label"> {{ __( 'order.bundle' ) }}</label>
                <div class="col-sm-7">
                    <input type="text" class="form-control-plaintext" id="{{ $order_view }}_bundle" readonly>
                </div>
            </div>
            
            <div class="mb-3 row d-flex justify-content-between bundle">
                <label class="col-sm-5 col-form-label"> {{ __( 'order.cups_left' ) }}</label>
                <div class="col-sm-7">
                    <input type="text" class="form-control-plaintext" id="{{ $order_view }}cups_left" readonly>
                </div>
            </div>

            <div class="mb-3 row d-flex justify-content-between">
                <label class="col-sm-5 col-form-label">Payment Method</label>
                <div class="col-sm-7">
                    <input type="text" class="form-control-plaintext" id="{{ $order_view }}_type" readonly>
                </div>
            </div>
            
            <div class="mb-3 row">
                <label for="{{ $order_view }}_status" class="col-sm-5 col-form-label">{{ __( 'order.status' ) }}</label>
                <div class="col-sm-7">
                    <select class="form-select form-select-sm" id="{{ $order_view }}_status">
                        @foreach ( $data[ 'status' ] as $key => $status)
                            <option value="{{ $key }}">{{ $status }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="mb-3 row d-flex justify-content-between">
                <label class="col-sm-5 col-form-label">QR Code</label>
                <div class="col-sm-7">
                    <div id="downloadQR" class="form-control-plaintext image-container" 
                         style="background-image: url('your-image.jpg'); height: 250px; cursor: pointer; background-size:cover;">
                    </div>
                </div>
            </div>
            <input type="hidden" id="{{ $order_view }}_id">

            <div class="mb-3">
                <strong>Order Details</strong>
                <div class="selections"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                <button type="button" class="btn btn-sm btn-primary">{{ __( 'template.save_changes' ) }}</button>
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

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#order_table',
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
            url: '{{ route( 'admin.order.allOrders' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'orders',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'reference' },
            { data: 'user' },
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
                width: '1%',
                render: function (data, type, row, meta) {
                    // Calculate the row number dynamically based on the page info
                    const pageInfo = dt_table.page.info();
                    return pageInfo.start + meta.row + 1; // Adjust for 1-based numbering
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "user" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data.username ?? '-' + '<br>' + '+60' + data.phone_number;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "total_price" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data;
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
                width: '1%',
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @canany( [ 'edit orders', 'delete orders' ] )
                    let edit = '', 
                    status = '';
 
                    @can( 'edit orders' )
                    edit += '<li class="dt-view" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.view' ) }}</span></a></li>';
                    edit += '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete orders' )
                    status = row['status'] == 10 ? 
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.order_canceled' ) }}</span></a></li>' : 
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.order_placed' ) }}</span></a></li>';
                    @endcan
                    
                    let html = 
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
                                </ul>
                            </div>
                        </div>
                        `;
                        console.log(html)
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

        let ov = '#{{ $order_view }}',
            modalmt5Detail = new bootstrap.Modal( document.getElementById( 'modal_order_view' ) );

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.order.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.order.updateOrderStatus' ) }}',
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

        $(document).on('click', '.dt-generate-order', function () {
            window.location.href = '{{ route('admin.order.generateTestOrder') }}';
            
            dt_table.draw( true );
            $( '#modal_success .caption-text' ).html( 'Test Orders Generated' );
            modalSuccess.toggle();
        });

        $( document ).on( 'click', '.dt-view', function() {

            $( '#modal_order_view .form-control-plaintext' ).val( '-' );
            $( '#modal_order_view .form-select' ).val( 2 );
            $( '#modal_order_view textarea' ).val();
            $( '#modal_order_view textarea' ).val();

            let id = $( this ).data( 'id' );

            $.ajax( {
                url: '{{ route( 'admin.order.oneOrder' ) }}',
                type: 'POST',
                data: {
                    id,
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    $('#{{ $order_view }}_id').val(response.id);
                    $('#{{ $order_view }}_fullname').val(response.vending_machine.title || '-');
                    $('#{{ $order_view }}_status').val(response.status || '-');
                    $('#{{ $order_view }}_email').val(response.reference || '-');
                    $('#{{ $order_view }}_type').val(response.payment_method === 1 ? 'Yobe Wallet' : 'Online Payment');
                    $('#{{ $order_view }}_account_type').val(response.status === 1 ? 'Order Placed' : 'Collected');
                    if (response.qr_code) {
                        $('#downloadQR').css('background-image', `url(${response.qr_code})`);
                    }
                    $('#modal_order_view .selections').empty();

                    const orderMetas = response.orderMetas || [];
                    orderMetas.forEach((meta) => {
                        const froyoHtml = meta.froyo
                            ? meta.froyo
                                .map(
                                    (froyo) =>
                                        `<div><strong>Froyo:</strong> ${froyo.title}</div>`
                                )
                                .join('')
                            : '<div><strong>Froyo:</strong> None selected</div>';

                        const syrupHtml = meta.syrup
                            ? meta.syrup
                                .map(
                                    (syrup) =>
                                        `<div><strong>Syrup:</strong> ${syrup.title}</div>`
                                )
                                .join('')
                            : '<div><strong>Syrup:</strong> None selected</div>';

                        const toppingHtml = meta.topping
                            ? meta.topping
                                .map(
                                    (topping) =>
                                        `<div><strong>Topping:</strong> ${topping.title}</div>`
                                )
                                .join('')
                            : '<div><strong>Topping:</strong> None selected</div>';

                        // Append to the modal
                        $('#modal_order_view .selections').append(
                            `<div>
                                <h6>Product: ${meta.product.title} (${meta.product.code})</h6>
                                <h6>Price: ${meta.product.title} (${meta.product.code})</h6>
                                ${froyoHtml}
                                ${syrupHtml}
                                ${toppingHtml}
                            </div><hr>`
                        );
                    });


                    modalmt5Detail.show();
                },
                error: function( error ) {
                    modalmt5Detail.hide();
                    $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                    modalDanger.show();
                },
            } );
        } );

        $( '#downloadQR' ).on( 'click', function() {
            if (!this.style.backgroundImage) return;
        
            let imageUrl = this.style.backgroundImage.replace(/url\(["']?/, '').replace(/["']?\)/, '');
            let link = document.createElement('a');
            link.href = imageUrl;
            link.download = 'qr_code.jpg';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        $( '#modal_order_view .btn-primary' ).on( 'click', function() {

            let formData = new FormData();
            formData.append( 'id', $( ov + '_id' ).val() );
            formData.append( 'status', $( ov + '_status' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.order.updateOrderStatusView' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    modalmt5Detail.hide();
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.show();
                    dt_table.draw( true );
                },
                error: function( error ) {
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $( ov + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        });

                    } else {
                        modalmt5Detail.hide();
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.show();    
                    }
                },
            } );
        } );

    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>