<?php

namespace App\Observers;

use App\Jobs\CheckUserPlanValidityJob;
use App\Models\User;
use App\Models\SubscriptionGroupMember;

class SubscriptionGroupMemberObserver {

    public function created( SubscriptionGroupMember $member ) {
        CheckUserPlanValidityJob::dispatch($member->user_id);
        
        \Log::info('UserSubscriptionObserver@created triggered', [
            'user_id' => $member->user_id,
            'subscription_id' => $member->id,
        ]);
    }

    public function updated( SubscriptionGroupMember $member ) {
        CheckUserPlanValidityJob::dispatch($member->user_id);

        // 处理旧用户
        if ($member->wasChanged('user_id')) {
            $oldUserId = $member->getOriginal('user_id');
            CheckUserPlanValidityJob::dispatch($oldUserId);
        }
        
        \Log::info('UserSubscriptionObserver@updated triggered', [
            'user_id' => $member->user_id,
            'subscription_id' => $member->id,
        ]);
    }

    public function deleted( SubscriptionGroupMember $member ) {
        CheckUserPlanValidityJob::dispatch($member->user_id);
        
        \Log::info('UserSubscriptionObserver@deleted triggered', [
            'user_id' => $member->user_id,
            'subscription_id' => $member->id,
        ]);
    }
}