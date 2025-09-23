<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Services\{
    ModuleService,
};

use Spatie\Permission\Models\{
    Permission,
};

use App\Models\{
    Module,
    PresetPermission,
};

use Helper;

class ModuleController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.modules' );
        $this->data['content'] = 'admin.module.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.modules' ),
                'class' => 'active',
            ],
        ];

        $moduleDelete = \Helper::unusedModule();
        $actionDelete = \Helper::unusedAction();
        $deleteActionMap = [];
        foreach ( $actionDelete as $item ) {
            $deleteActionMap[$item['name']][] = $item['action'];
        }

        $additionPermission = \Helper::additionPermission();
        $additionPermissionMap = [];
        foreach ( $additionPermission as $item ) {
            $additionPermissionMap[$item['name']][] = $item['action'];
        }

        $defaultActions = ['add', 'edit', 'view', 'delete'];

        foreach ( Route::getRoutes() as $route ) {
           $routeName = $route->getName();
            if ( str_contains( $route->getName(), 'admin.module_parent.' ) ) {

                $routeName = str_replace( 'admin.module_parent.', '', $routeName );
                $routeName = str_replace( '.index', '', $routeName );
                $moduleName = \Str::plural( $routeName );

                if ( in_array( $moduleName, $moduleDelete ) ) {
                    $modules = Module::where( 'name', $moduleName )->get();
                    foreach ( $modules as $module ) {
                        $module->delete();
                    }
                    continue;
                }

                $module = Module::firstOrCreate( [
                    'name' => $moduleName,
                    'guard_name' => 'admin',
                ] );
                
                if ( $module ) {
                    $notAllowedActions = $deleteActionMap[$moduleName] ?? [];

                    foreach ( $defaultActions as $action ) {
                        if ( !in_array( $action, $notAllowedActions ) ) {
                            PresetPermission::firstOrCreate([
                                'module_id' => $module->id,
                                'action' => $action,
                            ]);
                        } else {
                            $permission = PresetPermission::where( 'module_id', $module->id )
                                ->where('action', $action)
                                ->first();

                            if( $permission ) {
                                $permission->delete();
                            }
                        }
                    }

                    if( isset( $additionPermissionMap[$moduleName] ) ) {
                        foreach( $additionPermissionMap[$moduleName] as $additionAction ) {
                            PresetPermission::firstOrCreate([
                                'module_id' => $module->id,
                                'action' => $additionAction,
                            ]);
                        }
                    }
                    
                }
            }
        }

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();   

        return view( 'admin.main' )->with( $this->data );
    }

    public function allModules( Request $request ) {

        return ModuleService::allModules( $request );
    }
}
