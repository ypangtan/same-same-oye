<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ApiRequestService,
};

class ApiRequestController extends Controller
{
    /**
     * @group Api-Request API
     *
     * API for submitting and managing API request submissions.
     */

    /**
     * Submit a new API request.
     *
     * @bodyParam endpoint string required The desired API endpoint. Example: "/order/create"
     * @bodyParam method string optional The HTTP method for the API request. Must be one of: GET, POST, PUT, DELETE. Example: "POST"
     * @bodyParam request_body object optional The JSON request payload structure. Example: {"order_id": 12345, "amount": 100}
     * @bodyParam response_body object optional The expected JSON response format. Example: {"success": true, "message": "Order created"}
     * @bodyParam api_name string optional A descriptive name for the API. Example: "Order Creation API"
     * @bodyParam remarks string optional Additional remarks or comments about the API request. Example: "Urgent request for new order system."
     *
     * @response 201 {"message": "API request submitted successfully", "data": {"id": 1, "endpoint": "/order/create", "method": "POST", "status": "pending"}}
     */
    public function createApiRequest( Request $request ) {

        return ApiRequestService::createApiRequest( $request );
    }
}
