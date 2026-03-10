<?php 
    $website_website_banners = $data['website_website_banners'];
?>

<style>
    .sortable-placeholder {
        background: #f8f9fa;
        border: 2px dashed #ccc;
        height: 100px;
    }
    .website_website_banner-img {
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

    #website_website_banner-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        padding: 0;
    }
    #website_website_banner-list .list-group-item {
        width: 100%;
        text-align: center;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
    }
    .website_website_banner-img {
        width: 100%;
        max-width: 150px;
        object-fit: cover;
    }

    #website_website_banner-list li:hover {
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

<?php $website_website_banner_create = 'website_website_banner_create'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.website_website_banners' ) ) ] ) }}</h3>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-inner">
        <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
        @can( 'add website_website_banners' )
        <div class="mb-3">
            <label>{{ __( 'website_website_banner.image' ) }}</label>
            <div class="dropzone mb-3" id="{{ $website_website_banner_create }}_image" style="min-height: 0px;">
                <div class="dz-message needsclick">
                    <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                </div>
            </div>
            <div class="invalid-feedback"></div>
        </div>
        @endcan
        <ul id="website_website_banner-list" class="list-group">
            @foreach($website_website_banners as $website_website_banner)
                <li class="list-group-item d-flex flex-column align-items-center justify-content-center position-relative" data-id="{{ $website_website_banner->id }}">
                    <img src="{{ $website_website_banner->image_path }}" class="website_website_banner-img rounded">
                    <div class=" mt-2">
                        <label>{{ __('website_website_banner.website_website_banner_url') }}</label>
                        <input type="url" class="website_website_banner_url form-control" value="{{ $website_website_banner->url ?? '' }}" data-id="{{ $website_website_banner->id }}" placeholder="https://example.com"/>
                    </div>
                    <!-- Dropdown -->
                    @can( 'edit website_website_banners' )
                    <div class="dropdown mt-2">
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <em class="icon ni ni-more-h"></em>
                        </button>
                        <ul class="dropdown-menu">
                            {{-- <li>
                                <button class="dropdown-item edit-website_website_banner" data-id="{{ $website_website_banner->id }}">Edit</button>
                            </li> --}}
                            <li>
                                <button class="dropdown-item text-danger delete-website_website_banner" data-id="{{ $website_website_banner->id }}">Delete</button>
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

    let fc = '#{{ $website_website_banner_create }}', fileID = '';
    let website_website_bannerUrlTimer = {};

    $(fc + '_cancel').click(() => window.location.href = '{{ route('admin.module_parent.website_website_banner.index') }}');

    // ✅ Prevent Dropzone from being attached multiple times
    if (Dropzone.instances.length > 0) {
        Dropzone.instances.forEach(dz => dz.destroy()); // Destroy existing Dropzones before initializing
    }

    // ✅ Ensure Dropzone is initialized once
    if (!$(fc + '_image').hasClass("dz-clickable")) {
        Dropzone.autoDiscover = false;
        let myDropzone = new Dropzone(fc + '_image', {
            url: "{{ route('admin.website_website_banner.createWebsiteBanner') }}",
            maxFiles: 1,
            acceptedFiles: 'image/*,.heic,.heif,.webp',
            addRemoveLinks: true,
            params: {
                _token: "{{ csrf_token() }}"
            },
            success: function(file, response) {
                if (response.status == 200) {
                    let newWebsiteBanner = $(`
                        <li class="list-group-item d-flex flex-column align-items-center justify-content-center position-relative" data-id="${response.data.id}${response.data.id}">
                            <img src="${response.data.website_website_banner_url}" class="website_website_banner-img rounded">
                
                            <div class=" mt-2">
                                <label>{{ __('website_website_banner.website_website_banner_url') }}</label>
                                <input type="url" class="website_website_banner_url form-control" value="${response.data.url ?? ''}" data-id="${response.data.id}" placeholder="https://example.com"/>
                            </div>
                            <!-- Dropdown -->
                            <div class="dropdown mt-2">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <em class="icon ni ni-more-h"></em>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button class="dropdown-item text-danger delete-website_website_banner" data-id="${response.data.id}">Delete</button>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    `);
                    $("#website_website_banner-list").append(newWebsiteBanner);
                
                    // ✅ Remove file preview to allow new uploads
                    myDropzone.removeFile(file);

                }
            }
        });
    }


    $(document).on('keydown keyup', '.website_website_banner_url', function() {
        let input = $(this);
        let website_website_bannerId = input.data('id');
        let newUrl = input.val();

        // Clear any existing timer for this website_website_banner
        clearTimeout(website_website_bannerUrlTimer[website_website_bannerId]);

        // Start a new debounce timer (500ms after last key event)
        website_website_bannerUrlTimer[website_website_bannerId] = setTimeout(() => {
            // Skip if input is empty or unchanged (optional optimization)
            // if (!newUrl.trim()) return;

            let formData = new FormData();
            formData.append('id', website_website_bannerId);
            formData.append('url', newUrl ?? '');
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: '{{ route("admin.website_website_banner.updateWebsiteBannerUrl") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('✅ WebsiteBanner URL updated:', response);
                    input.css({
                        'border-color': '#28a745',
                        'box-shadow': '0 0 4px #28a745'
                    });
                    setTimeout(() => {
                        input.css('border-color', '#dbdfea');
                        input.css('box-shadow', '');
                    }, 600);
                },
                error: function(xhr) {
                    console.error('❌ Error updating website_website_banner URL:', xhr.responseText);
                    input.css({
                        'border-color': '#f8d7da',
                        'box-shadow': '0 0 4px #f8d7da'
                    });
                }
            });
        }, 500); // 500ms debounce after last key event
    });

    // ✅ Initialize Sortable.js
    let sortableList = new Sortable(document.getElementById('website_website_banner-list'), {
        animation: 200, // Smooth transition effect
        handle: ".website_website_banner-img", // Users can drag by clicking on the image
        ghostClass: 'sortable-placeholder', // Placeholder class when dragging
        handle: ".list-group-item", // Only drag using the list items
        ghostClass: "sortable-placeholder", // CSS class for dragged element
        onEnd: function(evt) {
            let sortedIDs = [];
            $("#website_website_banner-list li").each(function() {
                if( $(this).data("id") ){
                    sortedIDs.push($(this).data("id"));
                }
            });

            // ✅ Send updated order to backend
            $.ajax({
                url: "{{ route('admin.website_website_banner.updateOrder') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    order: sortedIDs
                },
                success: function(response) {
                    console.log("WebsiteBanner order updated successfully!");
                },
                error: function(error) {
                    console.error("Error updating website_website_banner order", error);
                }
            });
        }
    });

    $( document ).on( 'click', '.edit-website_website_banner', function() {
        window.location.href = '{{ route( 'admin.website_website_banner.edit' ) }}?id=' + $( this ).data( 'id' );
    } );

    // ✅ Delete WebsiteBanner
    $(document).on("click", ".delete-website_website_banner", function() {
        let website_website_bannerId = $(this).data("id");
        let website_website_bannerItem = $(this).closest(".list-group-item"); // Ensure correct targeting

        $( 'body' ).loading( {
            message: '{{ __( 'template.loading' ) }}'
        } );

        $.post('{{ route("admin.website_website_banner.updateWebsiteBannerStatus") }}', {
            _token: '{{ csrf_token() }}',
            id: website_website_bannerId
        }).done(function(response) {
            $( 'body' ).loading( 'stop' );

            website_website_bannerItem.fadeOut(300, function() {
                $(this).remove();
            });
        }).fail(function() {
            $( 'body' ).loading( 'stop' );

            alert("Error occurred. Please check your connection.");
        });
    });


});

</script>
