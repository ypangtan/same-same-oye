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
        'max_member',
        'status',
    ];
    
    public function getProductIdForPlatform( $platform ) {
        switch( $platform ) {
            case '1':
                return $this->ios_product_id;
            case '2':
                return $this->android_product_id;
            case '3':
                return $this->huawei_product_id;
            default:
                return null;
        }
    }

    public static function findByPlatformProductId( $platform, $productId) {
        $column = null;
        switch( $platform ) {
            case '1':
                $column = 'ios_product_id';
                break;
            case '2':
                $column = 'android_product_id';
                break;
            case '3':
                $column = 'huawei_product_id';
                break;
        }

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
        'max_member',
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
