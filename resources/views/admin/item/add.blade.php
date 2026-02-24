<?php
$item_create = 'item_create';
$type = $data['type'] ?? null;
$parent_route = $data['parent_route'] ?? '';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.items' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $item_create }}_title" class="col-sm-5 col-form-label">{{ __( 'item.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $item_create }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $item_create }}_author" class="col-sm-5 col-form-label">{{ __( 'item.author' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control"  style="min-height: 80px;" id="{{ $item_create }}_author"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $item_create }}_desc" class="col-sm-5 col-form-label">{{ __( 'item.desc' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $item_create }}_desc">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $item_create }}_membership_level" class="col-sm-5 col-form-label">{{ __( 'item.membership' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="{{ $item_create }}_membership_level">
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $item_create }}_upload_type" class="col-sm-5 col-form-label">{{ __( 'item.upload_type' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <select class="form-select" id="{{ $item_create }}_upload_type">
                                <option value="1" selected>{{ __( 'item.upload_file' ) }}</option>
                                <option value="2">{{ __( 'item.upload_url' ) }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row item-file">
                    <label>{{ __( 'item.song' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $item_create }}_file" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row d-none item-url">
                    <label for="{{ $item_create }}_url" class="col-sm-5 col-form-label">{{ __( 'item.url' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input type="text" class="form-control" id="{{ $item_create }}_url">
                        </div>
                    </div>
                </div>
                <div class="mb-3 row d-none item-url">
                    <label for="{{ $item_create }}_url_type" class="col-sm-5 col-form-label">{{ __( 'item.url_type' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <select class="form-select" id="{{ $item_create }}_url_type">
                                <option value="1" selected>{{ __( 'item.video' ) }}</option>
                                <option value="2">{{ __( 'item.live' ) }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'item.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $item_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="text-end">
                    <button id="{{ $item_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $item_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
window.ckeupload_path = '{{ route( 'admin.item.ckeUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
window.cke_element = [ 'item_create_desc'];
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multi.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let dc = '#{{ $item_create }}',
            fileID = '',
            song_file = '',
            song_file_type = '',
            duration = '',
            file2ID = '',
            songPath = '';

        $( dc + '_upload_type' ).change( function() {
            let selectedType = $( this ).val();

            if ( selectedType == 1 ) {
                $( '.item-file' ).removeClass( 'd-none' );
                $( '.item-url' ).addClass( 'd-none' );
            } else {
                $( '.item-file' ).addClass( 'd-none' );
                $( '.item-url' ).removeClass( 'd-none' );
            }
        } );

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
            formData.append( 'title', $( dc + '_title' ).val() ?? '' );
            formData.append( 'desc', editors['item_create_desc'].getData() );
            formData.append( 'file', file2ID ?? '' );
            formData.append( 'file_name', song_file ?? '' );
            formData.append( 'file_type', song_file_type ?? '' );
            formData.append( 'duration', duration ?? '' );
            formData.append( 'image', fileID ?? '' );
            formData.append( 'author', $( dc + '_author' ).val() ?? '' );
            formData.append( 'upload_type', $( dc + '_upload_type' ).val() ?? '' );
            formData.append( 'url', $( dc + '_url' ).val() ?? '' );
            formData.append( 'url_type', $( dc + '_url_type' ).val() ?? '' );
            formData.append( 'membership_level', $( dc + '_membership_level' ).is( ':checked' ) ? 1 : 0 );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.item.createItem' ) }}',
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
        
        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( dc + '_image', { 
            url: '{{ route( 'admin.item.imageUpload' ) }}',
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

        const dropzone2 = new Dropzone( dc + '_file', { 
            url: '{{ route("admin.item.songUpload") }}',
            maxFiles: 1,
            acceptedFiles: 'audio/mpeg,audio/mp3,video/mp4,video/webm,video/ogg,video/avi,video/mov,video/quicktime',
            addRemoveLinks: true,
            previewTemplate: `
                <div class="dz-preview dz-file-preview" style="cursor:pointer;">
                    <img src="{{ asset('admin/images/song.png') }}" 
                        style="width:120px;height:120px;object-fit:contain;">
                    
                    <div class="dz-details" style="margin-top:5px;">
                        <div class="dz-filename"><span data-dz-name></span></div>
                        <div class="dz-size" data-dz-size></div>
                    </div>
                </div>
            `,
            init: function() {
                this.on("addedfile", function(file) {
                    if (this.files.length > 1) {
                        this.removeFile(this.files[0]);
                    }
                    file.previewElement.addEventListener("click", () => {
                        if (file._fileUrl) window.open(file._fileUrl, "_blank");
                    });
                });
            },
            removedfile: function(file) {
                file2ID = "";
                if (file.previewElement) file.previewElement.remove();
            },
            success: function(file, response) {
                file2ID = response.file;
                song_file = response.file_name ?? '';
                song_file_type = response.file_type ?? '';
                duration = response.duration ?? '';
                file._fileUrl = response.url;

                file.previewElement.addEventListener("click", () => {
                    window.open(response.url, "_blank");
                });
            }
        });

    } );
</script>