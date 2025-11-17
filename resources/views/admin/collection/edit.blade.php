<?php
$collection_edit = 'collection_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.collections' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist" style="gap:20px;">
                        <button class="nav-link active" id="en_name-tab" data-bs-toggle="tab" data-bs-target="#en_name" type="button" role="tab" aria-controls="en_name" aria-selected="true"> English </button>
                        <button class="nav-link" id="zh_name-tab" data-bs-toggle="tab" data-bs-target="#zh_name" type="button" role="tab" aria-controls="zh_name" aria-selected="false">  中文 </button>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade pt-4 show active" id="en_name" role="tabpanel" aria-labelledby="en_name-tab">
                        <div class="mb-3 row">
                            <label for="{{ $collection_edit }}_en_name" class="col-sm-4 col-form-label">{{ __( 'collection.name' ) }} ( English )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $collection_edit }}_en_name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade pt-4" id="zh_name" role="tabpanel" aria-labelledby="zh_name-tab">
                        <div class="mb-3 row">
                            <label for="{{ $collection_edit }}_zh_name" class="col-sm-4 col-form-label">{{ __( 'collection.name' ) }} ( 中文 )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $collection_edit }}_zh_name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $collection_edit }}_category" class="col-sm-5 col-form-label">{{ __( 'collection.category' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-control select2" id="{{ $collection_edit }}_category" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'template.category' ) ] ) }}"></select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $collection_edit }}_membership_level" class="col-sm-5 col-form-label">{{ __( 'collection.min_membership_level' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="{{ $collection_edit }}_membership_level">
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $collection_edit }}_priority" class="col-sm-5 col-form-label">{{ __( 'collection.priority' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $collection_edit }}_priority">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'collection.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $collection_edit }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="row mb-3">
                    <div>
                        <label for="{{ $collection_edit }}_playlists" class="form-label" style="font-size:16px; font-weight:bold;">{{ __( 'collection.playlists' ) }}</label>
                        <select class="form-select form-select-md" id="{{ $collection_edit }}_playlists" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'collection.playlists' ) ] ) }}"></select>
                    </div>

                    <div id="selected-playlists" class="w-auto h-auto mb-2 gap-2 my-4"></div>

                    <input type="hidden" name="tags" id="{{ $collection_edit }}_hide_playlists">
                </div>
                <div class="text-end">
                    <button id="{{ $collection_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $collection_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
window.ckeupload_path = '{{ route( 'admin.collection.ckeUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
window.cke_element = [ 'collection_edit_en_name', 'collection_edit_zh_name' ];
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multi.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let de = '#{{ $collection_edit }}',
            fileID = '',
            selectedPlaylists = [];

        $( de + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.collection.index' ) }}';
        } );

        $( de + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'category_id', $( de + '_category' ).val() ?? '' );
            formData.append( 'en_name', editors['collection_edit_en_name'].getData()  );
            formData.append( 'zh_name', editors['collection_edit_zh_name'].getData() );
            formData.append( 'priority', $( de + '_priority' ).val() );
            formData.append( 'membership_level', $( de + '_membership_level' ).is( ':checked' ) ? 1 : 0 );
            formData.append( 'image', fileID );
            formData.append('playlists', JSON.stringify( selectedPlaylists ) );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.collection.updateCollection' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.collection.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( de + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        getCollection();

        function getCollection() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.collection.oneCollection' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    $( de + '_priority' ).val( response.priority );
                    $(de + '_membership_level').prop('checked', response.membership_level == 1);

                    editors['collection_edit_en_name'].setData( response.en_name ?? '' );
                    editors['collection_edit_zh_name'].setData( response.zh_name ?? '' );

                    imagePath = response.image_url;
                    fileID = response.image_url;

                    const dropzone = new Dropzone( de + '_image', { 
                        url: '{{ route( 'admin.collection.imageUpload' ) }}',
                        maxFiles: 1,
                        acceptedFiles: 'image/jpg,image/jpeg,image/png',
                        addRemoveLinks: true,
                        init: function() {
                            this.on("addedfile", function (file) {
                                if (this.files.length > 1) {
                                    this.removeFile(this.files[0]);
                                }
                            });
                        },
                        removedfile: function( file ) {
                            fileID = null;
                            file.previewElement.remove();
                        },
                        success: function( file, response ) {
                            fileID = response.file;
                        }
                    } );

                    if( response.category != null ){
                        let option1 = new Option( response.category.name, response.category.id, true, true );
                        categorySelect2.append( option1 );
                        categorySelect2.trigger( 'change' );
                    }

                    selectedPlaylists = [];
                    $('#selected-playlists').empty();
                    $.each( response.playlists, function( i, v ) {
                        data = v;
                        if ( !selectedPlaylists.some( playlist => playlist.id === data.id ) ) {
                            selectedPlaylists.push( {id: data['id'], text: data['name']} );
                            
                            $('#selected-playlists').append(`
                                <span class="item-block px-2 py-2 d-flex align-items-center gap-2 mb-2 text-black" data-id="${data['id']}" style="font-weight:normal; border-radius:4px; font-size:14px;">
                                    ${data['name']}
                                    <i class="icon icon-icon16-close remove-collection click-action" style="font-size:20px;"></i>
                                </span>
                            `);

                            updateHiddenInput();
                        }

                        $( de + '_playlists' ).val(null).trigger('change');
                    } );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
        
        categorySelect2 = $( de + '_category' ).select2({

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
                        name: params.term, // search term
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

        let assignPlaylistSelect2 = $( de + '_playlists' ).select2({

            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: true,

            ajax: { 
                url: '{{ route( 'admin.playlist.allPlaylists' ) }}',
                type: "post",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        title: params.term, // search term
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
                            text: v.title,
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

        $( de + '_playlists' ).on('select2:select', function (e) {
            let data = e.params.data;
            
            if (!selectedPlaylists.some( item => item.id === data.id ) ) {
                selectedPlaylists.push( {id: data.id, text: data.text} );

                $('#selected-playlists').append(`
                    <span class="item-block rounded-pill border px-3 py-2 d-flex align-items-center gap-2 mb-2 text-black" data-id="${data.id}" style="font-size:14px;">
                        ${data.text}
                        <i class="icon icon-icon16-close remove-collection click-action" style="font-size:23px;"></i>
                    </span>
                `);

                updateHiddenInput();
            }

            $('#assign_tag').val(null).trigger('change');
        });

        $(document).on('click', '.remove-collection', function() {
            let id = $(this).closest('.item-block').data('id');
            selectedPlaylists = selectedPlaylists.filter(tag => tag.id !== id);
            $(this).closest('.item-block').remove();
            updateHiddenInput();
        });

        $('#clearTags').on('click', function(e) {
            e.preventDefault();
            selectedPlaylists = [];
            $('#selected-playlists').empty();
            updateHiddenInput();
        });

        function updateHiddenInput() {
            let ids = selectedPlaylists.map(tag => tag.id);
            $( de + '_hide_playlists' ).val(JSON.stringify(ids));
        }
        
        $('#selected-playlists').sortable({
            tolerance: 'pointer',
            cursor: 'move',
            update: function(event, ui) {
                // rebuild selectedPlaylists order after sorting
                let newOrder = [];
                $('#selected-playlists .item-block').each(function() {
                    let id = $(this).data('id');
                    let item = selectedPlaylists.find(i => i.id === id);
                    if (item) newOrder.push(item);
                });
                selectedPlaylists = newOrder;
                updateHiddenInput();
            }
        });
    } );
</script>