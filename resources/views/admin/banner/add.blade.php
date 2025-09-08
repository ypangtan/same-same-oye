<?php 
    $banners = $data['banners'];
?>

<style>
    .sortable-placeholder {
        background: #f8f9fa;
        border: 2px dashed #ccc;
        height: 100px;
    }
    .banner-img {
        width: 80%; /* ✅ Increased size */
        height: 120px;
        object-fit: contain; /* ✅ Ensures it maintains aspect ratio */
    }
    .list-group-item {
        display: flex;
        justify-content: center; /* ✅ Centers content */
        align-items: center;
        text-align: center;
    }

    #banner-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        padding: 0;
    }
    #banner-list .list-group-item {
        width: 100%;
        text-align: center;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
    }
    .banner-img {
        width: 100%;
        max-width: 150px;
        object-fit: cover;
    }

    #banner-list li:hover {
        background: #e9ecef;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    }

    .sortable-placeholder {
        background: #dee2e6;
        border: 2px dashed #6c757d;
        height: 130px;
        border-radius: 8px;
        margin-bottom: 10px;
    }

</style>

<?php $banner_create = 'banner_create'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.banners' ) ) ] ) }}</h3>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-inner">
        <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
        @can( 'add banners' )
        <div class="mb-3">
            <label>{{ __( 'banner.image' ) }}</label>
            <div class="dropzone mb-3" id="{{ $banner_create }}_image" style="min-height: 0px;">
                <div class="dz-message needsclick">
                    <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                </div>
            </div>
            <div class="invalid-feedback"></div>
        </div>
        @endcan
        <ul id="banner-list" class="list-group">
            @foreach($banners as $banner)
                <li class="list-group-item d-flex flex-column align-items-center justify-content-center position-relative" data-id="{{ $banner->id }}">
                    <img src="{{ asset('storage/' . $banner->image) }}" class="banner-img rounded">
        
                    <!-- Dropdown -->
                    @can( 'edit banners' )
                    <div class="dropdown mt-2">
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <em class="icon ni ni-more-h"></em>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <button class="dropdown-item edit-banner" data-id="{{ $banner->id }}">Edit</button>
                            </li>
                            <li>
                                <button class="dropdown-item text-danger delete-banner" data-id="{{ $banner->id }}">Delete</button>
                            </li>
                        </ul>
                    </div>
                    @endcan
                </li>
            @endforeach
        </ul>
        
    </div>
</div>

<!-- jQuery (Make sure jQuery is included before jQuery UI) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>

<!-- jQuery UI CSS (Optional, for better styling) -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

    let fc = '#{{ $banner_create }}', fileID = '';

    $(fc + '_cancel').click(() => window.location.href = '{{ route('admin.module_parent.banner.index') }}');

    // ✅ Prevent Dropzone from being attached multiple times
    if (Dropzone.instances.length > 0) {
        Dropzone.instances.forEach(dz => dz.destroy()); // Destroy existing Dropzones before initializing
    }

    // ✅ Ensure Dropzone is initialized once
    if (!$(fc + '_image').hasClass("dz-clickable")) {
        Dropzone.autoDiscover = false;
        let myDropzone = new Dropzone(fc + '_image', {
            url: "{{ route('admin.banner.createBanner') }}",
            maxFiles: 1,
            acceptedFiles: "image/jpg,image/jpeg,image/png",
            addRemoveLinks: true,
            params: {
                _token: "{{ csrf_token() }}"
            },
            success: function(file, response) {
                if (response.status == 200) {
                    let newBanner = $(`
                        <li class="list-group-item d-flex flex-column align-items-center justify-content-center position-relative" data-id="${response.data.id}${response.data.id}">
                            <img src="${response.data.url}" class="banner-img rounded">
                
                            <!-- Dropdown -->
                            <div class="dropdown mt-2">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <em class="icon ni ni-more-h"></em>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button class="dropdown-item edit-banner" data-id="${response.data.id}">Edit</button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger delete-banner" data-id="${response.data.id}">Delete</button>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    `);
                    $("#banner-list").append(newBanner);
                
                    // ✅ Remove file preview to allow new uploads
                    myDropzone.removeFile(file);

                }
            }
        });
    }

    // ✅ Initialize Sortable.js
    let sortableList = new Sortable(document.getElementById('banner-list'), {
        animation: 200, // Smooth transition effect
        handle: ".banner-img", // Users can drag by clicking on the image
        ghostClass: 'sortable-placeholder', // Placeholder class when dragging
        handle: ".list-group-item", // Only drag using the list items
        ghostClass: "sortable-placeholder", // CSS class for dragged element
        onEnd: function(evt) {
            let sortedIDs = [];
            $("#banner-list li").each(function() {
                if( $(this).data("id") ){
                    sortedIDs.push($(this).data("id"));
                }
            });

            // ✅ Send updated order to backend
            $.ajax({
                url: "{{ route('admin.banner.updateOrder') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    order: sortedIDs
                },
                success: function(response) {
                    console.log("Banner order updated successfully!");
                },
                error: function(error) {
                    console.error("Error updating banner order", error);
                }
            });
        }
    });

    $( document ).on( 'click', '.edit-banner', function() {
        window.location.href = '{{ route( 'admin.banner.edit' ) }}?id=' + $( this ).data( 'id' );
    } );

    // ✅ Delete Banner
    $(document).on("click", ".delete-banner", function() {
        let bannerId = $(this).data("id");
        let bannerItem = $(this).closest(".list-group-item"); // Ensure correct targeting

        $( 'body' ).loading( {
            message: '{{ __( 'template.loading' ) }}'
        } );

        $.post('{{ route("admin.banner.updateBannerStatus") }}', {
            _token: '{{ csrf_token() }}',
            id: bannerId
        }).done(function(response) {
            $( 'body' ).loading( 'stop' );

            bannerItem.fadeOut(300, function() {
                $(this).remove();
            });
        }).fail(function() {
            $( 'body' ).loading( 'stop' );

            alert("Error occurred. Please check your connection.");
        });
    });


});

</script>
