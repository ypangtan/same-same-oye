<?php
$category_edit = 'category_edit';
$type = $data['type'] ?? null;
$parent_route = $data['parent_route'] ?? null;
?>
<style>
    .evo-pop {
        background: #fff !important;
        border: 1px solid #ddd !important;
        border-radius: 6px !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15) !important;
        padding: 8px !important;
        width: auto !important;
        z-index: 9999 !important;
    }

    .evo-pop-overlay {
        position: fixed;
        width: 100vw;
        height: 100vh;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.25);
        z-index: 9998;
    }

    .evo-cp-wrap{
        width: auto !important;
        display: flex;
        height: auto;
        align-items: center;
        gap: 4px;
        position: relative;
    }

    .evo-pointer, .evo-colorind{
        position: absolute;
        left: 100%;
        margin-left: 4px;
    }

    .evo-pop{
        top: 100%;
        height: auto;
    }
</style>
<div class="evo-pop-overlay d-none"></div>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.categorys' ) ) ] ) }}</h3>
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
                            <label for="{{ $category_edit }}_en_name" class="col-sm-4 col-form-label">{{ __( 'category.name' ) }} </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="{{ $category_edit }}_en_name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade pt-4" id="zh_name" role="tabpanel" aria-labelledby="zh_name-tab">
                        <div class="mb-3 row">
                            <label for="{{ $category_edit }}_zh_name" class="col-sm-4 col-form-label">{{ __( 'category.name' ) }} ( 中文 )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="{{ $category_edit }}_zh_name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'category.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $category_edit }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3">
                    <label for="{{ $category_edit }}_color" class="col-sm-4 col-form-label">{{ __( 'tag.color' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="{{ $category_edit }}_color" autocomplete="off">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $category_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $category_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let de = '#{{ $category_edit }}',
            fileID = '';

        const $cp = $( ue + '_color' ).colorpicker({
            appendTo: 'body',
            hideButton: false
        });

        $cp.on('click', function () {
            $cp.colorpicker('showPalette');
            $('.evo-pop-overlay').removeClass('d-none');
        });

        $( '.evo-colorind' ).click( function () {
            $cp.colorpicker('showPalette');
            $('.evo-pop-overlay').removeClass('d-none');
        } );

        $cp.on('change', function (ev, color) {
            $('.evo-pop-overlay').addClass('d-none');
        });

        $('.evo-pop-overlay').on('click', function () {
            $cp.colorpicker('hidePalette');
            $(this).addClass('d-none');
        });

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
            // formData.append( 'zh_name', $( de + '_zh_name' ).val() ?? '' );
            formData.append( 'color', $( de + '_color').val() );
            formData.append( 'image', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.category.updateCategory' ) }}',
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
        getCategory();

        function getCategory() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.category.oneCategory' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    $( de + '_en_name' ).val( response.en_name ?? '' );
                    $( de + '_zh_name' ).val( response.zh_name ?? '' );

                    imagePath = response.image_url;
                    fileID = response.image;
                    $cp.colorpicker( 'val', response.color ).trigger( 'change' );

                    const dropzone = new Dropzone( de + '_image', { 
                        url: '{{ route( 'admin.category.imageUpload' ) }}',
                        maxFiles: 1,
                        acceptedFiles: 'image/jpg,image/jpeg,image/png',
                        addRemoveLinks: true,
                        init: function() {
                            this.on("addedfile", function (file) {
                                if (this.files.length > 1) {
                                    this.removeFile(this.files[0]);
                                }
                            });
                            if ( imagePath ) {
                                let myDropzone = this,
                                    mockFile = { name: 'Default', size: 1024, accepted: true };

                                myDropzone.files.push( mockFile );
                                myDropzone.displayExistingFile( mockFile, imagePath );
                            }
                        },
                        removedfile: function( file ) {
                            fileID = null;
                            file.previewElement.remove();
                        },
                        success: function( file, response ) {
                            fileID = response.file;
                        }
                    } );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
    } );
</script>