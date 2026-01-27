<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    AnnouncementService,
    AdService,
    InAppPurchaseService,
    PopAnnouncementService,
    VoucherService
};

use App\Models\{
    Announcement
};

class InAppPurchaseController extends Controller {

    /**
     * 1. Verify Payment 
     * 
     * @group In App Purchase API
     * 
     * <strong>platform</strong><br>
     * 1: IOS<br>
     * 2: Android<br>
     * 3: Huawei<br>
     * 
     * @authenticated
     * 
     * @bodyParam platform string The platform. Example: 1
     * @bodyParam receipt_data string The receipt_data required when ios. Example: 1
     * @bodyParam product_id string The product_id. Example: 1
     * @bodyParam purchase_token string The purchase_token required when Android and huawei. Example: 1
     * @bodyParam purchase_data string The purchase_data required when huawei. Example: 1
     * @bodyParam signature string The signature required when huawei. Example: 1
     * 
     */
    public function verifyPayment( Request $request ) {

        return InAppPurchaseService::verifyPayment( $request );
    }

    /**
     * 2. Get Current Subscription 
     * 
     * @group In App Purchase API
     * 
     * @authenticated
     * 
     * 
     */
    public function getCurrentSubscription() {

        return InAppPurchaseService::getCurrentSubscription();
    }

    /**
     * 3. Cancel Subscription 
     * 
     * @group In App Purchase API
     * 
     * @authenticated
     * 
     */
    public function cancelSubscription() {

        return InAppPurchaseService::cancelSubscription();
    }

    /**
     * 4. Get Plans 
     * 
     * @group In App Purchase API
     * 
     */
    public function getPlans() {

        return InAppPurchaseService::getPlans();
    }

}
