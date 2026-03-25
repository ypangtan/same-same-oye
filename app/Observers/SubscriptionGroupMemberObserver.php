<?php

namespace App\Observers;

use App\Jobs\CheckUserPlanValidityJob;
use App\Models\User;
use App\Models\SubscriptionGroupMember;

class SubscriptionGroupMemberObserver {

    public function created( SubscriptionGroupMember $member ) {
        \DB::afterCommit( function() use ( $member ) {
            CheckUserPlanValidityJob::dispatch($member->user_id)->delay(now()->addSeconds(1));
        } );
    }

    public function updated( SubscriptionGroupMember $member ) {
        \DB::afterCommit( function() use ( $member ) {
            CheckUserPlanValidityJob::dispatch($member->user_id)->delay(now()->addSeconds(1));

            // 处理旧用户
            if ( $member->wasChanged('user_id') ) {
                $oldUserId = $member->getOriginal('user_id');
                CheckUserPlanValidityJob::dispatch($oldUserId)->delay( now()->addSeconds(1) );
            }
        } );
    }

    public function deleted( SubscriptionGroupMember $member ) {
        \DB::afterCommit( function() use ( $member ) {
            CheckUserPlanValidityJob::dispatch($member->user_id)->delay(now()->addSeconds(1));
        } );
    }
}