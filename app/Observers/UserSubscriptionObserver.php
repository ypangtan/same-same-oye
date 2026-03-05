<?php

namespace App\Observers;

use App\Jobs\CheckUserPlanValidityJob;
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
        $userSubscription->group()->each( fn($member) => $member->delete() );

        $userSubscription->member()
            ->pluck('user_id')
            ->each( fn($userId) => CheckUserPlanValidityJob::dispatch( $userId ) );

        // If the subscription is not active, we need to remove all the member of the plan.
        if( $userSubscription->status != 10 ) {
            try {
                \DB::beginTransaction();
                $members = $userSubscription->member()->get();
                foreach( $members as $member ) {
                    $member->delete();
                }
                \DB::commit();
            } catch (\Throwable $e) {
                \DB::rollBack();
                \Log::error('Failed to remove subscription members: ' . $e->getMessage());
                throw $e;
            }
        }
    }
}