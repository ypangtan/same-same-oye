<?php
$vending_machine_edit = 'vending_machine_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.vending_machines' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_title" class="col-sm-5 col-form-label">{{ __( 'vending_machine.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_edit }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_code" class="col-sm-5 col-form-label">{{ __( 'vending_machine.code' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_edit }}_code">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_description" class="col-sm-5 col-form-label">{{ __( 'vending_machine.description' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $vending_machine_edit }}_description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_quick_description" class="col-sm-5 col-form-label">{{ __( 'vending_machine.quick_description' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $vending_machine_edit }}_quick_description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_opening_hour" class="col-sm-5 col-form-label">{{ __( 'vending_machine.opening_hour' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_edit }}_opening_hour">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_closing_hour" class="col-sm-5 col-form-label">{{ __( 'vending_machine.closing_hour' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_edit }}_closing_hour">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_navigation_links" class="col-sm-5 col-form-label">{{ __( 'vending_machine.navigation_links' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_edit }}_navigation_links">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label>{{ __( 'vending_machine.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $vending_machine_edit }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="mb-3">
                    <label>{{ __( 'vending_machine.gallery' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $vending_machine_edit }}_gallery" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <h5 class="card-title mb-4">{{ __( 'template.location_info' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_latitude" class="col-sm-5 col-form-label">{{ __( 'vending_machine.latitude' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_edit }}_latitude" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_longitude" class="col-sm-5 col-form-label">{{ __( 'vending_machine.longitude' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_edit }}_longitude" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_address_1" class="col-sm-5 col-form-label">{{ __( 'customer.address_1' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $vending_machine_edit }}_address_1" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_address_2" class="col-sm-5 col-form-label">{{ __( 'customer.address_2' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $vending_machine_edit }}_address_2" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_city" class="col-sm-5 col-form-label">{{ __( 'customer.city' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_edit }}_city" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_state" class="col-sm-5 col-form-label">{{ __( 'customer.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vending_machine_edit }}_state" >
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
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_edit }}_postcode" class="col-sm-5 col-form-label">{{ __( 'customer.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_edit }}_postcode" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $vending_machine_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $vending_machine_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $vending_machine_edit }}',
                fileID = '';
                fileID2 = '';

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.vending_machine.index' ) }}';
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'code', $( fe + '_code' ).val() );
            formData.append( 'title', $( fe + '_title' ).val() );
            formData.append( 'description', $( fe + '_description' ).val() );
            formData.append( 'quick_description', $( fe + '_quick_description' ).val() );
            
            formData.append( 'address_1', $( fe + '_address_1' ).val() );
            formData.append( 'address_2', $( fe + '_address_2' ).val() );
            formData.append( 'latitude', $( fe + '_latitude' ).val() );
            formData.append( 'longitude', $( fe + '_longitude' ).val() );
            formData.append( 'city', $( fe + '_city' ).val() );
            formData.append( 'state', $( fe + '_state' ).val() );
            formData.append( 'postcode', $( fe + '_postcode' ).val() );
            formData.append( 'closing_hour', $( fe + '_closing_hour' ).val() );
            formData.append( 'opening_hour', $( fe + '_opening_hour' ).val() );
            formData.append( 'navigation_links', $( fe + '_navigation_links' ).val() );
            formData.append( 'image', fileID );
            formData.append( 'gallery', fileID2 );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.vending_machine.updateVendingMachine' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.vending_machine.index' ) }}';
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

        getVendingMachine();
        Dropzone.autoDiscover = false;

        function getVendingMachine() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.vending_machine.oneVendingMachine' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    
                    $( fe + '_code' ).val( response.code );
                    $( fe + '_title' ).val( response.title );
                    $( fe + '_description' ).val( response.description );
                    $( fe + '_quick_description' ).val( response.quick_description );
                    
                    $( fe + '_address_1' ).val( response.address_1 );
                    $( fe + '_address_2' ).val( response.address_2 );
                    $( fe + '_latitude' ).val( response.latitude );
                    $( fe + '_longitude' ).val( response.longitude );
                    $( fe + '_city' ).val( response.city );
                    $( fe + '_state' ).val( response.state );
                    $( fe + '_postcode' ).val( response.postcode );
                    $( fe + '_navigation_links' ).val( response.navigation_links );

                    // Extracting only the time portion (HH:mm) from the timestamp
                    const openingTime = new Date(response.opening_hour).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    const closingTime = new Date(response.closing_hour).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                    // Initialize flatpickr for opening_hour
                    $(fe + '_opening_hour').flatpickr({
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        defaultDate: openingTime // Set the default time
                    });

                    // Initialize flatpickr for closing_hour
                    $(fe + '_closing_hour').flatpickr({
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        defaultDate: closingTime // Set the default time
                    });

                    const dropzone = new Dropzone( fe + '_image', {
                        url: '{{ route( 'admin.file.upload' ) }}',
                        maxFiles: 10,
                        acceptedFiles: 'image/jpg,image/jpeg,image/png',
                        addRemoveLinks: true,
                        init: function() {

                            let that = this;
                            console.log(response)
                            if ( response.image_path != 0 ) {
                                let myDropzone = that
                                    cat_id = '{{ request('id') }}',
                                    mockFile = { name: 'Default', size: 1024, accepted: true, id: cat_id };

                                myDropzone.files.push( mockFile );
                                myDropzone.displayExistingFile( mockFile, response.image_path );
                                $( myDropzone.files[myDropzone.files.length - 1].previewElement ).data( 'id', cat_id );
                            }
                        },
                        removedfile: function( file ) {
                            var idToRemove = file.id;

                            var idArrays = fileID.split(/\s*,\s*/);

                            var indexToRemove = idArrays.indexOf( idToRemove.toString() );
                            if (indexToRemove !== -1) {
                                idArrays.splice( indexToRemove, 1 );
                            }

                            fileID = idArrays.join( ', ' );

                            file.previewElement.remove();

                            removeThumb( idToRemove );

                        },
                        success: function( file, response ) {
                            if ( response.status == 200 )  {
                                if ( fileID !== '' ) {
                                    fileID += ','; // Add a comma if fileID is not empty
                                }
                                fileID += response.data.id;

                                file.previewElement.id = response.data.id;
                            }
                        }
                    } );

                    const dropzone2 = new Dropzone( fe + '_gallery', {
                        url: '{{ route( 'admin.file.upload' ) }}',
                        maxFiles: 10,
                        acceptedFiles: 'image/jpg,image/jpeg,image/png',
                        addRemoveLinks: true,
                        init: function() {

                            let that = this;
                            console.log(response)
                            let myDropzone = that

                            response.galleries.forEach(gallery => {
                                let mockFile = { 
                                    name: 'Default', 
                                    size: 1024, 
                                    accepted: true, 
                                    id: gallery.encrypted_id 
                                };

                                myDropzone.files.push(mockFile);
                                myDropzone.displayExistingFile(mockFile, gallery.image_path);
                                $(myDropzone.files[myDropzone.files.length - 1].previewElement).data('id', cat_id);
                            });
                        },
                        removedfile: function( file ) {
                            var idToRemove = file.id;

                            var idArrays = fileID.split(/\s*,\s*/);

                            var indexToRemove = idArrays.indexOf( idToRemove.toString() );
                            if (indexToRemove !== -1) {
                                idArrays.splice( indexToRemove, 1 );
                            }

                            fileID = idArrays.join( ', ' );

                            file.previewElement.remove();

                            removeGallery( idToRemove );

                        },
                        success: function( file, response ) {
                            if ( response.status == 200 )  {
                                if ( fileID !== '' ) {
                                    fileID += ','; // Add a comma if fileID is not empty
                                }
                                fileID += response.data.id;

                                file.previewElement.id = response.data.id;
                            }
                        }
                    } );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

        function removeThumb( gallery ) {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', gallery );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.vending_machine.removeVendingMachineThumbImage' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
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
        }

        function removeGallery( gallery ) {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', gallery );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.vending_machine.removeVendingMachineGalleryImage' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
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
        }

    } );
</script>