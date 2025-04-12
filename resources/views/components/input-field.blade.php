<div class="mb-3 row">
    <label for="{{ $label }}" class="col-sm-5 col-form-label">{{ $label }}</label>
    <div class="col-sm-7">
        <input type="{{ $type ?? 'text' }}" class="form-control" id="{{ $id }}">
        <div class="invalid-feedback"></div>
    </div>
</div>