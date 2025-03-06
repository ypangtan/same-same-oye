<?php
$vending_machine_create = 'vending_machine_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.vending_machines' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_create }}_title" class="col-sm-5 col-form-label">{{ __( 'vending_machine.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_create }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_create }}_code" class="col-sm-5 col-form-label">{{ __( 'vending_machine.code' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_create }}_code">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_create }}_description" class="col-sm-5 col-form-label">{{ __( 'vending_machine.description' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $vending_machine_create }}_description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_create }}_opening_hour" class="col-sm-5 col-form-label">{{ __( 'vending_machine.opening_hour' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_create }}_opening_hour">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_create }}_closing_hour" class="col-sm-5 col-form-label">{{ __( 'vending_machine.closing_hour' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_create }}_closing_hour">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_create }}_navigation_links" class="col-sm-5 col-form-label">{{ __( 'vending_machine.navigation_links' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_create }}_navigation_links">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label>{{ __( 'vending_machine.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $vending_machine_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <h5 class="card-title mb-4">{{ __( 'template.location_info' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $vending_machine_create }}_latitude" class="col-sm-5 col-form-label">{{ __( 'vending_machine.latitude' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_create }}_latitude" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_create }}_longitude" class="col-sm-5 col-form-label">{{ __( 'vending_machine.longitude' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_create }}_longitude" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_create }}_address_1" class="col-sm-5 col-form-label">{{ __( 'vending_machine.address_1' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $vending_machine_create }}_address_1" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_create }}_address_2" class="col-sm-5 col-form-label">{{ __( 'vending_machine.address_2' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $vending_machine_create }}_address_2" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_create }}_city" class="col-sm-5 col-form-label">{{ __( 'vending_machine.city' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_create }}_city" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vending_machine_create }}_state" class="col-sm-5 col-form-label">{{ __( 'vending_machine.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vending_machine_create }}_state" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'vending_machine.state' ) ] ) }}</option>
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
                    <label for="{{ $vending_machine_create }}_postcode" class="col-sm-5 col-form-label">{{ __( 'vending_machine.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vending_machine_create }}_postcode" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $vending_machine_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $vending_machine_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {
        
        let fc = '#{{ $vending_machine_create }}',
                fileID = '',
                order = [];

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.vending_machine.index' ) }}';
        } );

        $( fc + '_opening_hour' ).flatpickr( {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
        } );

        $( fc + '_closing_hour' ).flatpickr( {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'title', $( fc + '_title' ).val() );
            formData.append( 'code', $( fc + '_code' ).val() );
            formData.append( 'description', $( fc + '_description' ).val() );
            formData.append( 'latitude', $( fc + '_latitude' ).val() );
            formData.append( 'longitude', $( fc + '_longitude' ).val() );
            formData.append( 'address_1', $( fc + '_address_1' ).val() );
            formData.append( 'address_2', $( fc + '_address_2' ).val() );
            formData.append( 'city', $( fc + '_city' ).val() );
            formData.append( 'state', $( fc + '_state' ).val() );
            formData.append( 'postcode', $( fc + '_postcode' ).val() );
            formData.append( 'closing_hour', $( fc + '_closing_hour' ).val() );
            formData.append( 'opening_hour', $( fc + '_opening_hour' ).val() );
            formData.append( 'navigation_links', $( fc + '_navigation_links' ).val() );
            formData.append( 'image', fileId );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.vending_machine.createVendingMachine' ) }}',
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
                            $( fc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone(fc + '_image', { 
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 10,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            init: function () {
                let dropzoneInstance = this;

                // Enable Sorting with Sortable.js
                new Sortable(document.getElementById("vending_machine_create_image"), {
                    draggable: ".dz-preview",
                    animation: 150, // Smooth transition
                    onEnd: function () {
                        updateImageOrder(dropzoneInstance);
                    }
                });

                // Update Order on File Add or Remove
                this.on("addedfile", function () {
                    updateImageOrder(dropzoneInstance);
                });

                this.on("removedfile", function () {
                    updateImageOrder(dropzoneInstance);
                });
            },
            removedfile: function(file) {
                var idToRemove = file.previewElement.id;
                var idArrays = fileID.split(/\s*,\s*/);
                var indexToRemove = idArrays.indexOf(idToRemove.toString());
                if (indexToRemove !== -1) {
                    idArrays.splice(indexToRemove, 1);
                }
                fileID = idArrays.join(', ');

                file.previewElement.remove();
                updateImageOrder(this); // Update order after removal
            },
            success: function(file, response) {
                if (response.status == 200) {
                    if (fileID !== '') {
                        fileID += ','; // Add a comma if fileID is not empty
                    }
                    fileID += response.data.id;

                    file.previewElement.id = response.data.id;
                }
            }
        });

        // Function to Update Image Order Using fileId
        function updateImageOrder(dropzoneInstance) {
            document.querySelectorAll("#vending_machine_create_image .dz-preview").forEach((element, index) => {
                let file = dropzoneInstance.files.find(f => f.previewElement === element);
                if (file && file.previewElement.id) {
                    order.push({ id: file.previewElement.id, order: index + 1 });
                }


            });

            console.log("Updated Order:", order);
        }


    } );
</script>