<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class SalesRecord extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'order_id',
        'customer_name',
        'facebook_name',
        'facebook_url',
        'live_id',
        'product_metas',
        'total_price',
        'payment_method',
        'handler',
        'remarks',
        'reference',
        'status',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'order_id',
        'customer_name',
        'facebook_name',
        'facebook_url',
        'live_id',
        'product_metas',
        'total_price',
        'payment_method',
        'handler',
        'remarks',
        'reference',
        'status',
    ];

    protected static $logName = 'sales_record';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} sales record";
    }
}
