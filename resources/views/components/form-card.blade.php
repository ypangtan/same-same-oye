<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ $title }}</h5>

                {{ $slot }}

                <div class="text-end mt-4">
                    <button type="button" class="btn btn-outline-secondary" id="{{ $id }}_cancel">
                        {{ __( 'template.cancel' ) }}
                    </button>
                    &nbsp;
                    <button type="button" class="btn btn-primary" id="{{ $id }}_submit">
                        {{ __( 'template.save_changes' ) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>