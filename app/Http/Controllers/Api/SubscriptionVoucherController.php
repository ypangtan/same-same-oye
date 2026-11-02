<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    SubscriptionVoucherService,
};

class SubscriptionVoucherController extends Controller
{
    /**
     * 1. Claim Subscription Voucher
     *
     * Redeem a voucher code into a free subscription ( duration_in_months of the voucher ).
     * Only free user ( no active own plan and not in any group plan ) can claim.
     * Each user can only claim the same voucher once.
     *
     * The code is also returned as voucher_code in the notifications API ( key = subscription_voucher ).
     *
     * @group Subscription Voucher API
     *
     * @authenticated
     *
     * @bodyParam code string required The voucher code, case insensitive. Example: ABCD2345
     *
     */
    public function claimSubscriptionVoucher( Request $request ) {

        return SubscriptionVoucherService::claimSubscriptionVoucher( $request );
    }
}
