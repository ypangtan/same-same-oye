<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ProductService,
    ProductBundleService,
};

class MenuController extends Controller
{
    /**
     * 1. Get menus
     * 
     * @group Menu API
     * 
     * 
     * 
     */   
    public function getMenus( Request $request ) {

        return ProductService::getMenus( $request );
    }
    
    /**
     * 2. Get selections
     * 
     * @group Menu API
     * 
     * 
     * 
     */   
    public function getSelections( Request $request ) {

        return ProductService::getSelections( $request );
    }
    
    /**
     * 3. Get froyos
     * 
     * @group Menu API
     * 
     * 
     * 
     */   
    public function getFroyos( Request $request ) {

        return ProductService::getFroyos( $request );
    }
    
    /**
     * 4. Get syrups
     * 
     * @group Menu API
     * 
     * 
     * 
     */   
    public function getSyrups( Request $request ) {

        return ProductService::getSyrups( $request );
    }
    
    /**
     * 5. Get toppings
     * 
     * @group Menu API
     * 
     * 
     * 
     */   
    public function getToppings( Request $request ) {

        return ProductService::getToppings( $request );
    }

    /**
     * 6. Get Bundles
     * 
     * @group Menu API
     * 
     * 
     * 
     */   
    public function getBundles( Request $request ) {

        return ProductBundleService::getBundles( $request );
    }
}
