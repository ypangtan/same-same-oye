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
     * 1. Get vending machines status
     * 
     * @group Vending Machine Operation API
     * 
     * @header X-Vending-Machine-Key string secret key of the machine to request verification. Example: 123ifa9sdb1j23sf
     * 
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
