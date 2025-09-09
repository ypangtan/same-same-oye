<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class UserVoucher extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'user_id',
        'voucher_id',
        'expired_date',
        'status',
        'redeem_from',
        'total_left',
        'used_at',
        'secret_code',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getUsedAtDateOnlyAttribute()
    {
        return $this->attributes['used_at'] ? $this->attributes['used_at']->format('Y-m-d') : null;
    }

    public function getRedeemFromLabelAttribute()
    {
        $rewardTypes = [
            '1' => __('user.checkin_rewards'),
            '2' => __('user.points_exchange'),
        ];

        return $rewardTypes[$this->attributes['redeem_from']] ?? null;
    }

    public function getVoucherStatusLabelAttribute()
    {

        $statuses = [
            10 => __('voucher.active'),
            20 => __('voucher.used'),
            21 => __('voucher.expired'),
        ];

        return $statuses[$this->attributes['status']] ?? null;
    }

    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'user_id',
        'voucher_id',
        'expired_date',
        'status',
        'redeem_from',
        'total_left',
        'used_at',
        'secret_code',
        'type',
    ];

    protected static $logName = 'user_vouchers';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} user vouchers";
    }
}
