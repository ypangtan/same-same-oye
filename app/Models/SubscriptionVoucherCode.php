<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class SubscriptionVoucherCode extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'subscription_voucher_id',
        'user_id',
        'user_notification_id',
        'user_subscription_id',
        'subscription_voucher_url_id',
        'code',
        'claimed_at',
        'status',
    ];

    // Exclude 0 / O / 1 / I to avoid confusion when user type the code
    const CODE_CHARACTERS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    const CODE_LENGTH = 8;

    public static function generateUniqueCode() {

        do {
            $code = '';
            for ( $i = 0; $i < self::CODE_LENGTH; $i++ ) {
                $code .= self::CODE_CHARACTERS[ random_int( 0, strlen( self::CODE_CHARACTERS ) - 1 ) ];
            }
        } while ( self::where( 'code', $code )->exists() || SubscriptionVoucher::where( 'universal_code', $code )->exists() );

        return $code;
    }

    // Not bound to any user & still available, can be given out or deleted
    public function scopeUnassigned( $query ) {
        return $query->whereNull( 'user_id' )->where( 'status', 1 );
    }

    // Bound to user, claimed or voided
    public function scopeUsed( $query ) {
        return $query->where( function( $q ) {
            $q->whereNotNull( 'user_id' )->orWhere( 'status', '!=', 1 );
        } );
    }

    public function voucher() {
        return $this->belongsTo( SubscriptionVoucher::class, 'subscription_voucher_id' );
    }

    public function user() {
        return $this->belongsTo( User::class, 'user_id' );
    }

    public function userNotification() {
        return $this->belongsTo( UserNotification::class, 'user_notification_id' );
    }

    public function userSubscription() {
        return $this->belongsTo( UserSubscription::class, 'user_subscription_id' );
    }

    // Third party voucher url assigned to the user
    public function url() {
        return $this->belongsTo( SubscriptionVoucherUrl::class, 'subscription_voucher_url_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'subscription_voucher_id',
        'user_id',
        'user_notification_id',
        'user_subscription_id',
        'subscription_voucher_url_id',
        'code',
        'claimed_at',
        'status',
    ];

    protected static $logName = 'subscription_voucher_codes';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
