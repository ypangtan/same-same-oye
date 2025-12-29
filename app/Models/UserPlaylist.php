<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class UserPlaylist extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'type_id',
        'name',
        'status',
    ];

    public function type() {
        return $this->belongsTo( Type::class, 'type_id' );
    }

    public function items() {
        return $this->belongsToMany( Item::class, 'user_playlist_items', 'user_playlist_id', 'item_id' )
            ->where( 'items.status', 10 )
            ->withPivot( [
                'id'
            ] );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'user_id',
        'type_id',
        'name',
        'status',
    ];

    protected static $logName = 'user_playlists';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
