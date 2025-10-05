<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class CollectionPlaylist extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'playlist_id',
        'collection_id',
        'priority',
        'status',
    ];

    public function playlist() {
        return $this->belongsTo( Playlist::class, 'playlist_id' );
    }

    public function collection() {
        return $this->belongsTo( Collection::class, 'collection_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'play_list_id',
        'item_id',
        'priority',
        'status',
    ];

    protected static $logName = 'play_list_items';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
