<?php
$app_version_create = 'app_version_create';
$platforms = $data['platform'];
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.app_versions' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                
                <div class="mb-3 row">
                    <label for="{{ $app_version_create }}_version" class="col-sm-5 form-label">{{ __( 'app_version.version' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $app_version_create }}_version">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $app_version_create }}_plaform" class="col-sm-5 form-label">{{ __( 'app_version.plaform' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $app_version_create }}_platform">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'app_version.plaform' ) ] ) }}</option>
                            @forEach( $platforms as $key => $platform )
                                <option value="{{ $key }}">{{ $platform }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $app_version_create }}_force_logout" class="col-sm-5 form-label">{{ __( 'app_version.force_logout' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $app_version_create }}_force_logout">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'app_version.force_logout' ) ] ) }}</option>
                            <option value="10"> {{ __( 'app_version.true' ) }} </option>
                            <option value="20"> {{ __( 'app_version.false' ) }} </option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist" style="gap:20px;">
                        <button class="nav-link active" id="en_title-tab" data-bs-toggle="tab" data-bs-target="#en_title" type="button" role="tab" aria-controls="en_title" aria-selected="true"> English </button>
                        <button class="nav-link" id="zh_title-tab" data-bs-toggle="tab" data-bs-target="#zh_title" type="button" role="tab" aria-controls="zh_title" aria-selected="false">  中文 </button>
                    </div>
                </nav>

                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade pt-4 show active" id="en_title" role="tabpanel" aria-labelledby="en_title-tab">
                        <div class="mb-3 row">
                            <label for="{{ $app_version_create }}_en_title" class="col-sm-5 col-form-label">{{ __( 'app_version.notes' ) }} ( English )</label>
                            <div class="col-sm-7">
                                <textarea type="text" class="form-control" id="{{ $app_version_create }}_en_notes" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $app_version_create }}_en_text" class="col-sm-5 col-form-label">{{ __( 'app_version.desc' ) }} ( English )</label>
                            <div class="col-sm-7">
                                <textarea class="form-control"  style="min-height: 80px;" id="{{ $app_version_create }}_en_desc"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade pt-4" id="zh_title" role="tabpanel" aria-labelledby="zh_title-tab">
                        <div class="mb-3 row">
                            <label for="{{ $app_version_create }}_zh_title" class="col-sm-5 col-form-label">{{ __( 'app_version.notes' ) }} ( 中文 )</label>
                            <div class="col-sm-7">
                                <textarea type="text" class="form-control" id="{{ $app_version_create }}_zh_notes" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $app_version_create }}_zh_text" class="col-sm-5 col-form-label">{{ __( 'app_version.desc' ) }} ( 中文 )</label>
                            <div class="col-sm-7">
                                <textarea class="form-control"  style="min-height: 80px;" id="{{ $app_version_create }}_zh_desc"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-end">
                    <button id="{{ $app_version_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $app_version_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
window.ckeupload_path = '{{ route( 'admin.app_version.ckeUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
window.cke_element = [ 'app_version_create_en_notes', 'app_version_create_zh_notes' ];
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multi.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $app_version_create }}',
            template_reference = $( `
            <div class="d-flex justify-content-between align-items-center w-100">
                <input type="text" class="form-control {{ $app_version_create }}_reference_id" name="{{ $app_version_create }}_reference_id">
                <span class="close btn_reduce" type="button"><em class="icon ni ni-cross"></em></span>
            </div>
        ` );

        $( document ).on( 'click', '.btn_reduce', function() {
            $( this ).parent().remove();
        } );

        $( document ).on( 'focus' , '.app_version_create_reference_id', function() {
            $( '.app_version_create_reference_id' ).removeClass( 'is-invalid' ).parent().parent().nextAll( 'div.invalid-feedback' ).text( '' );
        } );

        $( '.btn_add' ).click( function() {
            template = template_reference.clone();
            $( fc + '_reference_id' ).append( template );
        } );

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.app_version.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let refenrence_id = [];

            $.each( $( fc + '_reference_id' ).find( 'input' ), function( key, value ) {
                if ( $( value ).val() ) {
                    refenrence_id.push( $( value ).val() );
                }
            } );

            let formData = new FormData();
            formData.append( 'customer_member_id', $( fc + '_customer_member_id' ).val() );
            formData.append( 'name', $( fc + '_name' ).val() );
            formData.append( 'quantity', $( fc + '_quantity' ).val() );
            if( refenrence_id.length == 0 ) {
                
            } else {
                formData.append( 'reference_id', JSON.stringify( refenrence_id ) );
            }
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.app_version.createAppVersion' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.app_version.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            if( key == 'reference_id' ) {
                                $( fc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                                $( fc + '_' + key ).find( '.app_version_create_reference_id' ).addClass( 'is-invalid' )
                            }else {
                                $( fc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                            }
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );
        
    } );
</script>