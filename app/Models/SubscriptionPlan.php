<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class SubscriptionPlan extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_in_days',
        'duration_in_months',
        'duration_in_years',
        'ios_product_id',
        'android_product_id',
        'huawei_product_id',
        'status',
    ];
    
    public function getProductIdForPlatform( $platform ) {
        return match( $platform ) {
            '1' => $this->ios_product_id,
            '2' => $this->android_product_id,
            '3' => $this->huawei_product_id,
            default => null,
        };
    }

    public static function findByPlatformProductId( $platform, $productId) {
        $column = match( $platform ) {
            '1' => 'ios_product_id',
            '2' => 'android_product_id',
            '3' => 'huawei_product_id',
            default => null,
        };

        if (!$column) {
            return null;
        }

        return static::where($column, $productId)->where('status', 10)->first();
    }

    public function subscriptions() {
        return $this->hasMany(UserSubscription::class);
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'name',
        'description',
        'price',
        'duration_in_days',
        'duration_in_months',
        'duration_in_years',
        'ios_product_id',
        'android_product_id',
        'huawei_product_id',
        'status',
    ];

    protected static $logName = 'subscription_plans';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
