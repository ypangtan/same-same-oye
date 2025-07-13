<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    VoucherService
};

class VoucherController extends Controller
{
    /**
     * 1. Get vouchers 
     * 
     * <aside class="notice">Get all voucher that is claimable</aside>
     * 
     * <strong>voucher discount type</strong></br>
     * 1: percentage<br>
     * 2: fixed amount<br>
     * 3: buy x free y<br>
     * 
     * <strong>voucher type</strong></br>
     * 1: public voucher<br>
     * 2: points redeemable<br>
     * 3: register reward<br>
     *
     * <strong>user_voucher (retrieve user's voucher)</strong></br>
     * 1: true<br>
     * 2: false<br>
     * 
     * <strong>expired_only (retrieve user's expired voucher)</strong></br>
     * 1: true<br>
     * 2: false<br>
     * 
     * @authenticated
     * 
     * @group Voucher API
     * 
     * @queryParam per_page integer Retrieve how many product in a page, default is 10. Example: 10
     * @queryParam promo_code string The voucher code to be filter. Example: XBMSD22
     * @queryParam user_voucher integer Retrieve all user's voucher only Example: 1
     * @queryParam voucher_type integer The voucher type to be filter Example: 1
     * @queryParam discount_type integer The voucher discount type to be filter Example: 2
     * @queryParam expired_only integer The voucher usage type to be filter Example: 2
     * 
     */
    public function getVouchers( Request $request ) {

        return VoucherService::getVouchers( $request );
    }

    /**
     * 1. Get promo code 
     * 
     * <aside class="notice">Get all promo code</aside>
     * 
     * @authenticated
     * 
     * @group Promo Code API
     * 
     * @queryParam per_page integer Retrieve how many product in a page, default is 10. Example: 10
     * @queryParam promo_code string The promo code to be filter. Example: XBMSD22
     * @queryParam discount_type integer The promo discount type to be filter Example: 2
     * 
     */
    public function getPromoCode( Request $request ) {

        $request->merge(['voucher_type' => 1, 'user_voucher' => null]);
        return VoucherService::getVouchers( $request );
    }

    /**
     * 3. Validate vouchers
     * 
     * @authenticated
     * 
     * @group Voucher API
     * 
     * @bodyParam cart integer required The cart id Example: 1
     * @bodyParam promo_code string The voucher code to be validate. Example: XBMSD22
     * 
     */
    public function validateVoucher( Request $request ) {

        return VoucherService::validateVoucher( $request );
    }

    /**
     * 4. Claim Vouchers
     * 
     * @authenticated
     * 
     * @group Voucher API
     * 
     * @bodyParam voucher_id required integer The voucher_id to be claim. Example: 1
     * 
     */
    public function claimVoucher( Request $request ) {

        return VoucherService::claimVoucher( $request );
    }
}
