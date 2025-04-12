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
     * <aside class="notice">Get user's current points </aside>
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
     * <aside class="notice">Redeem points with reference and amount, customer name is not required yet, as now use the logged in user's name</aside>
     * 
     * @authenticated
     * 
     * @group Points API
     * 
     * @bodyParam reference required integer The referance of sales to be claim. Example: 1231ns-12
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
     * <aside class="notice">Get user's points history </aside>
     * 
     * 
     * @authenticated
     * 
     * @group Points API
     * 
     * @queryParam start_date string optional redeemed time in Y-m-d Example: 2023-08-17
     * @queryParam end_date string optional redeemed time in Y-m-d Example: 2023-08-17
     * @queryParam per_page integer Show how many record in a page. Leave blank for default (100). Example: 5
     * 
     */
    public function getPointsRedeemHistory( Request $request ) {

        return SalesRecordService::getPointsRedeemHistory( $request );
    }

    /**
     * 4. Get conversion rate
     * 
     * <aside class="notice">Get conversion rate, if any </aside>
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
