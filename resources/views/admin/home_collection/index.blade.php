<?php
$home_collection = 'home_collection';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.home_layout' ) }}</h3>
            <div class="nk-block-des text-soft">
                <p>{{ __( 'template.home_layout_hint' ) }}</p>
            </div>
        </div><!-- .nk-block-head-content -->
        @can( 'add home_collections' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <button type="button" id="{{ $home_collection }}_add" class="btn btn-primary">{{ __( 'template.add' ) }}</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- .nk-block-head-content -->
        @endcan
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<?php
$enableReorder = auth()->user()->can( 'edit home_collections' ) ? 1 : 0;

$columns = [
    [
        'type' => 'default',
        'id' => 'dt_no',
        'title' => 'No.',
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'collection.title' ) ] ),
        'id' => 'title',
        'title' => __( 'collection.title' ),
    ],
    [
        'type' => 'default',
        'id' => 'type',
        'title' => __( 'collection.type' ),
    ],
    [
        'type' => 'default',
        'id' => 'display_type',
        'title' => __( 'collection.display_type' ),
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

<x-data-tables id="{{ $home_collection }}_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

@canany( [ 'add home_collections', 'edit home_collections' ] )
<div class="modal fade" id="{{ $home_collection }}_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $home_collection }}_modal_title"></h5>
                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close"><em class="icon ni ni-cross"></em></a>
            </div>
            <div class="modal-body">
                <input type="hidden" id="{{ $home_collection }}_id">
                <div class="form-group">
                    <label class="form-label" for="{{ $home_collection }}_collection">{{ Str::singular( __( 'template.collections' ) ) }}</label>
                    <div class="form-control-wrap">
                        <select class="form-control" id="{{ $home_collection }}_collection" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => Str::singular( __( 'template.collections' ) ) ] ) }}"></select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="{{ $home_collection }}_display_type">{{ __( 'collection.display_type' ) }}</label>
                    <div class="form-control-wrap">
                        <select class="form-select" id="{{ $home_collection }}_display_type">
                            @foreach( $data['display_types'] as $value )
                            <option value="{{ $value['value'] }}">{{ $value['title'] }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="{{ $home_collection }}_modal_submit">{{ __( 'template.save_changes' ) }}</button>
            </div>
        </div>
    </div>
</div>
@endcanany

<div class="card mt-4">
    <div class="card-inner">
        <h5 class="card-title mb-4">{{ __( 'collection.display_type_guide' ) }}</h5>
        <div class="row">
            @foreach( $data['display_types'] as $key => $value )
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="mb-3 row">
                        <p class="text-center" style="font-weight:bold;">{{ $value['title'] ?? '' }}</p>
                        <div class="col-sm-7 mx-auto">
                            <img src="{{ asset( 'admin/images/display_types/' . $value['value'] . '.png' ) }}" alt="{{ $value['title'] ?? '' }}" class="img-fluid">
                        </div>
                    </div>
                </div>
            @endforeach
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

var displayTypes = @json( $data['display_types'] ),
    canEdit = parseInt( '{{ $enableReorder }}' ) == 1,
    dt_table,
    dt_table_name = '#{{ $home_collection }}_table',
    dt_table_config = {
        language: {
            'lengthMenu': '{{ __( "datatables.lengthMenu" ) }}',
            'zeroRecords': '{{ __( "datatables.zeroRecords" ) }}',
            'emptyTable': '{{ __( "template.no_home_collections" ) }}',
            'info': '{{ __( "datatables.info" ) }}',
            'infoEmpty': '{{ __( "datatables.infoEmpty" ) }}',
            'infoFiltered': '{{ __( "datatables.infoFiltered" ) }}',
            'paginate': {
                'previous': '{{ __( "datatables.previous" ) }}',
                'next': '{{ __( "datatables.next" ) }}',
            }
        },
        ajax: {
            url: '{{ route( 'admin.home_collection.allHomeCollections' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'home_collections',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 0, 'asc' ]],
        columns: [
            { data: 'priority' },
            { data: 'name' },
            { data: 'type' },
            { data: 'display_type' },
            { data: 'encrypted_id' },
        ],
        columnDefs: [
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return data;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "title" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    let html = data ? $( '<div>' ).text( data ).html() : '-';
                    if ( row.priority == 1 ) {
                        html += ' <span class="badge bg-primary ms-1">{{ __( 'template.recommended' ) }}</span>';
                    }
                    return html;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "type" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return data ? $( '<div>' ).text( data ).html() : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "display_type" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    let displayType = displayTypes.find( function( v ) {
                        return String( v.value ) == String( data );
                    } );
                    return displayType ? displayType.title : '-';
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @canany( [ 'edit home_collections', 'delete home_collections' ] )
                    let edit = '', dt_delete = '';

                    @can( 'edit home_collections' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete home_collections' )
                    dt_delete = '<li class="dt-delete" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-trash"></em><span>{{ __( 'datatables.delete' ) }}</span></a></li>';
                    @endcan

                    let html =
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
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
    timeout = null,
    reorderPath = '{{ route( 'admin.home_collection.updateOrder' ) }}';

    if ( canEdit ) {

        dt_table_config.rowReorder = {
            selector: '.dt-reorder',
            dataSrc: 'priority',
            update: false,
        };

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

        let hc = '#{{ $home_collection }}';

        function showError( error ) {
            let message = error.status === 422
                ? Object.values( error.responseJSON.errors ).flat().join( '<br>' )
                : error.responseJSON.message;

            $( '#modal_danger .caption-text' ).html( message );
            modalDanger.toggle();
        }

        @canany( [ 'add home_collections', 'edit home_collections' ] )
        let hcModal = new bootstrap.Modal( document.getElementById( '{{ $home_collection }}_modal' ) );

        // Empty id = add, otherwise edit that home collection
        function openModal( row ) {
            resetInputValidation();

            $( hc + '_id' ).val( row ? row.encrypted_id : '' );
            $( hc + '_modal_title' ).text( row
                ? '{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.collections' ) ) ] ) }}'
                : '{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.collections' ) ) ] ) }}' );

            $( hc + '_collection' ).empty();
            if ( row ) {
                let text = row.name + ( row.type ? ' (' + row.type + ')' : '' );
                $( hc + '_collection' ).append( new Option( text, row.collection_id, true, true ) );
            }
            $( hc + '_collection' ).trigger( 'change' );

            $( hc + '_display_type' ).val( row ? String( row.display_type ) : displayTypes[0].value );
            hcModal.show();
        }

        @can( 'add home_collections' )
        $( hc + '_add' ).click( function() {
            openModal( null );
        } );
        @endcan

        @can( 'edit home_collections' )
        $( document ).on( 'click', '.dt-edit', function() {
            openModal( dt_table.row( $( this ).closest( 'tr' ) ).data() );
        } );
        @endcan

        $( hc + '_collection' ).select2( {

            theme: 'bootstrap-5',
            width: '100%',
            placeholder: $( hc + '_collection' ).data( 'placeholder' ),
            closeOnSelect: true,
            dropdownParent: $( hc + '_modal .modal-content' ),

            ajax: {
                url: '{{ route( 'admin.collection.allCollections' ) }}',
                type: 'post',
                dataType: 'json',
                delay: 250,
                data: function( params ) {
                    return {
                        title: params.term,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function( data, params ) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.collections.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.name + ( v.type ? ' (' + v.type.name + ')' : '' ),
                            display_type: v.display_type,
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
        } );

        // Default to the collection's own display type; admin can still override it
        $( hc + '_collection' ).on( 'select2:select', function( e ) {
            if ( e.params.data.display_type ) {
                $( hc + '_display_type' ).val( String( e.params.data.display_type ) );
            }
        } );

        $( hc + '_modal_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let id = $( hc + '_id' ).val();

            $.ajax( {
                url: id
                    ? '{{ route( 'admin.home_collection.updateHomeCollection' ) }}'
                    : '{{ route( 'admin.home_collection.addHomeCollection' ) }}',
                type: 'POST',
                data: {
                    'id': id,
                    'collection': $( hc + '_collection' ).val(),
                    'display_type': $( hc + '_display_type' ).val(),
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    hcModal.hide();
                    dt_table.draw( false );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( hc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        showError( error );
                    }
                }
            } );
        } );
        @endcanany

        @can( 'delete home_collections' )
        $( document ).on( 'click', '.dt-delete', function() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.home_collection.deleteHomeCollection' ) }}',
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
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    showError( error );
                }
            } );
        } );
        @endcan
    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
