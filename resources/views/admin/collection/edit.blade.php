<?php
$collection_edit = 'collection_edit';
$type = $data['type'] ?? null;
$parent_route = $data['parent_route'] ?? '';
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
                {{-- <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist" style="gap:20px;">
                        <button class="nav-link active" id="en_name-tab" data-bs-toggle="tab" data-bs-target="#en_name" type="button" role="tab" aria-controls="en_name" aria-selected="true"> English </button>
                        <button class="nav-link" id="zh_name-tab" data-bs-toggle="tab" data-bs-target="#zh_name" type="button" role="tab" aria-controls="zh_name" aria-selected="false">  中文 </button>
                    </div>
                </nav> --}}
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade pt-4 show active" id="en_name" role="tabpanel" aria-labelledby="en_name-tab">
                        <div class="mb-3 row">
                            <label for="{{ $collection_edit }}_en_name" class="col-sm-4 col-form-label">{{ __( 'collection.name' ) }} </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="{{ $collection_edit }}_en_name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade pt-4" id="zh_name" role="tabpanel" aria-labelledby="zh_name-tab">
                        <div class="mb-3 row">
                            <label for="{{ $collection_edit }}_zh_name" class="col-sm-4 col-form-label">{{ __( 'collection.name' ) }} ( 中文 )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="{{ $collection_edit }}_zh_name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $collection_edit }}_membership_level" class="col-sm-5 col-form-label">{{ __( 'collection.membership' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="{{ $collection_edit }}_membership_level">
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $collection_edit }}_display_type" class="col-sm-5 col-form-label">{{ __( 'collection.display_type' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <select class="form-select" id="{{ $collection_edit }}_display_type">
                                @foreach( $data['display_types'] as $key => $value )
                                <option value="{{ $value['value'] ?? '' }}">{{ $value['title'] ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
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
                        <div class="invalid-feedback"></div>
                    </div>

                    <div id="selected-playlists" class="w-100 h-auto gap-2 my-4"></div>

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

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'collection.display_type_guide' ) }}</h5>
                <div class="row">
                    @foreach( $data['display_types'] as $key => $value)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="mb-3 row">
                                <p class="text-center" style="font-weight:bold;">{{ __( 'collection.type_' . ( $key + 1 ) ) }}</p>
                                <div class="col-sm-7 mx-auto">
                                    <img src="{{ asset( 'admin/images/display_types/' . ( $key + 1 ) . '.png' ) }}" alt="{{ $value['title'] ?? '' }}" class="img-fluid">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let de = '#{{ $collection_edit }}',
            fileID = '',
            selectedPlaylists = [];

        $( de + '_cancel' ).click( function() {
            window.location.href = '{{ $parent_route }}';
        } );

        $( de + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'type_id', '{{ $type }}' );
            formData.append( 'en_name', $( de + '_en_name' ).val() ?? '' );
            formData.append( 'zh_name', $( de + '_zh_name' ).val() ?? '' );
            formData.append( 'display_type', $( de + '_display_type' ).val() ?? '' );
            formData.append( 'membership_level', $( de + '_membership_level' ).is( ':checked' ) ? 1 : 0 );
            formData.append( 'image', fileID ?? '' );
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
                        window.location.href = '{{ $parent_route }}';
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
                    $( de + '_membership_level').prop('checked', response.membership_level == 1);

                    $( de + '_en_name' ).val( response.en_name ?? '' );
                    $( de + '_zh_name' ).val( response.zh_name ?? '' );
                    $( de + '_display_type' ).val( response.display_type ?? '' );

                    imagePath = response.image_url;
                    fileID = response.image;

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
                            this.on("sending", function( file ) {
                                $( 'body' ).loading( {
                                    message: '{{ __( 'template.loading' ) }}'
                                } );
                            });
                            this.on("complete", function( file ) {
                                $( 'body' ).loading( 'stop' );
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

                    selectedPlaylists = [];
                    $('#selected-playlists').empty();
                    $.each( response.playlists, function( i, v ) {
                        data = v;
                        if ( !selectedPlaylists.some( playlist => playlist.id === data.id ) ) {
                            selectedPlaylists.push( {id: data['id'], text: data['name']} );
                            
                            $('#selected-playlists').append(`
                                <span class="playlist-block px-3 py-2 d-flex justify-content-between w-full gap-2 text-black mb-2" data-id="${data['id']}" style="font-size:14px;">
                                    ${data['name']}
                                    <em class="icon ni ni-cross remove-playlist click-action"></em>
                                </span>
                            `);

                            updateHiddenInput();
                        }

                    } );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

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

        $( de + '_playlists' ).on('select2:select', function (e) {
            let data = e.params.data;
            
            if (!selectedPlaylists.some( item => item.id === data.id ) ) {
                selectedPlaylists.push( {id: data.id, text: data.text} );

                $('#selected-playlists').append(`
                    <span class="playlist-block px-3 py-2 d-flex justify-content-between w-full gap-2 text-black mb-2" data-id="${data.id}" style="font-size:14px;">
                        ${data.text}
                        <em class="icon ni ni-cross remove-playlist click-action"></em>
                    </span>
                `);

                updateHiddenInput();
            }

            $( de + '_playlists' ).val(null).trigger('change');
        });

        $(document).on('click', '.remove-playlist', function() {
            let id = $(this).closest('.playlist-block').data('id');
            selectedPlaylists = selectedPlaylists.filter(tag => tag.id !== id);
            $(this).closest('.playlist-block').remove();
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
                $('#selected-playlists .playlist-block').each(function() {
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