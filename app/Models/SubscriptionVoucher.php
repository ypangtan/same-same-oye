<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class SubscriptionVoucher extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'type',
        'description',
        'duration_in_months',
        'quantity',
        'universal_code',
        'start_date',
        'end_date',
        'status',
    ];

    // Subscription: user claim code to get free subscription
    const TYPE_SUBSCRIPTION = 1;

    // Third party: user only receive url provided by third party, no claim
    const TYPE_THIRD_PARTY = 2;

    public function codes() {
        return $this->hasMany( SubscriptionVoucherCode::class, 'subscription_voucher_id' );
    }

    public function urls() {
        return $this->hasMany( SubscriptionVoucherUrl::class, 'subscription_voucher_id' );
    }

    public function isThirdParty() {
        return $this->attributes['type'] == self::TYPE_THIRD_PARTY;
    }

    public function notifications() {
        return $this->hasMany( UserNotification::class, 'subscription_voucher_id' );
    }

    public function getStartDateAttribute() {
        return $this->attributes['start_date'] ? Carbon::parse( $this->attributes['start_date'] )->format( 'Y-m-d H:i' ) : null;
    }

    public function getEndDateAttribute() {
        return $this->attributes['end_date'] ? Carbon::parse( $this->attributes['end_date'] )->format( 'Y-m-d H:i' ) : null;
    }

    // Codes are pre-generated, remaining = codes not used yet ( used include voided )
    public function remainingQuantity() {
        return max( 0, $this->attributes['quantity'] - $this->codes()->used()->count() );
    }

    public function isClaimable() {

        if ( $this->attributes['status'] != 10 ) {
            return false;
        }

        $now = Carbon::now( 'Asia/Kuala_Lumpur' );

        if ( $this->attributes['start_date'] && $now->lt( Carbon::parse( $this->attributes['start_date'], 'Asia/Kuala_Lumpur' ) ) ) {
            return false;
        }

        if ( $this->attributes['end_date'] && $now->gt( Carbon::parse( $this->attributes['end_date'], 'Asia/Kuala_Lumpur' ) ) ) {
            return false;
        }

        return true;
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'name',
        'type',
        'description',
        'duration_in_months',
        'quantity',
        'universal_code',
        'start_date',
        'end_date',
        'status',
    ];

    protected static $logName = 'subscription_vouchers';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
