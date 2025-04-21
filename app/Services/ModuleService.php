<?php

namespace App\Services;

use App\Models\{
    Module,
};

class ModuleService
{
    public static function allModules( $request ) {
        
        $module = Module::select( 'modules.*' );

        $filterObject = self::filter( $request, $module );
        $module = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $module->orderBy( 'created_at', $dir );
                    break;
                case 2:
                    $module->orderBy( 'name', $dir );
                    break;
            }
        }

        $moduleCount = $module->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;
        
        $modules = $module->skip( $offset )->take( $limit )->get();

        $totalRecord = Module::count();

        $data = [
            'modules' => $modules,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $moduleCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
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

                $model->whereBetween( 'modules.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );                
            } else {

                $dates = explode( '-', $request->created_date );
    
                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'modules.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }

            $filter = true;
        }
        
        if ( !empty( $name = $request->module_name ) ) {
            $model->where( 'name', 'LIKE', "%{$name}%" );
            $filter = true;
        }

        if ( !empty( $guardName = $request->guard_name ) ) {
            $model->where( 'guard_name', $guardName );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }
}