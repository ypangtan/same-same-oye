<?php

namespace App\Observers;

use App\Jobs\CheckUserPlanValidityJob;
use App\Models\User;
use App\Models\SubscriptionGroupMember;

class SubscriptionGroupMemberObserver
{
    public function created( SubscriptionGroupMember $member ) {
        CheckUserPlanValidityJob::dispatch($member->user_id);
    }

    public function updated( SubscriptionGroupMember $member ) {
        CheckUserPlanValidityJob::dispatch($member->user_id);

        // 处理旧用户
        if ($member->wasChanged('user_id')) {
            $oldUserId = $member->getOriginal('user_id');
            CheckUserPlanValidityJob::dispatch($oldUserId);
        }
    }

    public function deleted( SubscriptionGroupMember $member ) {
        CheckUserPlanValidityJob::dispatch($member->user_id);
    }
}