<?php

namespace App\Models;

use App\Services\StorageService;
use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class RadioQueueItem extends Model
{
    use HasFactory, LogsActivity;

    const STATUS_QUEUED = 10;
    const STATUS_RESERVED = 20;
    const STATUS_PLAYED = 30;

    protected $fillable = [
        'title',
        'file',
        'file_name',
        'image',
        'duration',
        'position',
        'status',
        'add_by',
        'reserved_at',
        'played_at',
    ];

    // Eloquent only auto-casts created_at/updated_at by default — without this, reserved_at and
    // played_at come back as plain strings (not Carbon), which both breaks ->timezone() calls on
    // them and skips serializeDate() below, so they'd render in raw UTC instead of KL time.
    protected $casts = [
        'reserved_at' => 'datetime',
        'played_at' => 'datetime',
    ];

    public function administrator() {
        return $this->belongsTo( Administrator::class, 'add_by' );
    }

    public function getFileUrlAttribute() {
        if( empty( $this->attributes['file'] ) ) {
            return null;
        }

        return StorageService::get( $this->attributes['file'] );
    }

    public function getImageUrlAttribute() {
        if( empty( $this->attributes['image'] ) ) {
            return null;
        }

        return StorageService::get( $this->attributes['image'] );
    }

    public function getDisplayDurationAttribute() {

        if( !$this->attributes['duration'] ) {
            return '-';
        }

        $minutes = floor( $this->attributes['duration'] / 60 );
        $seconds = $this->attributes['duration'] % 60;

        return sprintf( '%d:%02d', $minutes, $seconds );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'title',
        'file',
        'file_name',
        'duration',
        'position',
        'status',
        'add_by',
        'played_at',
    ];

    protected static $logName = 'radio_queue_items';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
