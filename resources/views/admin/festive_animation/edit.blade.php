<?php
$festive_animation_edit = 'festive_animation_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.festive_animations' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

                <div class="mb-3">
                    <label>{{ __( 'festive_animation.file' ) }}</label>
                    <div class="form-note mb-2">{{ __( 'festive_animation.file_hint' ) }}</div>
                    <div class="dropzone mb-3" id="{{ $festive_animation_edit }}_file" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist" style="gap:20px;">
                        <button class="nav-link active" id="en_title-tab" data-bs-toggle="tab" data-bs-target="#en_title" type="button" role="tab" aria-controls="en_title" aria-selected="true"> English </button>
                        <button class="nav-link" id="zh_title-tab" data-bs-toggle="tab" data-bs-target="#zh_title" type="button" role="tab" aria-controls="zh_title" aria-selected="false"> 中文 </button>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade pt-4 show active" id="en_title" role="tabpanel" aria-labelledby="en_title-tab">
                        <div class="mb-3 row">
                            <label for="{{ $festive_animation_edit }}_en_title" class="col-sm-4 col-form-label">{{ __( 'festive_animation.title' ) }} ( English )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $festive_animation_edit }}_en_title">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade pt-4" id="zh_title" role="tabpanel" aria-labelledby="zh_title-tab">
                        <div class="mb-3 row">
                            <label for="{{ $festive_animation_edit }}_zh_title" class="col-sm-4 col-form-label">{{ __( 'festive_animation.title' ) }} ( 中文 )</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $festive_animation_edit }}_zh_title">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $festive_animation_edit }}_url" class="col-sm-4 col-form-label">{{ __( 'festive_animation.url' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $festive_animation_edit }}_url" placeholder="https://">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $festive_animation_edit }}_start_date" class="col-sm-4 col-form-label">{{ __( 'festive_animation.start_date' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $festive_animation_edit }}_start_date" placeholder="{{ __( 'festive_animation.date_placeholder' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $festive_animation_edit }}_end_date" class="col-sm-4 col-form-label">{{ __( 'festive_animation.end_date' ) }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="{{ $festive_animation_edit }}_end_date" placeholder="{{ __( 'festive_animation.date_placeholder' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $festive_animation_edit }}_sequence" class="col-sm-4 col-form-label">{{ __( 'festive_animation.sequence' ) }}</label>
                    <div class="col-sm-8">
                        <input type="number" class="form-control form-control-sm" id="{{ $festive_animation_edit }}_sequence" value="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $festive_animation_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $festive_animation_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $festive_animation_edit }}',
            fileID = '',
            fileName = '',
            startDate = flatpickr( fe + '_start_date', {
                dateFormat: 'Y-m-d',
                disableMobile: true,
                allowInput: true,
            } ),
            endDate = flatpickr( fe + '_end_date', {
                dateFormat: 'Y-m-d',
                disableMobile: true,
                allowInput: true,
            } );

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.festive_animation.index' ) }}';
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'en_title', $( fe + '_en_title' ).val() );
            formData.append( 'zh_title', $( fe + '_zh_title' ).val() );
            formData.append( 'file', fileID ?? '' );
            formData.append( 'file_name', fileName ?? '' );
            formData.append( 'url', $( fe + '_url' ).val() );
            formData.append( 'start_date', $( fe + '_start_date' ).val() );
            formData.append( 'end_date', $( fe + '_end_date' ).val() );
            formData.append( 'sequence', $( fe + '_sequence' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.festive_animation.updateFestiveAnimation' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.festive_animation.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( fe + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        getFestiveAnimation();

        function getFestiveAnimation() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.festive_animation.oneFestiveAnimation' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( fe + '_en_title' ).val( response.en_title ?? '' );
                    $( fe + '_zh_title' ).val( response.zh_title ?? '' );
                    $( fe + '_url' ).val( response.url ?? '' );
                    $( fe + '_sequence' ).val( response.sequence ?? 0 );
                    startDate.setDate( response.start_date ?? null );
                    endDate.setDate( response.end_date ?? null );

                    fileID = response.file ?? '';
                    fileName = response.file_name ?? '';
                    let filePath = response.file_path ?? '';

                    const dropzone = new Dropzone( fe + '_file', {
                        url: '{{ route( 'admin.festive_animation.fileUpload' ) }}',
                        params: {
                            '_token': '{{ csrf_token() }}',
                        },
                        maxFiles: 1,
                        maxFilesize: 100,
                        timeout: 0,
                        addRemoveLinks: true,
                        init: function() {
                            this.on( 'addedfile', function( file ) {
                                if ( this.files.length > 1 ) {
                                    this.removeFile( this.files[0] );
                                }
                            } );
                            this.on( 'sending', function( file ) {
                                $( 'body' ).loading( {
                                    message: '{{ __( 'template.loading' ) }}'
                                } );
                            } );
                            this.on( 'complete', function( file ) {
                                $( 'body' ).loading( 'stop' );
                            } );
                            if ( filePath ) {
                                let myDropzone = this,
                                    mockFile = { name: fileName || 'Default', size: 1024, accepted: true };

                                myDropzone.files.push( mockFile );
                                if ( [ 1, 2 ].includes( parseInt( response.file_type ) ) ) {
                                    myDropzone.displayExistingFile( mockFile, filePath );
                                } else {
                                    myDropzone.emit( 'addedfile', mockFile );
                                    myDropzone.emit( 'complete', mockFile );
                                }
                            }
                        },
                        removedfile: function( file ) {
                            fileID = null;
                            fileName = null;
                            file.previewElement.remove();
                        },
                        success: function( file, response ) {
                            fileID = response.file;
                            fileName = response.file_name;
                        },
                        error: function( file, message ) {
                            $( '#modal_danger .caption-text' ).html( message.message ?? message );
                            modalDanger.toggle();
                            this.removeFile( file );
                        }
                    } );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
    } );
</script>
