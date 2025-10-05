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
                <div class="mb-3 row">
                    <label for="{{ $playlist_create }}_referral" class="col-sm-5 col-form-label">{{ __( 'playlist.referral' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-control select2" id="{{ $playlist_create }}_referral" data-placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'template.playlists' ) ] ) }}"></select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $playlist_create }}_date_of_birth" class="col-sm-5 col-form-label">{{ __( 'playlist.date_of_birth' ) }}</label>
                    <div class="col-sm-7">
                        <input type="date" class="form-control" id="{{ $playlist_create }}_date_of_birth">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @if( 1 == 2 )
                <div class="mb-3 row">
                    <label for="{{ $playlist_create }}_account_type" class="col-sm-5 col-form-label">{{ __( 'playlist.account_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $playlist_create }}_account_type" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'playlist.account_type' ) ] ) }}</option>
                            <option value="1">{{ __( 'playlist.personal' ) }}</option>
                            <option value="2">{{ __( 'playlist.company' ) }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $playlist_create }}_playlistname" class="col-sm-5 col-form-label">{{ __( 'playlist.playlistname' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $playlist_create }}_playlistname">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @endif
                <div class="mb-3 row">
                    <label for="{{ $playlist_create }}_email" class="col-sm-5 col-form-label">{{ __( 'playlist.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $playlist_create }}_email">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $playlist_create }}_first_name" class="col-sm-5 col-form-label">{{ __( 'playlist.first_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $playlist_create }}_first_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $playlist_create }}_last_name" class="col-sm-5 col-form-label">{{ __( 'playlist.last_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $playlist_create }}_last_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $playlist_create }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'playlist.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <div class="input-group">
                            <select class="form-select flex-shrink-0" id="{{ $playlist_create }}_calling_code" style="max-width: 100px;">
                                <option value="+60" selected>+60</option>
                                <option value="+65">+65</option>
                            </select>
                            <input type="text" class="form-control" id="{{ $playlist_create }}_phone_number">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>                    
                </div>                
                
                <div class="mb-3 row">
                    <label for="{{ $playlist_create }}_password" class="col-sm-5 col-form-label">{{ __( 'playlist.password' ) }}</label>
                    <div class="col-sm-7">
                        <input type="password" class="form-control" id="{{ $playlist_create }}_password" autocomplete="new-password">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row d-none">
                    <label for="{{ $playlist_create }}_address_1" class="col-sm-5 col-form-label">{{ __( 'customer.address_1' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $playlist_create }}_address_1" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $playlist_create }}_address_2" class="col-sm-5 col-form-label">{{ __( 'customer.address_2' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $playlist_create }}_address_2" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $playlist_create }}_city" class="col-sm-5 col-form-label">{{ __( 'customer.city' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $playlist_create }}_city" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $playlist_create }}_state" class="col-sm-5 col-form-label">{{ __( 'customer.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $playlist_create }}_state" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'customer.state' ) ] ) }}</option>
                            <option value="Johor">Johor</option>
                            <option value="Kedah">Kedah</option>
                            <option value="Kelantan">Kelantan</option>
                            <option value="Malacca">Malacca</option>
                            <option value="Negeri Sembilan">Negeri Sembilan</option>
                            <option value="Pahang">Pahang</option>
                            <option value="Penang">Penang</option>
                            <option value="Perlis">Perlis</option>
                            <option value="Sabah">Sabah</option>
                            <option value="Sarawak">Sarawak</option>
                            <option value="Selangor">Selangor</option>
                            <option value="Terengganu">Terengganu</option>
                            <option value="Kuala Lumpur">Kuala Lumpur</option>
                            <option value="Labuan">Labuan</option>
                            <option value="Putrajaya">Putrajaya</option>
                            <option value="Perak">Perak</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none">
                    <label for="{{ $playlist_create }}_postcode" class="col-sm-5 col-form-label">{{ __( 'customer.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $playlist_create }}_postcode" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
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
                fileID = '';

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
            formData.append( 'category_id', $( dc + '_category' ).val() );
            formData.append( 'image', fileID );
            
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
                url: '{{ route( 'admin.category.allCagetories' ) }}',
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

    } );
</script>