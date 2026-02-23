<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class SubscriptionGroupMember extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'user_subscription_id',
    ];

    public function user() {
        return $this->belongsTo( User::class, 'user_id' );
    }

    public function userSubscription() {
        return $this->belongsTo( UserSubscription::class, 'user_subscription_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'user_id',
        'user_subscription_id',
    ];

    protected static $logName = 'SubscriptionGroupMember';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
