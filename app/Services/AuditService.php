<?php

namespace App\Services;

use App\Models\{
    ActivityLog,
};

use Helper;

use Carbon\Carbon;

class AuditService
{
    public static function allAudits( $request, $export = false ) {
        // test commit
        $audit = ActivityLog::select( 'activity_log.*', 'administrators.name AS admin_username' );
        $audit->leftJoin( 'administrators', 'activity_log.causer_id', '=', 'administrators.id' );
        $audit->where( 'causer_type', 'App\Models\Administrator' );

        $filterObject = self::filter( $request, $audit );
        $audit = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $audit->orderBy( 'created_at', $dir );
                    break;
            }
        }

        if ( $export == false ) {

            $auditCount = $audit->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;
            
            $audits = $audit->skip( $offset )->take( $limit )->get();

            $totalRecord = ActivityLog::where( 'causer_type', 'App\Models\Administrator' )->count();

            $data = [
                'audits' => $audits,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $auditCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );
        }
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'activity_log.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'activity_log.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;   
        }

        if ( !empty( $request->username ) ) {
            $model->where( 'username', $username );
            $filter = true;
        }
        
        if ( !empty( $moduleName = $request->module_name ) ) {
            $model->where( 'log_name', 'LIKE', "%{$moduleName}%" );
            $filter = true;
        }

        if ( !empty( $actionPerformed = $request->action_performed ) ) {
            $model->where( 'description', 'LIKE', "%{$actionPerformed}%" );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }    

    public static function oneAudit( $request ) {

        $log = ActivityLog::find( $request->id );

        return response()->json( $log );
    }
}