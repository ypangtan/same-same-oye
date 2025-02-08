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
     * 2. update machines status
     * 
     * <strong>status</strong><br>
     * 10: Online<br>
     * 20: Offline<br>
     * 21: Maintenance Required<br>
     * 
     * 
     * @group Vending Machine API
     * 
     * @authenticated
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
     * 3. update machines stock
     * 
     * <strong>status</strong><br>
     * 10: Online<br>
     * 20: Offline<br>
     * 21: Maintenance Required<br>
     * 
     * 
     * @group Vending Machine API
     * 
     * @authenticated
     * 
     * @header X-Vending-Machine-Key string secret key of the machine to request verification. Example: 123ifa9sdb1j23sf
     * 
     * @bodyParam items array required The list of products with their ingredients. Example: [{"productId": 1, "froyo": [1, 2], "syrup": [3], "topping": [4, 5]}]
     * @bodyParam items.*.froyo array An array of froyo IDs. Pass an empty array if no froyo is selected. Example: [1, 2]
     * @bodyParam items.*.froyo.* integer A froyo ID. Example: 1
     * @bodyParam items.*.syrup array An array of syrup IDs. Pass an empty array if no syrup is selected. Example: [3]
     * @bodyParam items.*.syrup.* integer A syrup ID. Example: 3
     * @bodyParam items.*.topping array An array of topping IDs. Pass an empty array if no topping is selected. Example: [4, 5]
     * @bodyParam items.*.topping.* integer A topping ID. Example: 4
     * 
     */   
    public function updateVendingMachineStock( Request $request ) {

        return VendingMachineService::updateVendingMachineStatus( $request );
    }
}
