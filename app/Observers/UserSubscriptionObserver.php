<?php

namespace App\Observers;

use App\Jobs\CheckUserPlanValidityJob;
use App\Models\SubscriptionGroupMember;
use App\Models\User;
use App\Models\UserSubscription;

class UserSubscriptionObserver {

    public function created( UserSubscription $userSubscription ) {
        \DB::afterCommit( function() use ( $userSubscription ) {
            CheckUserPlanValidityJob::dispatch( $userSubscription->user_id );

            $userSubscription->member()
                ->pluck('user_id')
                ->each( fn($userId) => CheckUserPlanValidityJob::dispatch( $userId ) );

            $userSubscription->group()->get()->each( fn($member) => $member->delete() );
        } );
    }

    public function updated( UserSubscription $userSubscription ) {
        \DB::afterCommit( function() use ( $userSubscription ) {
            CheckUserPlanValidityJob::dispatch( $userSubscription->user_id );

            // If the subscription is not active, we need to remove all the member of the plan.
            $members = SubscriptionGroupMember::where( 'leader_id', $userSubscription->user_id )->get();

            if( $userSubscription->status != 10 ) {
                try {
                    foreach( $members as $member ) {
                        $member->delete();
                    }
                } catch (\Throwable $e) {
                    \Log::error('Failed to remove subscription members: ' . $e->getMessage());
                    throw $e;
                }
            } else {
                foreach( $members as $member ) {
                    CheckUserPlanValidityJob::dispatch( $member->user_id );
                }
            }
        } );
    }
}