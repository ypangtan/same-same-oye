<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.user_subscriptions' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add user_subscriptions' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.user_subscription.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
        'id' => 'dt_no',
        'title' => 'No.',
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'user.user' ) ] ),
        'id' => 'user',
        'title' => __( 'user_subscription.user' ),
    ],
    [
        'type' => 'default',
        'id' => 'plan',
        'title' => __( 'user_subscription.plan' ),
    ],
    [
        'type' => 'default',
        'id' => 'end_date',
        'title' => __( 'user_subscription.end_date' ),
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

<x-data-tables id="user_subscription_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

    window['columns'] = @json( $columns );
    window['ids'] = [];
    
    @foreach ( $columns as $column )
    @if ( $column['type'] != 'default' )
    window['{{ $column['id'] }}'] = '';
    @endif
    @endforeach
    
    var statusMapper = @json( $data['status'] ),
        dt_table,
        dt_table_name = '#user_subscription_table',
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
                url: '{{ route( 'admin.user_subscription.allUserSubscriptions' ) }}',
                data: {
                    '_token': '{{ csrf_token() }}',
                },
                dataSrc: 'user_subscriptions',
            },
            lengthMenu: [
                [ 10, 25, 50, 999999 ],
                [ 10, 25, 50, '{{ __( 'datatables.all' ) }}' ]
            ],
            order: [[ 1, 'desc' ]],
            columns: [
                { data: null },
                { data: 'user' },
                { data: 'type' },
                { data: 'end_date' },
                { data: 'status' },
                { data: 'encrypted_id' },
            ],
            columnDefs: [
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                    orderable: false,
                    render: function( data, type, row, meta ) {
                    // Calculate the row number dynamically based on the page info
                    const pageInfo = dt_table.page.info();
                    return pageInfo.start + meta.row + 1; // Adjust for 1-based numbering
                    },
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "user" ) }}' ),
                    orderable: false,
                    render: function( data, type, row, meta ) {
                        if( data ) {
                            return ( data.calling_code ?? '+60' ) + data.phone_number + ' (' + ( data.email ? data.email : '-' ) + ')';
                        } else {
                            return '-';
                        }
                    }
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "plan" ) }}' ),
                    orderable: false,
                    render: function( data, type, row, meta ) {
                        if( data == 2 ) {
                            return 'Trial';
                        }

                        return row?.plan?.name ?? '-';
                    },
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                    orderable: false,
                    render: function( data, type, row, meta ) {
                        return statusMapper[data];
                    },
                },
                {
                    targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                    orderable: false,
                    width: '10%',
                    className: 'text-center',
                    render: function( data, type, row, meta ) {

                        @canany( [ 'edit user_subscriptions', 'view user_subscriptions' ] )
                        let view = '',
                            edit = '',
                            status = '';

                        @can( 'edit user_subscriptions' )
                        edit += '<li class="dropdown-item click-action dt-edit" data-id="' + data + '">{{ __( 'template.edit' ) }}</li>';
                        status = row.status == 10 ? 
                        '<li class="dropdown-item click-action dt-suspend" data-id="' + data + '">{{ __( 'datatables.suspend' ) }}</li>':
                        '' ;
                        @endcan

                        @can( 'view subscription_group_members' )
                        view += '<li class="dropdown-item click-action dt-view" data-id="' + data + '">{{ __( 'template.view' ) }}</li>';
                        @endcan

                        let html = 
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+view+`
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

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.user_subscription.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-view', function() {
            window.location.href = '{{ route( 'admin.module_parent.subscription_group_member.index' ) }}?id=' + $( this ).data( 'id' );
        } );

        let uid = 0,
            status = '',
            scope = '';

        $( document ).on( 'click', '.dt-suspend', function() {

            uid = $( this ).data( 'id' );
            status = 20,
            scope = 'status';

            $( '#modal_confirmation_title' ).html( '{{ __( 'template.x_y', [ 'action' => __( 'datatables.suspend' ), 'title' => Str::singular( __( 'template.user_subscriptions' ) ) ] ) }}' );
            $( '#modal_confirmation_description' ).html( '{{ __( 'template.are_you_sure_to_x_y', [ 'action' => __( 'datatables.suspend' ), 'title' => Str::singular( __( 'template.user_subscriptions' ) ) ] ) }}' );

            modalConfirmation.show();
        } );

        $( document ).on( 'click', '#modal_confirmation_submit', function() {

            switch ( scope ) {
                case 'status':
                    $.ajax( {
                        url: '{{ route( 'admin.user_subscription.updateUserSubscriptionStatus' ) }}',
                        type: 'POST',
                        data: {
                            id: uid,
                            status,
                            _token: '{{ csrf_token() }}',
                        },
                        success: function( response ) {
                            modalConfirmation.hide();
                            $( '#modal_success .caption-text' ).html( response.message );
                            modalSuccess.show();
                            dt_table.draw( false );
                        },
                        error: function( error ) {
                            modalConfirmation.hide();
                            $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                            modalDanger.show();
                        },
                    } );        
            }
        } );
   } );

</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>