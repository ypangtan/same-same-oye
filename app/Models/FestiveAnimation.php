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
        'duration',
        'status',
    ];

    protected $casts = [
        'duration' => 'float',
    ];

    // Default duration ( seconds ) for image
    const IMAGE_DURATION = 2;

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

    // Duration in seconds: image use IMAGE_DURATION, gif / video read from file, null for others
    public static function resolveDuration( $pathname, $fileType ) {

        try {
            switch ( $fileType ) {
                case 1:
                    return self::IMAGE_DURATION;
                case 2:
                    return self::gifDuration( $pathname );
                case 3:
                    $ffprobe = \FFMpeg\FFProbe::create();
                    return round( (float) $ffprobe->format( $pathname )->get( 'duration' ), 2 );
            }
        } catch ( \Throwable $th ) {
            \Log::warning( 'Festive animation duration failed', [
                'file' => $pathname,
                'error' => $th->getMessage(),
            ] );
        }

        return null;
    }

    // Sum every frame delay ( Graphic Control Extension, unit 1/100s )
    private static function gifDuration( $pathname ) {

        $content = file_get_contents( $pathname );
        if ( $content === false ) {
            return null;
        }

        preg_match_all( '/\x21\xF9\x04[\x00-\xFF]([\x00-\xFF]{2})[\x00-\xFF]\x00/s', $content, $matches );

        $total = 0;
        foreach ( $matches[1] as $delay ) {
            $delay = unpack( 'v', $delay )[1];
            // Browsers treat 0 / 1 as 10 ( 0.1s )
            $total += $delay <= 1 ? 10 : $delay;
        }

        return $total > 0 ? round( $total / 100, 2 ) : null;
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
        'duration',
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
