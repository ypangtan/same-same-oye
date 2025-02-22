<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    VendingMachineService,
};

class VendingMachineController extends Controller
{
    /**
     * 1. Get vending machines
     * 
     * @group Vending Machine API
     * 
     * @authenticated
     * 
     * @queryParam title string Vending Machine Title to be filter Example: KL ECOCITY
     * 
     */   
    public function getVendingMachines( Request $request ) {

        return VendingMachineService::getVendingMachines( $request );
    }

/**
     * 1. Get vending machines
     *
     * Retrieves the details of vending machines from the backoffice.
     * This endpoint requires authentication.
     *
     * @group Vending Machine API
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "vending_machine": {
     *       "id": 4,
     *       "code": "MD01",
     *       "title": "Midvalley",
     *       "description": "Midvalley Megamall",
     *       "latitude": "3.118056",
     *       "longitude": "101.676666",
     *       "address_1": "Lingkaran Syed Putra, Mid Valley City, 59200 Kuala Lumpur",
     *       "city": "KL",
     *       "state": "Kuala Lumpur",
     *       "postcode": "59200",
     *       "status": 10,
     *       "opening_hour": "2025-02-08 10:00:00",
     *       "closing_hour": "2025-02-08 11:00:00",
     *       "navigation_links": "https://g.co/kgs/XwXNHFf",
     *       "stocks": [
     *         {
     *           "id": 9,
     *           "vending_machine_id": 4,
     *           "froyo_id": 2,
     *           "quantity": 100,
     *           "froyo": {
     *             "id": 2,
     *             "code": "choco",
     *             "title": "Chocolate",
     *             "image": "froyo/2/skdqNYKoAP4VJu9GmYklpSpst2YB4gohIQtsfgAK.png",
     *             "price": "0.00"
     *           }
     *         },
     *         {
     *           "id": 10,
     *           "vending_machine_id": 4,
     *           "syrup_id": 1,
     *           "quantity": 100,
     *           "syrup": {
     *             "id": 1,
     *             "code": "chocolate",
     *             "title": "Chocolate",
     *             "image": "syrup/1/7I6h6rZK2u2vaLYYzllW6Fr2R2K6MCMjVOrhsyAn.png",
     *             "price": "1.99"
     *           }
     *         }
     *       ]
     *     },
     *     "message_key": "get_vending_machine_success"
     *   }
     * }
     */
    public function getVendingMachineStatus( Request $request ) {

        return VendingMachineService::getVendingMachineStatus( $request );
    }

    /**
     * 2. update machines status
     * 
     * <strong>status</strong><br>
     * 10: Online<br>
     * 20: Offline<br>
     * 21: Maintenance Required<br>
     * 
     * 
     * @group Vending Machine Operation API
     * 
     * @header X-Vending-Machine-Key string secret key of the machine to request verification. Example: 123ifa9sdb1j23sf
     * 
     * @bodyParam status string Vending Machine Status to be updated. Example: 1
     * 
     */   
    public function updateVendingMachineStatus( Request $request ) {

        return VendingMachineService::updateVendingMachineStatus( $request );
    }

    /**
     * 3. deduct machines stock
     * 
     * 
     * @group Vending Machine Operation API
     * 
     * 
     * @header X-Vending-Machine-Key string secret key of the machine to request verification. Example: 123ifa9sdb1j23sf
     * 
     * @bodyParam froyos array required An array of objects representing froyo stock changes. Example: [{"1": -1}]
     * @bodyParam froyos.* object required A key-value pair where the key is the froyo ID, and the value is the quantity change. Example: {"1": -1}
     * @bodyParam syrups array required An array of objects representing syrup stock changes. Example: [{"1": -1}]
     * @bodyParam syrups.* object required A key-value pair where the key is the syrup ID, and the value is the quantity change. Example: {"1": -1}
     * @bodyParam toppings array required An array of objects representing topping stock changes. Example: [{"1": -1}]
     * @bodyParam toppings.* object required A key-value pair where the key is the topping ID, and the value is the quantity change. Example: {"1": -1}
     * 
     */   
    public function deductVendingMachineStock( Request $request ) {
        $request->merge( [
            'update_method' => 1,
        ] );
        return VendingMachineService::updateVendingMachineStock( $request );
    }

    /**
     * 4. update machines stock
     * 
     * 
     * @group Vending Machine Operation API
     * 
     * 
     * @header X-Vending-Machine-Key string secret key of the machine to request verification. Example: 123ifa9sdb1j23sf
     * 
     * @bodyParam froyos array required An array of objects representing froyo stock changes. Example: [{"1": 100}]
     * @bodyParam froyos.* object required A key-value pair where the key is the froyo ID, and the value is the quantity change. Example: {"1": 100}
     * @bodyParam syrups array required An array of objects representing syrup stock changes. Example: [{"1": 98}]
     * @bodyParam syrups.* object required A key-value pair where the key is the syrup ID, and the value is the quantity change. Example: {"1": 98}
     * @bodyParam toppings array required An array of objects representing topping stock changes. Example: [{"1": 50}]
     * @bodyParam toppings.* object required A key-value pair where the key is the topping ID, and the value is the quantity change. Example: {"1": 50}
     * 
     */   
    public function updateVendingMachineStock( Request $request ) {
        $request->merge( [
            'update_method' => 2,
        ] );
        return VendingMachineService::updateVendingMachineStock( $request );
    }

    /**
     * 5. Alert stock
     * 
     * 
     * @group Vending Machine Operation API
     * 
     * 
     * @header X-Vending-Machine-Key string secret key of the machine to request verification. Example: 123ifa9sdb1j23sf
     * 
     * @bodyParam froyos array required An array of objects representing froyo stock changes. Example: [{"1": 100}]
     * @bodyParam froyos.* object required A key-value pair where the key is the froyo ID, and the value is the quantity change. Example: {"1": 100}
     * @bodyParam syrups array required An array of objects representing syrup stock changes. Example: [{"1": 98}]
     * @bodyParam syrups.* object required A key-value pair where the key is the syrup ID, and the value is the quantity change. Example: {"1": 98}
     * @bodyParam toppings array required An array of objects representing topping stock changes. Example: [{"1": 50}]
     * @bodyParam toppings.* object required A key-value pair where the key is the topping ID, and the value is the quantity change. Example: {"1": 50}
     * 
     */   
    public function alertStock( Request $request ) {
        return VendingMachineService::alertStock( $request );
    }

}
