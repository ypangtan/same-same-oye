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

class Playlist extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'add_by',
        'category_id',
        'type_id',
        'en_name',
        'zh_name',
        'image',
        'priority',
        'membership_level',
        'is_item',
        'item_id',
        'file_type',
        'status',
    ];

    public function searchPlaylists() {
        return $this->hasMany( SearchItem::class, 'playlist_id' );
    }

    public function type() {
        return $this->belongsTo( Type::class, 'type_id' );
    }

    public function item() {
        return $this->belongsTo( Item::class, 'item_id' );
    }

    public function category() {
        return $this->belongsToMany( Category::class, 'playlist_categories', 'playlist_id', 'category_id' )
            ->where( 'categories.status', 10 );
    }

    public function collection() {
        return $this->belongsToMany( Collection::class, 'collection_playlists', 'playlist_id', 'collection_id' );
    }

    public function items() {
        $items = $this->belongsToMany( Item::class, 'playlist_items', 'playlist_id', 'item_id' )
            ->where( 'items.status', 10 )
            ->withPivot( 'playlist_items.priority' )
            ->orderBy( 'playlist_items.priority' );

            
        if( !auth()->check() || auth()->user()->membership == 0 && !auth('admin')->check() ) {
            $items->where( 'playlists.membership_level', 0 );
        }

        return $items;
    }

    public function administrator() {
        return $this->belongsTo( Administrator::class, 'add_by' );
    }

    public function getNameAttribute() {
        $locale = app()->getLocale();
        if( $locale == 'zh' ) {
            return $this->attributes['zh_name'] ?? $this->attributes['en_name'];
        } else {
            return $this->attributes['en_name'];
        }
    }

    public function getImageUrlAttribute() {
        if( $this->attributes['image'] ) {
            $localPath = storage_path ('app/public/' . $this->attributes['image'] );
            if ( file_exists( $localPath ) ) {
                return asset( 'storage/' . $this->attributes['image'] );
            }

            return StorageService::get( $this->attributes['image'] );
        } else {
            $item = $this->items()->first();
            if( $item && $item->item ) {
                return $item->item->image_url;
            } else {
                return null;
            }
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
        'category_id',
        'type_id',
        'en_name',
        'zh_name',
        'image',
        'priority',
        'membership_level',
        'is_item',
        'item_id',
        'file_type',
        'status',
    ];

    protected static $logName = 'playlists';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
