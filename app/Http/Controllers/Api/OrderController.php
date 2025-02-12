<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    OrderService,
    DiscountRuleService,
};

class OrderController extends Controller
{
    /**
     * 1. checkout
     * 
     * <strong>payment_method</strong><br>
     * 1: yobe wallet<br>
     * 2: payment gateway<br>
     * 
     * @authenticated
     * 
     * @group Order API
     * 
     * @bodyParam cart integer required The ID of the cart. Example: 1
     * @bodyParam promo_code integer The ID of the promotion/voucher to apply. Example: BUY1FREE1
     * @bodyParam payment_method integer The payment Method. Example: 1
     * 
     */
    public function checkout( Request $request ) {

        return OrderService::checkout( $request );
    }

    /**
     * 2. Retrieve user order
     * 
     * <aside class="notice">id and reference can be used to filter out the order</aside>
     * 
     * <strong>status</strong><br>
     * 1: placed order / pending payment<br>
     * 3: paid / unclaimed<br>
     * 10: completed / claimed<br>
     * 20: canceled<br>
     * 
     * @authenticated
     * 
     * @group Order API
     * 
     * @queryParam reference string The unique reference for the order. Example: abcd-1234
     * @queryParam id integer The ID of the order. . Example: 1
     * @queryParam status integer The Status of the order. . Example: 1
     * @queryParam per_page integer Retrieve how many insurance quote in a page, default is 10. Example: 10
     * @queryParam user_bundle integer Retrieve User Bundle bought within order. Example: 1
     * 
     */
    public function getOrder( Request $request ) {

        return OrderService::getOrder( $request );
    }

    /**
     * 3. Retry Payment
     * 
     * <aside class="notice">retry payment for online payment</aside>
     * 
     * @authenticated
     * 
     * @group Order API
     * 
     * @queryParam order_id integer The ID of the order. Example: 1
     */
    public function retryPayment( Request $request ) {

        return OrderService::retryPayment( $request );
    }

    /**
     * 1. Update order status (Scan Order)
     * 
     * 
     * @group Order Operation API
     * 
     * @header X-Vending-Machine-Key string secret key of the machine to request verification. Example: 123ifa9sdb1j23sf
     * 
     * @bodyParam reference string Order reference to be updated. Example: 1knkbasbmc
     * 
     */   
    public function updateOrderStatus( Request $request ) {

        return OrderService::updateOrderStatusOperation( $request );
    }

    /**
     * 2. Update machine sales
     * 
     * <strong>sales_type</strong><br>
     * 1: daily <br>
     * 2: weekly <br>
     * 3: monthly <br>
     * 
     * @group Order Operation API
     * 
     * @header X-Vending-Machine-Key string secret key of the machine to request verification. Example: 123ifa9sdb1j23sf
     * 
     * @bodyParam sales_date string required The date of sales in YYYY-MM-DD format. Example: "2025-02-12"
     * @bodyParam sales_type integer The type of sales (e.g., 1 for daily, 2 for weekly, 3 for monthly). Default: 1 Example: 2
     * @bodyParam sales_metas array Additional sales metadata (e.g., products sold). Example: [{"product_id": 1, "quantity": 3}]
     * @bodyParam order_references array A list of related order references. Example: ["ORD-12345", "ORD-67890"]
     * 
     */   
    public function updateSalesData( Request $request ) {

        return OrderService::updateSalesData( $request );
    }
    
}
