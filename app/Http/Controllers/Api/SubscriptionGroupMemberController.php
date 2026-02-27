<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    SubscriptionGroupMemberService
};

use App\Models\{
    Announcement
};

class SubscriptionGroupMemberController extends Controller
{
    /**
     * 1. Get Subscription Group Members 
     * 
     * @group Subscription Group Member API
     * 
     */
    public function getSubscriptionGroupMembers( Request $request ) {

        return SubscriptionGroupMemberService::getSubscriptionGroupMembers( $request );
    }

    /**
     * 2. Create Subscription Group Member 
     * 
     * @group Subscription Group Member API
     * 
     * @bodyParam user_id string The encrypted_id of the user. Example: 52
     * 
     */
    public function createSubscriptionGroupMember( Request $request ) {

        return SubscriptionGroupMemberService::createSubscriptionGroupMemberApi( $request );
    }

    /**
     * 3. Delete Subscription Group Member 
     * 
     * @group Subscription Group Member API
     * 
     * @bodyParam id string The encrypted_id of the subscription group member. Example: 52
     * 
     */
    public function deleteSubscriptionGroupMember( Request $request ) {

        return SubscriptionGroupMemberService::deleteSubscriptionGroupMember( $request );
    }

    /**
     * 4. Search User for Subscription Group Member 
     * 
     * @group Subscription Group Member API
     * 
     * @bodyParam user string The search text for the user. Example: 52
     * @bodyParam per_page string The data per page. Example: 10
     * 
     */
    public function searchUser( Request $request ) {

        return SubscriptionGroupMemberService::searchUser( $request );
    }

}
