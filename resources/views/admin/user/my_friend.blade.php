<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.my_friends' ) }}</h3>
        </div><!-- .nk-block-head-content -->
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
        'type' => 'default',
        'id' => 'referral_id',
        'title' => '',
    ],
    [
        'type' => 'date',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'datatables.created_date' ) ] ),
        'id' => 'created_date',
        'title' => __( 'datatables.created_date' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'user.first_name' ) ] ),
        'id' => 'first_name',
        'title' => __( 'user.first_name' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'user.last_name' ) ] ),
        'id' => 'last_name',
        'title' => __( 'user.last_name' ),
    ],
    // [
    //     'type' => 'input',
    //     'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'user.username' ) ] ),
    //     'id' => 'username',
    //     'title' => __( 'user.username' ),
    // ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'user.email' ) ] ),
        'id' => 'email',
        'title' => __( 'user.email' ),
    ],
    [
        'type' => 'select',
        'options' => $data['user_social'],
        'id' => 'user_social',
        'title' => __( 'user.user_social' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'user.phone_number' ) ] ),
        'id' => 'phone_number',
        'title' => __( 'user.phone_number' ),
    ],
    [
        'type' => 'select',
        'options' => $data['rank'],
        'id' => 'rank',
        'title' => __( 'user.rank' ),
    ],
    [
        'type' => 'default',
        'id' => 'tier_progress',
        'title' => __( 'user.tier_progress' ),
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
            url: '{{ route( 'admin.user.oneUserDownlines' ) }}',
            data: {
                'referral_id': '{{ Request( 'id' ) }}',
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'users',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'first_name' },
            { data: 'last_name' },
            // { data: 'username' },
            { data: 'email' },
            { data: 'social_logins' },
            { data: 'phone_number' },
            { data: 'current_rank' },
            { data: 'total_accumulate_spending' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "referral_id" ) }}' ),
                visiable: false,
                render: function( data, type, row, meta ) {
                    return '';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "email" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "user_social" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data.length > 0 ? data[0].platform_label : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "user" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "first_name" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "last_name" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            // {
            //     targets: parseInt( '{{ Helper::columnIndex( $columns, "fullname" ) }}' ),
                
            //     render: function( data, type, row, meta ) {
            //         return data ? data : '-' ;
            //     },
            // },
            // {
            //     targets: parseInt( '{{ Helper::columnIndex( $columns, "username" ) }}' ),
                
            //     render: function( data, type, row, meta ) {
            //         return data ? data : '-' ;
            //     },
            // },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "feedback_email" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "phone_number" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? ( row.calling_code ? row.calling_code + " " : "+60 " ) + data : '-' ;
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
            window.location.href = '{{ route( 'admin.user.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.user.updateUserStatus' ) }}',
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