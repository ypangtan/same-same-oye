<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use App\Models\{
    FileManager,
    LuckyDrawImportHistory,
    OtpLog,
    OtpLog,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;

class OtpLogService
{
    public static function allOtpLogs( $request ) {

        $otpLog = OtpLog::select( 'otp_logs.*' );

        $filterObject = self::filter( $request, $otpLog );
        $otpLog = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $otpLog->orderBy( 'created_at', $dir );
                    break;
                case 3:
                    $otpLog->orderBy( 'customer_member_id', $dir );
                    break;
                case 4:
                    $otpLog->orderBy( 'name', $dir );
                    break;
                case 5:
                    $otpLog->orderBy( 'quantity', $dir );
                    break;
            }
        }

        $otpLogCount = $otpLog->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $otpLogs = $otpLog->skip( $offset )->take( $limit )->get();

        if ( $otpLogs ) {
            $otpLogs->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = OtpLog::count();

        $data = [
            'otp_logs' => $otpLogs,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $otpLogCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
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

                $model->whereBetween( 'otp_logs.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'otp_logs.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->phone_number ) ) {
            $model->where( 'phone_number', 'LIKE', '%' . $request->phone_number . '%' );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneOtpLog( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $otp_log = OtpLog::find( $request->id );

        if( $otp_log ) {
            $otp_log->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $otp_log );
    }

}