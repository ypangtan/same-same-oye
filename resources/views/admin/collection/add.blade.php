<?php
$collection_create = 'collection_create';
$type = $data['type'] ?? null;
$parent_route = $data['parent_route'] ?? '';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.collections' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                {{-- <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist" style="gap:20px;">
                        <button class="nav-link active" id="en_name-tab" data-bs-toggle="tab" data-bs-target="#en_name" type="button" role="tab" aria-controls="en_name" aria-selected="true"> English </button>
                        <button class="nav-link" id="zh_name-tab" data-bs-toggle="tab" data-bs-target="#zh_name" type="button" role="tab" aria-controls="zh_name" aria-selected="false">  中文 </button>
                    </div>
                </nav> --}}
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade pt-4 show active" id="en_name" role="tabpanel" aria-labelledby="en_name-tab">
                        <div class="mb-3 row">
                            <label for="{{ $collection_create }}_en_name" class="col-sm-4 col-form-label">{{ __( 'collection.name' ) }} </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="{{ $collection_create }}_en_name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade pt-4" id="zh_name" role="tabpanel" aria-labelledby="zh_name-tab">
                        <div class="mb-3 row">
                            <label for="{{ $collection_create }}_zh_name" class="col-sm-4 col-form-label">{{ __( 'collection.name' ) }} ( 中文 )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="{{ $collection_create }}_zh_name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $collection_create }}_membership_level" class="col-sm-5 col-form-label">{{ __( 'collection.membership' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="{{ $collection_create }}_membership_level">
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $collection_create }}_display_type" class="col-sm-5 col-form-label">{{ __( 'collection.display_type' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <select class="form-select" id="{{ $collection_create }}_display_type">
                                <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'collection.display_type' ) ] ) }}</option>
                            @foreach( $data['display_types'] as $type )
                                <option value="{{ $type['value'] }}">{{ $type['title'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'collection.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $collection_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="row mb-3">
                    <div>
                        <label for="{{ $collection_create }}_playlists" class="form-label" style="font-size:16px; font-weight:bold;">{{ __( 'collection.playlists' ) }}</label>
                        <select class="form-select form-select-md" id="{{ $collection_create }}_playlists" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'template.playlists' ) ] ) }}">></select>
                    </div>

                    <div id="selected-playlists" class="w-100 h-auto gap-2 my-4"></div>

                    <input type="hidden" name="tags" id="{{ $collection_create }}_hide_playlists">
                </div>
                
                <div class="text-end">
                    <button id="{{ $collection_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $collection_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let dc = '#{{ $collection_create }}',
            fileID = '',
            selectedPlaylists = [];

        $( dc + '_cancel' ).click( function() {
            window.location.href = '{{ $parent_route }}';
        } );

        $( dc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'type_id', '{{ $type }}' );
            formData.append( 'en_name', $( dc + '_en_name' ).val() ?? '' );
            formData.append( 'zh_name', $( dc + '_zh_name' ).val() ?? '' );
            formData.append( 'display_type', $( dc + '_display_type' ).val() ?? '' );
            formData.append( 'membership_level', $( dc + '_membership_level' ).is( ':checked' ) ? 1 : 0 );
            formData.append( 'image', fileID ?? '' );
            formData.append('playlists', JSON.stringify( selectedPlaylists ) );
            
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.collection.createCollection' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ $parent_route }}';
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
        
        let assignPlaylistSelect2 = $( dc + '_playlists' ).select2({

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
                        type: '{{ $type }}',
                        designation: 1,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.playlists.map( function( v, i ) {
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

        $( dc + '_playlists' ).on('select2:select', function (e) {
            let data = e.params.data;
            
            if (!selectedPlaylists.some(tag => tag.id === data.id)) {
                selectedPlaylists.push( {id: data.id, text: data.text} );

                $('#selected-playlists').append(`
                    <span class="item-block px-3 py-2 d-flex justify-content-between w-full gap-2 text-black mb-2" data-id="${data.id}" style="font-size:14px;">
                        ${data.text}
                        <em class="icon ni ni-cross remove-item click-action"></em>
                    </span>
                `);
                updateHiddenInput();
            }

            $( dc + '_playlists' ).val(null).trigger('change');
        });

        $(document).on('click', '.remove-playlist', function() {
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
            $( dc + '_hide_playlists' ).val(JSON.stringify(ids));
        }
        
        $('#selected-playlists').sortable({
            tolerance: 'pointer',
            cursor: 'move',
            update: function(event, ui) {
                // rebuild selectedPalylists order after sorting
                let newOrder = [];
                $('#selected-playlists .item-block').each(function() {
                    let id = $(this).data('id');
                    let item = selectedPalylists.find(i => i.id === id);
                    if (item) newOrder.push(item);
                });
                selectedPalylists = newOrder;
                updateHiddenInput();
            }
        });
        
        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( dc + '_image', { 
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

    } );
</script>