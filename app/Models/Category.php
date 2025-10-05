<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Category extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'en_name',
        'zh_name',
        'status',
    ];

    public function getNameAttribute() {
        $locale = app()->getLocale();
        if( $locale == 'zh' ) {
            return $this->attributes['zh_name'] ?? $this->attributes['en_name'];
        } else {
            return $this->attributes['en_name'];
        }
    }

    public function collections() {
        return $this->hasMany( Collection::class, 'category_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'en_name',
        'zh_name',
        'status',
    ];

    protected static $logName = 'categories';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
