<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    SalesRecordService,
    WalletService
};

use App\Models\{
    SalesRecord
};

class PointsController extends Controller
{
    /**
     * 1. Get Points
     * 
     * 
     * @authenticated
     * 
     * @group Points API
     * 
     * 
     */
    public function getPoints( Request $request ) {

        return SalesRecordService::getPoints( $request );
    }

    /**
     * 2. Redeem Points
     * 
     * <aside class="notice">Get all announcement filtered, claim the promotion with claim voucher api</aside>
     * 
     * @authenticated
     * 
     * @group Points API
     * 
     * @bodyParam reference required integer The referance of sales to be claim. Example: 1231ns-12
     * @bodyParam customer_name nullable integer The name of customer to claim points. Example: James
     * @bodyParam amount required float The id of amount of the sales. Example: 12.20
     * 
     */
    public function redeemPoints( Request $request ) {

        return SalesRecordService::redeemPoints( $request );
    }

    /**
     * 3. Get redeem history
     * 
     * 
     * @authenticated
     * 
     * @group Points API
     * 
     * @bodyParam start_date string optional redeemed time in Y-m-d Example: 2023-08-17
     * @bodyParam end_date string optional redeemed time in Y-m-d Example: 2023-08-17
     * 
     */
    public function getPointsRedeemHistory( Request $request ) {

        return SalesRecordService::getPointsRedeemHistory( $request );
    }

    /**
     * 4. Get conversion rate
     * 
     * 
     * @authenticated
     * 
     * @group Points API
     * 
     * 
     */
    public function getConversionRate( Request $request ) {

        return SalesRecordService::getConversionRate( $request );
    }

}
