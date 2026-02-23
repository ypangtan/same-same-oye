<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserSubscription;

class UserSubscriptionObserver
{
    public function created( UserSubscription $userSubscription ) {
        $user = User::find( $userSubscription->user_id );
        $user->checkPlanValidity();

        $members = $userSubscription->member()->get();
        foreach( $members as $member ) {
            $user = User::find( $member->user_id );
            $user->checkPlanValidity();
        }
    }

    public function updated( UserSubscription $userSubscription ) {
        $user = User::find( $userSubscription->user_id );
        $user->checkPlanValidity();

        $members = $userSubscription->member()->get();
        foreach( $members as $member ) {
            $user = User::find( $member->user_id );
            $user->checkPlanValidity();
        }
    }

    public function deleted( UserSubscription $userSubscription) {
        $user = User::find( $userSubscription->user_id );
        $user->checkPlanValidity();

        $members = $userSubscription->member()->get();
        foreach( $members as $member ) {
            $user = User::find( $member->user_id );
            $user->checkPlanValidity();
        }
    }
}