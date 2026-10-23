<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use App\Services\StorageService;

class FestiveAnimation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'label',
        'file',
        'file_name',
        'file_type',
        'status',
    ];

    public static function resolveFileType( $path ) {

        $extension = strtolower( pathinfo( $path ?? '', PATHINFO_EXTENSION ) );

        if ( $extension == 'gif' ) {
            return 2;
        }

        if ( in_array( $extension, [ 'jpg', 'jpeg', 'png', 'webp', 'bmp', 'svg' ] ) ) {
            return 1;
        }

        if ( in_array( $extension, [ 'mp4', 'mov', 'webm', 'm4v', 'avi', 'mkv', '3gp' ] ) ) {
            return 3;
        }

        return 4;
    }

    public function getFilePathAttribute() {
        if ( $this->attributes['file'] ) {
            $localPath = storage_path( 'app/public/' . $this->attributes['file'] );
            if ( file_exists( $localPath ) ) {
                return asset( 'storage/' . $this->attributes['file'] );
            }

            return StorageService::get( $this->attributes['file'] );
        }
        return null;
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'label',
        'file',
        'file_name',
        'file_type',
        'status',
    ];

    protected static $logName = 'festive_animations';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
