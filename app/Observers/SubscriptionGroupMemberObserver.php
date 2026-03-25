<?php

namespace App\Observers;

use App\Jobs\CheckUserPlanValidityJob;
use App\Models\User;
use App\Models\SubscriptionGroupMember;

class SubscriptionGroupMemberObserver {

    public function created( SubscriptionGroupMember $member ) {
        \DB::afterCommit( function() use ( $member ) {
            CheckUserPlanValidityJob::dispatchSafe($member->user_id);
        } );
    }

    public function updated( SubscriptionGroupMember $member ) {
        \DB::afterCommit( function() use ( $member ) {
            CheckUserPlanValidityJob::dispatchSafe($member->user_id);

            // 处理旧用户
            if ( $member->wasChanged('user_id') ) {
                $oldUserId = $member->getOriginal('user_id');
                CheckUserPlanValidityJob::dispatchSafe($oldUserId);
            }
        } );
    }

    public function deleted( SubscriptionGroupMember $member ) {
        \DB::afterCommit( function() use ( $member ) {
            CheckUserPlanValidityJob::dispatchSafe($member->user_id);
        } );
    }
}