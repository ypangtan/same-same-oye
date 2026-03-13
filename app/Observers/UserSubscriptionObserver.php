<?php

namespace App\Observers;

use App\Jobs\CheckUserPlanValidityJob;
use App\Models\SubscriptionGroupMember;
use App\Models\User;
use App\Models\UserSubscription;

class UserSubscriptionObserver {

    public function created( UserSubscription $userSubscription ) {
        CheckUserPlanValidityJob::dispatch( $userSubscription->user_id );

        $userSubscription->member()
            ->pluck('user_id')
            ->each( fn($userId) => CheckUserPlanValidityJob::dispatch( $userId ) );

        $userSubscription->group()->each( fn($member) => $member->delete() );
    }

    public function updated( UserSubscription $userSubscription ) {
        CheckUserPlanValidityJob::dispatch( $userSubscription->user_id );

        // If the subscription is not active, we need to remove all the member of the plan.
        $members = SubscriptionGroupMember::where( 'leader_id', $userSubscription->user_id )->get();

        if( $userSubscription->status != 10 ) {
            try {
                \DB::beginTransaction();
                foreach( $members as $member ) {
                    $member->delete();
                }
                \DB::commit();
            } catch (\Throwable $e) {
                \DB::rollBack();
                \Log::error('Failed to remove subscription members: ' . $e->getMessage());
                throw $e;
            }
        } else {
            $userSubscription->group()->each( fn($downline) => $downline->delete() );
            foreach( $members as $member ) {
                CheckUserPlanValidityJob::dispatch( $member->id );
            }
        }


    }
}