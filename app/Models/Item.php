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
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'add_by',
        'type_id',
        'category_id',
        'title',
        'lyrics',
        'file',
        'image',
        'author',
        'membership_level',
        'file_name',
        'file_type',
        'upload_type',
        'url',
        'status',
    ];

    public function searchItems() {
        return $this->hasMany( SearchItem::class, 'item_id' );
    }

    public function type() {
        return $this->belongsTo( Type::class, 'type_id' );
    }

    public function category() {
        return $this->belongsTo( Category::class, 'category_id' );
    }

    public function administrator() {
        return $this->belongsTo( Administrator::class, 'add_by' );
    }

    public function playlists() {
        return $this->belongsToMany( Playlist::class, 'playlist_items', 'item_id', 'playlist_id' )
            ->where( 'playlists.status', 10 )
            ->withPivot( 'playlist_items.priority' );
    }

    public function playlist() {
        return $this->hasMany( Playlist::class, 'item_id' );
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

    public function getSongUrlAttribute() {
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
        'add_by',
        'type_id',
        'category_id',
        'title',
        'lyrics',
        'file',
        'image',
        'author',
        'membership_level',
        'file_name',
        'file_type',
        'upload_type',
        'url',
        'status',
    ];

    protected static $logName = 'items';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
