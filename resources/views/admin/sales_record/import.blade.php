<?php $sales_record_create = 'sales_record_create'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.sales_records' ) ) ] ) }}</h3>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-inner">
        <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
        <div class="mb-3">
            <label>{{ __( 'template.sales_records' ) }}</label>
            <div class="dropzone mb-3" id="{{ $sales_record_create }}_image" style="min-height: 0px;">
                <div class="dz-message needsclick">
                    <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                </div>
            </div>
            <div class="invalid-feedback"></div>
        </div>
        
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

    let fc = '#{{ $sales_record_create }}', fileID = '';

    $(fc + '_cancel').click(() => window.location.href = '{{ route('admin.module_parent.banner.index') }}');

    // ✅ Prevent Dropzone from being attached multiple times
    if (Dropzone.instances.length > 0) {
        Dropzone.instances.forEach(dz => dz.destroy()); // Destroy existing Dropzones before initializing
    }

    // ✅ Ensure Dropzone is initialized once
    if (!$(fc + '_image').hasClass("dz-clickable")) {
        Dropzone.autoDiscover = false;
        let myDropzone = new Dropzone(fc + '_image', {
            url: "{{ route('admin.sales_record.importSalesRecords') }}",
            maxFiles: 1,
            acceptedFiles: ".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            addRemoveLinks: true,
            params: {
                _token: "{{ csrf_token() }}"
            },
            success: function(file, response) {
                if (response.status == 200) {
                    myDropzone.removeFile(file);
                }
            }
        });
    }

});

</script>
