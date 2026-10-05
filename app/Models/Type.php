<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Type extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'en_name',
        'zh_name',
        'status',
    ];

    protected $appends = [
        'name',
    ];

    // Show Podcast as Video, only turned on by get types v2 API
    public static $showPodcastAsVideo = false;

    public function getEnNameAttribute( $value ) {
        if ( self::$showPodcastAsVideo && $value == 'Podcast' ) {
            return 'Video';
        }
        return $value;
    }

    public function getNameAttribute() {
        $locale = app()->getLocale();
        if( $locale == 'zh' ) {
            return $this->attributes['zh_name'] ?? $this->en_name;
        } else {
            return $this->en_name;
        }
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
        'image',
        'color',
        'status',
    ];

    protected static $logName = 'types';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
