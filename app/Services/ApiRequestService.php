<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
};

use Helper;

use App\Models\{
   ApiRequest
};

use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Carbon\Carbon;

class ApiRequestService
{

    public static function createApiRequest( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'endpoint' => [ 'required' ],
            'method' => [ 'required' ],
            'request_body' => [ 'nullable' ],
            'response_body' => [ 'nullable' ],
            'api_name' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
        ] );

        $validator->validate();

        DB::beginTransaction();

        try {
            $ApiRequestCreate = ApiRequest::create([
                'endpoint' => $request->endpoint,
                'method' => $request->method,
                'request_body' => json_encode( $request->request_body),
                'response_body' => json_encode( $request->response_body),
                'api_name' => $request->api_name,
                'remarks' => $request->remarks,
                'status' => 20,
            ]);

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.api_requests' ) ) ] ),
            'data' => $ApiRequestCreate
        ] );
    }

}