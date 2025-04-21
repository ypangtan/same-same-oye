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
    Employee,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

class EmployeeService
{
    public static function allWorkers( $request ) {

        $worker = Employee::select( 'employees.*' );

        $filterObject = self::filter( $request, $worker );
        $worker = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $worker->orderBy( 'created_at', $dir );
                    break;
                case 3:
                    $worker->orderBy( 'name', $dir );
                    break;
                case 4:
                    $worker->orderBy( 'phone_number', $dir );
                    break;
                case 5:
                    $worker->orderBy( 'identification_number', $dir );
                    break;
                case 6:
                    $worker->orderBy( 'license_number', $dir );
                    break;
                case 7:
                    $worker->orderBy( 'license_expiry_date', $dir );
                    break;
                case 8:
                    $worker->orderBy( 'designation', $dir );
                    break;
                case 9:
                    $vendor->orderBy( 'status', $dir );
                    break;
            }
        }

        $workerCount = $worker->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $workers = $worker->skip( $offset )->take( $limit )->get();

        if ( $workers ) {
            $workers->append( [
                'path',
                'encrypted_id',
            ] );
        }

        $totalRecord = Employee::count();

        $data = [
            'workers' => $workers,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $workerCount : $totalRecord,
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

                $model->whereBetween( 'employees.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'employees.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( 'name', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->phone_number ) ) {
            $model->where( 'phone_number', 'LIKE', '%' . $request->phone_number . '%' );
            $filter = true;
        }

        if ( !empty( $request->identification_number ) ) {
            $model->where( 'identification_number', 'LIKE', '%' . $request->identification_number . '%' );
            $filter = true;
        }

        if ( !empty( $request->license_number ) ) {
            $model->where( 'license_number', 'LIKE', '%' . $request->license_number . '%' );
            $filter = true;
        }

        if ( !empty( $request->license_expiry_date ) ) {
            $model->where( 'license_expiry_date', $request->license_expiry_date );
            $filter = true;
        }

        if ( !empty( $request->designation ) ) {
            $model->where( 'designation', $request->designation );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( function( $query ) {
                $query->where( 'name', 'LIKE', '%' . request( 'custom_search' ) . '%' );
            } );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneWorker( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $worker = Employee::find( $request->id );

        if( $worker ) {
            $worker->append( [
                'path',
                'local_date_of_birth',
                'encrypted_id',
            ] );
        }

        return response()->json( $worker );
    }

    public static function createWorker( $request ) {

        $validator = Validator::make( $request->all(), [
            // 'photo' => [ 'required' ],
            'name' => [ 'required' ],
            'email' => [ 'nullable', 'bail', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'phone_number' => [ 'required', 'digits_between:8,15' ],
            'identification_number' => [ 'required' ],
            'date_of_birth' => [ 'nullable', 'date_format:Y-m-d' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'name' => __( 'worker.name' ),
            'email' => __( 'worker.email' ),
            'phone_number' => __( 'worker.phone_number' ),
            'identification_number' => __( 'worker.identification_number' ),
            'date_of_birth' => __( 'worker.driver_amount' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        $employmentDate = $request->employment_date != null ? Carbon::createFromFormat( 'Y-m-d', $request->employment_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->startOfDay() : null;
        $dateOfBirth = $request->date_of_birth != null ? Carbon::createFromFormat( 'Y-m-d', $request->date_of_birth, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->startOfDay() : null;

        try {

            $createWorker = Employee::create( [
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'identification_number' => $request->identification_number,
                'remarks' => $request->remarks,
                'date_of_birth' => $dateOfBirth,
                'date_of_birth' => $dateOfBirth,
                'age' => $request->age,
            ] );

            $file = FileManager::find( $request->photo );
            if ( $file ) {
                $fileName = explode( '/', $file->file );
                $target = 'workers/' . $createWorker->id . '/' . $fileName[1];
                Storage::disk( 'public' )->move( $file->file, $target );

                $createWorker->photo = $target;
                $createWorker->save();

                $file->status = 10;
                $file->save();
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.workers' ) ) ] ),
        ] );
    }

    public static function updateWorker( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            // 'photo' => [ 'required' ],
            'name' => [ 'required' ],
            'email' => [ 'nullable', 'bail', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'phone_number' => [ 'required', 'digits_between:8,15' ],
            'identification_number' => [ 'required' ],
            'date_of_birth' => [ 'nullable', 'date_format:Y-m-d' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'name' => __( 'worker.name' ),
            'email' => __( 'worker.email' ),
            'phone_number' => __( 'worker.phone_number' ),
            'identification_number' => __( 'worker.identification_number' ),
            'date_of_birth' => __( 'worker.driver_amount' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateWorker = Employee::find( $request->id );
            $updateWorker->name = $request->name;
            $updateWorker->email = $request->email;
            $updateWorker->phone_number = $request->phone_number;
            $updateWorker->identification_number = $request->identification_number;
            $updateWorker->remarks = $request->remarks;
            $updateWorker->date_of_birth = Carbon::createFromFormat( 'Y-m-d', $request->date_of_birth, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->startOfDay();
            $updateWorker->save();

            if ( $request->photo ) {
                $file = FileManager::find( $request->photo );
                if ( $file ) {

                    Storage::disk( 'public' )->delete( $updateWorker->photo );

                    $fileName = explode( '/', $file->file );
                    $target = 'workers/' . $updateWorker->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $file->file, $target );
    
                    $updateWorker->photo = $target;
                    $updateWorker->save();
    
                    $file->status = 10;
                    $file->save();
                }
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.workers' ) ) ] ),
        ] );
    }

    public static function updateWorkerStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateWorker = Employee::find( $request->id );
        $updateWorker->status = $request->status;
        $updateWorker->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.workers' ) ) ] ),
        ] );
    }

    public static function calculateBirthday( $request ) {

        return response()->json( [
            'age' => Carbon::parse( $request->date_of_birth )->age,
        ] );
    }
}