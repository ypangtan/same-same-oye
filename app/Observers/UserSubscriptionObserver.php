<?php

namespace App\Observers;

use App\Jobs\CheckUserPlanValidityJob;
use App\Models\SubscriptionGroupMember;
use App\Models\User;
use App\Models\UserSubscription;

class UserSubscriptionObserver {

    public function created( UserSubscription $userSubscription ) {
        \DB::afterCommit( function() use ( $userSubscription ) {
            CheckUserPlanValidityJob::dispatch( $userSubscription->user_id )->delay(now()->addSeconds(1));

            $userSubscription->member()
                ->pluck('user_id')
                ->each( fn($userId) => CheckUserPlanValidityJob::dispatch( $userId )->delay(now()->addSeconds(1)) );

            $userSubscription->group()->get()->each( fn($member) => $member->delete() );
        } );
    }

    public function updated( UserSubscription $userSubscription ) {
        \DB::afterCommit( function() use ( $userSubscription ) {
            CheckUserPlanValidityJob::dispatch( $userSubscription->user_id )->delay(now()->addSeconds(1));

            // If the subscription is not active, we need to remove all the member of the plan.
            $members = SubscriptionGroupMember::where( 'leader_id', $userSubscription->user_id )->get();

            if( $userSubscription->status != 10 ) {
                foreach( $members as $member ) {
                    $member->delete();
                }
            } else {
                foreach( $members as $member ) {
                    CheckUserPlanValidityJob::dispatch( $member->user_id )->delay(now()->addSeconds(1));
                }
            }
        } );
    }
}