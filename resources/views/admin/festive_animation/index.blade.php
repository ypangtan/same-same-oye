<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.festive_animations' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add festive_animations' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.festive_animation.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
        'id' => 'preview',
        'title' => __( 'festive_animation.preview' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'festive_animation.title' ) ] ),
        'id' => 'title',
        'title' => __( 'festive_animation.title' ),
    ],
    [
        'type' => 'select',
        'options' => $data['file_type'],
        'id' => 'file_type',
        'title' => __( 'festive_animation.file_type' ),
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

<x-data-tables id="festive_animation_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

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
    fileTypeMapper = @json( $data['file_type'] ),
    dt_table,
    dt_table_name = '#festive_animation_table',
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
            url: '{{ route( 'admin.festive_animation.allFestiveAnimations' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'festive_animations',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'file_path' },
            { data: 'title' },
            { data: 'file_type' },
            { data: 'status' },
            { data: 'encrypted_id' },
        ],
        columnDefs: [
            {
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
                    const pageInfo = dt_table.page.info();
                    return pageInfo.start + meta.row + 1;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "preview" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    if ( !data ) {
                        return '<img src="{{ asset( 'admin/images/placeholder.png' ) }}" width="75px" />';
                    }
                    switch ( parseInt( row.file_type ) ) {
                        case 1:
                        case 2:
                            return '<img src="' + data + '" width="75px" />';
                        case 3:
                            return '<video src="' + data + '" width="120px" muted loop autoplay playsinline></video>';
                        default:
                            return '<a href="' + data + '" target="_blank"><em class="icon ni ni-file"></em> ' + ( row.file_name ?? '{{ __( 'festive_animation.file' ) }}' ) + '</a>';
                    }
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "title" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return data ?? '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "file_type" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return fileTypeMapper[data] ?? '-';
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

                    @canany( [ 'edit festive_animations', 'delete festive_animations' ] )
                    let edit = '', status = '', remove = '';

                    @can( 'edit festive_animations' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete festive_animations' )
                    status = row['status'] == 10 ?
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>' :
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>';
                    remove = '<li class="dt-delete" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-trash"></em><span>{{ __( 'template.delete' ) }}</span></a></li>';
                    @endcan

                    let html =
                        `
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

        let uid = '';

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.festive_animation.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.festive_animation.updateFestiveAnimationStatus' ) }}',
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

            uid = $( this ).data( 'id' );

            $( '#modal_confirmation_title' ).html( '{{ __( 'template.delete' ) }}' );
            $( '#modal_confirmation_description' ).html( '{{ __( 'festive_animation.delete_confirm' ) }}' );

            modalConfirmation.show();
        } );

        $( document ).on( 'click', '#modal_confirmation_submit', function() {

            $.ajax( {
                url: '{{ route( 'admin.festive_animation.deleteFestiveAnimation' ) }}',
                type: 'POST',
                data: {
                    id: uid,
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
        } );
    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
