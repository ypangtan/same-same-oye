<?php

namespace App\Models;
use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Helper;

class StreamLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content_type',
        'radio_id',
        'item_id',
        'playlist_id',
        'collection_id',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }
}
