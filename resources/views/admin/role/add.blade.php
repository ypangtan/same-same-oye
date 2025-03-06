<?php
$role_create = 'role_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.roles' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $role_create }}_role_name" class="col-sm-5 col-form-label">{{ __( 'role.role_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $role_create }}_role_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $role_create }}_guard_name" class="col-sm-5 col-form-label">{{ __( 'role.guard_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $role_create }}_guard_name" value="admin" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <hr>
                <div>
                <?php
                $modules = \App\Models\Module::with( [ 'presetPermissions' ] )->orderBy( 'guard_name', 'ASC' )->orderBy( 'name', 'ASC' )->get();
                ?>
                @foreach ( $modules as $module )
                <div class="mb-4 role_create-modules-section" data-module="{{ $module->name . '|' . $module->guard_name }}">
                    <h5>{{ __( 'role.module_title', [ 'module' => __( 'module.' . $module->name ) ] ) }} ( {{ __( 'role.' . $module->guard_name ) }} )</h4>
                    @foreach( $module->presetPermissions as $preset )
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="{{ 'role_create_' . $preset->action . '_' . $module->name . '_' . $module->guard_name }}" value="{{ $preset->action }}">
                        <label class="form-check-label" for="{{ 'role_create_' . $preset->action . '_' . $module->name . '_' . $module->guard_name }}">{{ __( 'role.action_module', [ 'action' => __( 'role.' . $preset->action ), 'module' => __( 'module.' . $module->name ) ] ) }}</label>
                    </div>
                    @endforeach
                    @if ( count( $module->presetPermissions ) == 0 )
                    <p class="text-center">{{ __( 'role.no_action_found' ) }}</p>
                    @endif
                </div>
                @endforeach
                @if ( count( $modules ) == 0 )
                <p class="text-center">{{ __( 'role.no_module_found' ) }}</p>
                @endif
                </div>
                <div class="text-end">
                    <button id="{{ $role_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $role_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let id = '{{ request( 'id' ) }}',
            rc = '#{{ $role_create }}';

        $( rc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.role.index' ) }}';
        } );

        $( rc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let modules = {};

            $( '.role_create-modules-section' ).each( function() {

                let temp = [];

                $( this ).find( '.form-check-input' ).each( function() {
                    
                    if ( $( this ).prop( 'checked' ) ) {
                        temp.push( $( this ).val() );
                    }

                } );

                modules[ $( this ).data( 'module' ) ] = temp;

            } );

            $.ajax( {
                url: '{{ route( 'admin.role.createRole' ) }}',
                type: 'POST',
                data: {
                    'role_name': $( rc + '_role_name' ).val().trim().replace(/ /g,"_").toLowerCase(),
                    'guard_name': $( rc + '_guard_name' ).val().trim().toLowerCase(),
                    modules,
                    '_token': '{{ csrf_token() }}',
                },
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.role.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( rc + '_' + key ).addClass( 'is-invalid' ).next().text( value );
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