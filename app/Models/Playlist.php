<?php

namespace App\Models;

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
        'en_name',
        'zh_name',
        'image',
        'priority',
        'membership_level',
        'is_item',
        'item_id',
        'status',
    ];

    public function item() {
        return $this->belongsTo( Item::class, 'item_id' );
    }

    public function category() {
        return $this->belongsTo( Category::class, 'category_id' );
    }

    public function collection() {
        return $this->belongsToMany( Collection::class, 'collection_playlists', 'playlist_id', 'collection_id' );
    }

    public function items() {
        return $this->belongsToMany( Item::class, 'playlist_items', 'playlist_id', 'item_id' )
            ->where( 'items.status', 10 )
            ->withPivot( 'playlist_items.priority' )
            ->orderBy( 'playlist_items.priority' );
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
            return asset( 'storage/' . $this->attributes['image'] );
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
        'en_name',
        'zh_name',
        'image',
        'priority',
        'membership_level',
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
