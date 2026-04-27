<?php

namespace App\Models;

use App\Services\StorageService;
use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class TrendingContent extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'desc',
        'upload_type',
        'image',
        'file',
        'url',
        'priority',
        'status',
        'publishing_date',
    ];

    public function getPublishingDateAttribute() {
        return $this->attributes['publishing_date'] ? Carbon::parse( $this->attributes['publishing_date'] )->format( 'Y-m-d' ) : null;
    }

    public function getImageUrlAttribute() {
        if( $this->attributes['image'] ) {
            $localPath = storage_path ('app/public/' . $this->attributes['image'] );
            if ( file_exists( $localPath ) ) {
                return asset( 'storage/' . $this->attributes['image'] );
            }

            return StorageService::get( $this->attributes['image'] );
        } else {
            return null;
        }
    }

    public function getSongUrlAttribute() {
        if( $this->attributes['file'] ) {
            $localPath = storage_path ('app/public/' . $this->attributes['file'] );
            if ( file_exists( $localPath ) ) {
                return asset( 'storage/' . $this->attributes['file'] );
            }
            
            return StorageService::get( $this->attributes['file'] );
        } else {
            return '';
        }
    }

    public function getFileUrlAttribute() {
        if( $this->attributes['file'] ) {
            $localPath = storage_path ('app/public/' . $this->attributes['file'] );
            if ( file_exists( $localPath ) ) {
                return asset( 'storage/' . $this->attributes['file'] );
            }

            return StorageService::get( $this->attributes['file'] );
        } else {
            return $this->attributes['url'];
        }
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'title',
        'desc',
        'upload_type',
        'image',
        'file',
        'url',
        'priority',
        'status',
    ];

    protected static $logName = 'trending_contents';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
