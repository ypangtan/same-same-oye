<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.marketing_notifications' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add marketing_notifications' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.marketing_notifications.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'announcement.user' ) ] ),
        'id' => 'user',
        'title' => __( 'announcement.user' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'datatables.title' ) ] ),
        'id' => 'title',
        'title' => __( 'datatables.title' ),
    ],
    // [
    //     'type' => 'select',
    //     'options' => $data['type'],
    //     'id' => 'type',
    //     'title' => __( 'datatables.type' ),
    // ],
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

<x-data-tables id="announcement_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>
    window['columns'] = @json( $columns );
    window['ids'] = [];
    
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
            '11': {
                'text': '{{ __( 'datatables.completed' ) }}',
                'color': 'badge rounded-pill bg-success',
            },
            '20': {
                'text': '{{ __( 'datatables.suspended' ) }}',
                'color': 'badge rounded-pill bg-danger',
            },
        },dt_table,
        dt_table_name = '#announcement_table',
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
                url: '{{ route( 'admin.marketing_notifications.allMarketingNotifications' ) }}',
                data: {
                    '_token': '{{ csrf_token() }}',
                },
                dataSrc: 'notifications',
            },
            lengthMenu: [[10, 25],[10, 25]],
            order: [[ 1, 'desc' ]],
            columns: [
                { data: null },
                { data: null },
                { data: 'created_at' },
                { data: 'is_broadcast' },
                { data: 'title' },
                // { data: 'type' },
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
                    orderable: false,
                    render: function( data, type, row, meta ) {
                        if ( data == 20 || data == 0 ) {
                            const users = row.user_notification_users || [];
                            if (users.length > 1) {
                                return 'Specific User';
                            } else if (users.length === 1 && users[0].user) {
                                return ( users[0].user.calling_code ?? '+60' ) + users[0].user.phone_number + ' (' + ( users[0].user.email ? users[0].user.email : '-' ) + ')';
                            } else {
                                return 'Unknown User';
                            }
                        } else {
                            return 'All Users';
                        }
                    }
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "title" ) }}' ),
                    orderable: false,
                    render: function( data, type, row, meta ) {
                        return data ? data : '-';
                    },
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "type" ) }}' ),
                    render: function( data, type, row, meta ) {
                        return data == 2 ? '{{ __( 'announcement.news' ) }}' : '{{ __( 'announcement.event' ) }}';
                    },
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
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

                        @canany( [ 'edit marketing_notifications', 'delete marketing_notifications' ] )
                        let edit, status = '';

                        @can( 'edit marketing_notifications' )
                        edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                        @endcan

                        @can( 'delete marketing_notifications' )
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


        $( document ).on( 'click', '#add', function() {

            window.open( '{{ route( 'admin.marketing_notifications.add' ) }}', '_blank' );
        } );
        
        $( document ).on( 'click', '.dt-edit', function() {

            let id = $( this ).data( 'id' );

            window.open( '{{ route( 'admin.marketing_notifications.edit' ) }}/' + id, '_blank' );
        } );

         $( document ).on( 'click', '.dt-status', function() {

            let data = {
                id: $( this ).data( 'id' ),
                status: $( this ).data( 'status' ),
                _token: '{{ csrf_token() }}',
            }

            $.ajax( {
                url: '{{ route( 'admin.marketing_notifications.updateMarketingNotificationStatus' ) }}',
                type: 'POST',
                data: data,
                success: function( response ) {
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                    dt_table.draw( false );
                },
            } );
        } );

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                if ( $( instance.element ).val() ) {
                    window[$( instance.element ).data('id')] = $( instance.element ).val();
                    dt_table.draw( false );
                }
            }
        } );
    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>