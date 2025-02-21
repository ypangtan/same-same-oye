<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class TopupRecord extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'wallet_transaction_id',
        'reference',
        'amount',
        'payment_url',
        'payment_attempt',
        'status',
        'is_processed',
    ];

    public function user() {
        return $this->belongsTo( User::class, 'user_id' );
    }

    public function walletTransaction() {
        return $this->belongsTo( WalletTransaction::class, 'wallet_transaction_id' );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'user_id',
        'wallet_transaction_id',
        'reference',
        'amount',
        'payment_url',
        'payment_attempt',
        'status',
        'is_processed',
    ];

    protected static $logName = 'topup_record';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} topup records";
    }
}
