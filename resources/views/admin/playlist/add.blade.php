<?php
$playlist_create = 'playlist_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.playlists' ) ) ] ) }}</h3>
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
                            <label for="{{ $playlist_create }}_en_name" class="col-sm-4 col-form-label">{{ __( 'playlist.name' ) }} ( English )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="{{ $playlist_create }}_en_name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade pt-4" id="zh_name" role="tabpanel" aria-labelledby="zh_name-tab">
                        <div class="mb-3 row">
                            <label for="{{ $playlist_create }}_zh_name" class="col-sm-4 col-form-label">{{ __( 'playlist.name' ) }} ( 中文 )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="{{ $playlist_create }}_zh_name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $playlist_create }}_category" class="col-sm-5 col-form-label">{{ __( 'playlist.category' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-control select2" id="{{ $playlist_create }}_category" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'template.category' ) ] ) }}"></select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $playlist_create }}_membership_level" class="col-sm-5 col-form-label">{{ __( 'playlist.membership' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="{{ $playlist_create }}_membership_level" checked>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'playlist.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $playlist_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="row mb-3 w-100">
                    <div>
                        <label for="{{ $playlist_create }}_items" class="form-label" style="font-size:16px; font-weight:bold;">{{ __( 'playlist.items' ) }}</label>
                        <select class="form-select form-select-md" id="{{ $playlist_create }}_items" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'template.items' ) ] ) }}">></select>
                    </div>

                    <div id="selected-items" class="w-100 h-auto gap-2 my-4"></div>

                    <input type="hidden" name="tags" id="{{ $playlist_create }}_hide_items">
                </div>
                <div class="text-end">
                    <button id="{{ $playlist_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $playlist_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let dc = '#{{ $playlist_create }}',
            fileID = '',
            selectedItems = [];

        $( dc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.playlist.index' ) }}';
        } );

        $( dc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'category_id', $( dc + '_category' ).val() ?? '' );
            formData.append( 'en_name', $( dc + '_en_name' ).val() ?? '' );
            formData.append( 'zh_name', $( dc + '_zh_name' ).val() ?? '' );
            formData.append( 'membership_level', $( dc + '_membership_level' ).is( ':checked' ) ? 1 : 0 );
            formData.append( 'image', fileID );
            formData.append('items', JSON.stringify( selectedItems ) );
            
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.playlist.createPlaylist' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.playlist.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( dc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );
        
        $( dc + '_category' ).select2({

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
                        item: params.term, // search term
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

        let assignItemSelect2 = $( dc + '_items' ).select2({

            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: true,

            ajax: { 
                url: '{{ route( 'admin.item.allItems' ) }}',
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

                    data.items.map( function( v, i ) {
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

        $( dc + '_items' ).on('select2:select', function (e) {
            let data = e.params.data;
            
            if (!selectedItems.some(tag => tag.id === data.id)) {
                selectedItems.push( {id: data.id, text: data.text} );

                $('#selected-items').append(`
                    <span class="item-block rounded-pill border px-3 py-2 mb-2 d-flex align-items-center gap-2 text-black" data-id="${data.id}" style="font-size:14px;">
                        ${data.text}
                        <i class="icon icon-icon16-close remove-item click-action" style="font-size:23px;"></i>
                    </span>
                `);

                updateHiddenInput();
            }

            $( dc + '_items' ).val(null).trigger('change');
        });

        $(document).on('click', '.remove-item', function() {
            let id = $(this).closest('.item-block').data('id');
            selectedItems = selectedItems.filter(tag => tag.id !== id);
            $(this).closest('.item-block').remove();
            updateHiddenInput();
        });

        $('#clearTags').on('click', function(e) {
            e.preventDefault();
            selectedItems = [];
            $('#selected-items').empty();
            updateHiddenInput();
        });

        function updateHiddenInput() {
            let ids = selectedItems.map(tag => tag.id);
            $( dc + '_hide_items' ).val(JSON.stringify(ids));
        }
        
        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( dc + '_image', { 
            url: '{{ route( 'admin.playlist.imageUpload' ) }}',
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

        $('#selected-items').sortable({
            tolerance: 'pointer',
            cursor: 'move',
            update: function(event, ui) {
                // rebuild selectedItems order after sorting
                let newOrder = [];
                $('#selected-items .item-block').each(function() {
                    let id = $(this).data('id');
                    let item = selectedItems.find(i => i.id === id);
                    if (item) newOrder.push(item);
                });
                selectedItems = newOrder;
                updateHiddenInput();
            }
        });

    } );
</script>