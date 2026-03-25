<?php

namespace App\Observers;

use App\Jobs\CheckUserPlanValidityJob;
use App\Models\SubscriptionGroupMember;
use App\Models\User;
use App\Models\UserSubscription;

class UserSubscriptionObserver {

    public function created( UserSubscription $userSubscription ) {
        \DB::afterCommit( function() use ( $userSubscription ) {
            CheckUserPlanValidityJob::dispatchSafe( $userSubscription->user_id );

            $userSubscription->member()
                ->pluck('user_id')
                ->each( fn($userId) => CheckUserPlanValidityJob::dispatchSafe( $userId ) );

            $userSubscription->group()->get()->each( fn($member) => $member->delete() );
        } );
    }

    public function updated( UserSubscription $userSubscription ) {
        \DB::afterCommit( function() use ( $userSubscription ) {
            CheckUserPlanValidityJob::dispatchSafe( $userSubscription->user_id );

            // If the subscription is not active, we need to remove all the member of the plan.
            $members = SubscriptionGroupMember::where( 'leader_id', $userSubscription->user_id )->get();

            if( $userSubscription->status != 10 ) {
                foreach( $members as $member ) {
                    $member->delete();
                }
            } else {
                foreach( $members as $member ) {
                    CheckUserPlanValidityJob::dispatchSafe( $member->user_id );
                }
            }
        } );
    }
}