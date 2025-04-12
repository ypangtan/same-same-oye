<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    WalletService,
};

class WalletController extends Controller
{

    /**
     * 1. Get wallet
     * 
     * @group Wallet API
     * 
     * @authenticated
     * 
     * 
     */   
    public function getWallet( Request $request ) {

        return WalletService::getWallet( $request );
    }

    /**
     * 2. Get wallet transactions
     * 
     * @group Wallet API
     * 
     * @authenticated
     * 
     * @queryParam per_page integer Retrieve how many my tranasction in a page, default is 10. Example: 10
     * 
     */   
    public function getWalletTransactions( Request $request ) {

        return WalletService::getWalletTransactions( $request );
    }

    /**
     * 3. Topup
     * 
     * <strong>payment_method</strong><br>
     * 1: Payment Gateway
     * 
     * @group Wallet API
     * 
     * @authenticated
     * 
     * @bodyParam topup_amount numeric required The topup amount. Example: 10
     * @bodyParam payment_method integer required The payment method. Example: 1
     * 
     */
    public function topup( Request $request ) {

        return WalletService::topup( $request );
    }

    /**
     * 1. Get points history
     * 
     * @group IFei Points API
     * 
     * @authenticated
     * 
     * @queryParam per_page integer Retrieve how many my tranasction in a page, default is 10. Example: 10
     * 
     */   
    public function getPointsHistories( Request $request ) {

        $request->merge( [
            'type' => 2
        ] );
        
        return WalletService::getWalletTransactions( $request );
    }
}
