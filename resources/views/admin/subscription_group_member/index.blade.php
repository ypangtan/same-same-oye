<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.subscription_group_members' ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->


<?php
$leader = $data['leader'] ?? '';
$columns = [
    [
        'type' => 'default',
        'id' => 'dt_no',
        'title' => 'No.',
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'subscription_group_member.user' ) ] ),
        'id' => 'user',
        'title' => __( 'subscription_group_member.user' ),
    ],
    [
        'type' => 'select',
        'options' => $data['status'],
        'id' => 'status',
        'title' => __( 'datatables.status' ),
    ],
];
?>

<x-data-tables id="subscription_group_member_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

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
        dt_table_name = '#subscription_group_member_table',
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
                url: '{{ route( 'admin.subscription_group_member.allSubscriptionGroupMembers' ) }}',
                data: {
                    '_token': '{{ csrf_token() }}',
                },
                dataSrc: 'subscription_group_members',
            },
            lengthMenu: [
                [ 10, 25, 50, 999999 ],
                [ 10, 25, 50, '{{ __( 'datatables.all' ) }}' ]
            ],
            order: [[ 1, 'desc' ]],
            columns: [
                { data: null },
                { data: 'user' },
                { data: 'status' },
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
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "leader" ) }}' ),
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
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                    orderable: false,
                    render: function( data, type, row, meta ) {
                        return statusMapper[data];
                    },
                },
            ],
        },
        table_no = 0,
        timeout = null;

</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>