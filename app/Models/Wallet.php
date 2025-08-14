<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;
use Carbon\Carbon;

class Wallet extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'balance',
        'type',
    ];

    public function user() {
        return $this->belongsTo( User::class, 'user_id' );
    }

    public function transactions() {
        return $this->hasMany( WalletTransaction::class, 'user_wallet_id' );
    }

    public function getListingBalanceAttribute() {
        return Helper::numberFormat( $this->attributes['balance'], 2, false );
    }

    public function getToBeExpiredPointsAttribute() {
        $today = Carbon::now('Asia/Kuala_Lumpur')->startOfDay();
    
        $grouped = $this->transactions()
            ->where( 'status', 10 )
            ->where( 'transaction_type', 12 )
            ->whereDate( 'expired_at', '>', $today )
            ->selectRaw( 'expired_at, SUM(amount) as total' )
            ->groupBy( 'expired_at' )
            ->orderBy( 'expired_at', 'asc' )
            ->pluck( 'total', 'expired_at' );
    
        if ( $grouped->isEmpty() ) {
            return [];
        }
    
        // Get the earliest expiry date
        $nextExpiryDate = $grouped->keys()->first();
    
        return [
            $nextExpiryDate => number_format( $grouped->get( $nextExpiryDate ), 2, '.', '' )
        ];
    }

    public function getFormattedTypeAttribute() {
        return Helper::wallets()[$this->attributes['type']];
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'user_id',
        'balance',
        'type',
    ];

    protected static $logName = 'wallets';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} wallet";
    }
}
