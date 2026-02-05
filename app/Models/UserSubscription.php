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
            ->where('end_date', '>', now());
    }

    public function isActive() {
        return $this->status === 10 
            && $this->end_date 
            && $this->end_date->isFuture();
    }

    public function isExpired() {
        return $this->end_date && $this->end_date->isPast();
    }

    public function renew( $days )  {
        $newEndDate = $this->end_date && $this->end_date->isFuture()
            ? $this->end_date->addDays($days)
            : now()->addDays($days);

        $this->update([
            'status' => 10,
            'end_date' => $newEndDate,
        ]);

        self::checkPlanValidity();

        return $this;
    }

    public function cancel() {
        $this->update([
            'status' => 40,
            'cancelled_at' => now(),
        ]);

        self::checkPlanValidity();

        return $this;
    }

    public function markAsExpired() {
        $this->update([
            'status' => 20,
        ]);

        self::checkPlanValidity();

        return $this;
    }

    public function refund() {
        $this->update( [
            'status' => 30,
        ] );

        self::checkPlanValidity();

        return $this;
    }

    public function checkPlanValidity() {
        $user = $this->user()->with('subscriptions')->first();
        $have_plan = $user->subscriptions()->isActive()->exists();
        $user->update(['membership' => $have_plan ? 1 : 0]);
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
