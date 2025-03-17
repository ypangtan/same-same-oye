<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;
use Carbon\Carbon;

class ApiRequest extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'endpoint',
        'method',
        'request_body',
        'response_body',
        'api_name',
        'remarks',
        'status',
    ];
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'name', 'description', 'quick_description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'endpoint',
        'method',
        'request_body',
        'response_body',
        'api_name',
        'remarks',
        'status',
    ];

    protected static $logName = 'api_requests';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} api requests";
    }
}
