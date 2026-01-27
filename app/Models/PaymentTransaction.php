<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class PaymentTransaction extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'user_subscription_id',
        'transaction_id',
        'original_transaction_id',
        'amount',
        'product_id',
        'platform',
        'receipt_data',
        'signature',
        'verified_at',
        'verification_response',
        'event_type',
        'status',
    ];

    public function statusList() {
        return [
            10 => 'verified',
            20 => 'failed',
            30 => 'refunded',
        ];
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function subscription() {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id');
    }

    public function markAsVerified( $response = []) {
        $this->update([
            'status' => 10,
            'verified_at' => now(),
            'verification_response' => json_encode($response),
        ]);

        return $this;
    }

    public function markAsFailed( $response = [] ) {
        $this->update( [
            'status' => 20,
            'verification_response' => json_encode($response),
        ] );

        return $this;
    }

    public function markAsRefunded() {
        $this->update( [
            'status' => 30,
        ] );

        return $this;
    }

    public static function exists( $transactionId ) {
        return static::where( 'transaction_id', $transactionId )->exists();
    }

    public function scopeSuccessful( $query ) {
        return $query->where( 'status', 10 );
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
        'transaction_id',
        'original_transaction_id',
        'amount',
        'product_id',
        'platform',
        'receipt_data',
        'signature',
        'verified_at',
        'verification_response',
        'event_type',
        'status',
    ];

    protected static $logName = 'payment_transactions';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
