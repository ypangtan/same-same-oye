<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class SubscriptionVoucherUrl extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'subscription_voucher_id',
        'url',
        'max_users',
        'status',
    ];

    public function voucher() {
        return $this->belongsTo( SubscriptionVoucher::class, 'subscription_voucher_id' );
    }

    // Users assigned this url
    public function codes() {
        return $this->hasMany( SubscriptionVoucherCode::class, 'subscription_voucher_url_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'subscription_voucher_id',
        'url',
        'max_users',
        'status',
    ];

    protected static $logName = 'subscription_voucher_urls';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
