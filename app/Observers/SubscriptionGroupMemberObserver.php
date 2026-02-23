<?php

namespace App\Observers;

use App\Models\User;
use App\Models\SubscriptionGroupMember;

class SubscriptionGroupMemberObserver
{
    public function created( SubscriptionGroupMember $SubscriptionGroupMember ) {
        $member = $SubscriptionGroupMember->user()->first();
        if( $member ) {
            $member->checkPlanValidity();
        }
    }

    public function updated( SubscriptionGroupMember $SubscriptionGroupMember ) {
        $newMember = $SubscriptionGroupMember->user()->first();
        if ($newMember) $newMember->checkPlanValidity();

        if ( $SubscriptionGroupMember->wasChanged('user_id') ) {
            $oldUser = User::find($SubscriptionGroupMember->getOriginal('user_id'));
            if ($oldUser) $oldUser->checkPlanValidity();
        }
    }

    public function deleted( SubscriptionGroupMember $SubscriptionGroupMember) {
        $member = $SubscriptionGroupMember->user()->first();
        if( $member ) {
            $member->checkPlanValidity();
        }
    }
}