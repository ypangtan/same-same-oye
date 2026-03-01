<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    SubscriptionGroupMemberService,
    UserSubscriptionService
};

use App\Models\{
    Announcement
};

class SubscriptionGroupMemberController extends Controller
{
    /**
     * 1. Get Subscription Group Members 
     * 
     * @authenticated
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
     * @authenticated
     * 
     * @bodyParam user string The email of the user. Example: user@example.com
     * 
     */
    public function createSubscriptionGroupMember( Request $request ) {

        return SubscriptionGroupMemberService::createSubscriptionGroupMemberApi( $request );
    }

    /**
     * 3. Update Subscription Group Member 
     * 
     * @group Subscription Group Member API
     * 
     * @authenticated
     * 
     * @bodyParam id string The encrypted_id of the subscription group member. Example: 52
     * @bodyParam user string The email of the user. Example: user@example.com
     * 
     */
    public function updateSubscriptionGroupMember( Request $request ) {

        return SubscriptionGroupMemberService::updateSubscriptionGroupMemberApi( $request );
    }

    /**
     * 4. Delete Subscription Group Member 
     * 
     * @group Subscription Group Member API
     * 
     * @authenticated
     * 
     * @bodyParam id string The encrypted_id of the subscription group member. Example: 52
     * 
     */
    public function deleteSubscriptionGroupMember( Request $request ) {

        return SubscriptionGroupMemberService::deleteSubscriptionGroupMember( $request );
    }

    /**
     * 5. Accept Subscription Group Member 
     * 
     * @group Subscription Group Member API
     * 
     * @authenticated
     * 
     * @bodyParam token string The token. Example: 52
     * 
     */
    public function acceptSubscriptionGroupMember( Request $request ) {

        return SubscriptionGroupMemberService::acceptSubscriptionGroupMember( $request );
    }

    /**
     * 6. Verify User Subscription 
     * 
     * @group Subscription Group Member API
     * 
     * @authenticated
     * 
     * @bodyParam plan_id string The plan_id of the subscription. Example: 52
     * 
     */
    public function verifyUserSubscription( Request $request ) {

        return UserSubscriptionService::verifyUserSubscription( $request );
    }

    

}
