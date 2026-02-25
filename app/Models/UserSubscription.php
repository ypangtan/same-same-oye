<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class UserSubscription extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'cancelled_at',
        'platform',
        'platform_transaction_id',
        'platform_receipt',
        'type',
        'status',
    ];

    public function statusList() {
        return [
            10 => 'active',
            20 => 'expired',
            30 => 'refunded',
            40 => 'cancelled',
        ];
    }

    public function member() {
        return $this->hasMany( SubscriptionGroupMember::class, 'user_subscription_id' );
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function plan() {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function transactions() {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function scopeIsActive($query) {
        return $query->where('status', 10)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>', now());
    }
    
    public function scopeIsGroup($query) {
        return $query->whereHas( 'plan', function ( $q ) {
            $q->where( 'max_member', '>', 1 );
        } );
    }
    
    public function scopeNotHitMaxMember($query) {
        return $query->whereHas('plan', function ($q) {
            $q->where( 'max_member', '>', 1 );
        })->whereRaw('
            (SELECT COUNT(*) FROM subscription_group_members 
            WHERE subscription_group_members.user_subscription_id = user_subscriptions.id) 
            < 
            (SELECT max_member FROM subscription_plans 
            WHERE subscription_plans.id = user_subscriptions.subscription_plan_id)
        ');
    }

    public function cancel() {
        $this->update([
            'status' => 40,
            'cancelled_at' => now(),
        ]);

        return $this;
    }

    public function markAsExpired() {
        $this->update([
            'status' => 20,
        ]);

        return $this;
    }

    public function refund() {
        $this->update( [
            'status' => 30,
        ] );

        return $this;
    }

    public function getMemberCountAttribute() {
        return $this->member()->count();
    }

    public function getMaxMemberCountAttribute() {
        $plan = $this->plan()->first();

        return $plan ? $plan->max_member : 0;
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'user_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'cancelled_at',
        'platform',
        'platform_transaction_id',
        'platform_receipt',
        'status',
    ];

    protected static $logName = 'user_subscriptions';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
