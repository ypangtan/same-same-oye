<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use Spatie\Permission\Models\{
    Permission,
    Role,
};

use App\Models\{
    Module,
    Role as RoleModel,
};

use Helper;

use Carbon\Carbon;

class RoleService
{
    public static function allRoles( $request ) {

        $role = RoleModel::select( 'roles.*' );

        $filterObject = self::filter( $request, $role );
        $role = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch( $request->input( 'order.0.column' ) ) {
                case 1:
                    $role->orderBy( 'created_at', $dir );
                    break;
                case 2:
                    $role->orderBy( 'name', $dir );
                    break;
            }
        }

        $roleCount = $role->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;
        
        $roles = $role->skip( $offset )->take( $limit )->get();

        if ( $roles ) {
            $roles->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = RoleModel::count();

        $data = array(
            'roles' => $roles,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $roleCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        );

        return $data;
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if (  !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'roles.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );                
            } else {

                $dates = explode( '-', $request->created_date );
    
                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'roles.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }

            $filter = true;
        }

        if ( !empty( $request->role_name ) ) {
            $model->where( 'name', $request->role_name );
            $filter = true;
        }

        if ( !empty( $request->guard_name ) ) {
            $model->where( 'guard_name', $request->guard_name );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneRole( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $permission = \DB::table( 'role_has_permissions' )
            ->leftJoin( 'permissions', 'role_has_permissions.permission_id', '=', 'permissions.id' )
            ->where( 'role_id', $request->id )
            ->get();

        return response()->json( [ 'role' => RoleModel::find( $request->id ), 'permissions' => $permission ] );
    }

    public static function createRole( $request ) {

        $validator = Validator::make( $request->all(), [
            'role_name' => 'required|unique:roles,name',
            'guard_name' => 'required',
        ] );

        $attributeName = [
            'role_name' => __( 'role.role_name' ),
            'guard_name' => __( 'role.guard_name' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        $createRole = Role::create( [ 
            'name' => $request->role_name,
            'guard_name' => $request->guard_name,
        ] );

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        
        $permissions = [];

        if ( !empty( $request->modules ) ) {
            foreach ( $request->modules as $key => $module ) {
                $key = explode( '|', $key );
                if ( $key[1] != $createRole->guard_name ) {
                    continue;
                }

                $moduleObject = Module::where( 'name', $key[0] )->first();

                foreach ( $module as $action ) {
                    
                    $exist = Permission::where( 'name', $action . ' ' . $key[0] )->first();
                    if ( !$exist ) {
                        $createPermission = Permission::create( [
                            'name' => $action . ' ' . $key[0],
                            'guard_name' => $key[1],
                        ] );
    
                        $updatePermission = Permission::find( $createPermission->id );
                        $updatePermission->module_id = $moduleObject->id;
                        $updatePermission->save();
                    }

                    array_push( $permissions, $action . ' ' . $key[0] );
                }
            }
        }

        $createRole->syncPermissions( $permissions );

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.roles' ) ) ] ),
        ] );
    }

    public static function updateRole( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $roleModel = RoleModel::find( $request->id );
        $role = Role::findByName( $roleModel->name, $roleModel->guard_name );

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [];
        
        if ( $request->modules ) {
            foreach ( $request->modules as $key => $module ) {
                $key = explode( '|', $key );
                if ( $key[1] != $roleModel->guard_name ) {
                    continue;
                }

                $moduleObject = Module::where( 'name', $key[0] )->first();

                foreach ( $module as $action ) {

                    $exist = Permission::where( 'name', $action . ' ' . $key[0] )->first();
                    if ( !$exist ) {
                        $createPermission = Permission::create( [
                            'name' => $action . ' ' . $key[0],
                            'guard_name' => $key[1],
                        ] );
    
                        $updatePermission = Permission::find( $createPermission->id );
                        $updatePermission->module_id = $moduleObject->id;
                        $updatePermission->save();
                    }

                    array_push( $permissions, $action . ' ' . $key[0] );
                }
            }
        }

        $role->syncPermissions( $permissions );

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.roles' ) ) ] ),
        ] );
    }
}