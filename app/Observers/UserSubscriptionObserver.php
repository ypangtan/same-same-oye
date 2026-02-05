<?php

namespace App\Observers;

use App\Models\UserSubscription;

class UserSubscriptionObserver
{
    public function created( UserSubscription $userSubscription ) {
        $userSubscription->checkPlanValidity();
    }

    public function updated( UserSubscription $userSubscription ) {
        $userSubscription->checkPlanValidity();
    }

    public function deleted( UserSubscription $userSubscription) {
        $userSubscription->checkPlanValidity();
    }
}