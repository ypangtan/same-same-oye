<?php

namespace App\Models;

use App\Services\StorageService;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadioSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'default_image',
    ];

    /**
     * Single-row settings — always id 1, created on first use.
     */
    public static function current() {
        return static::firstOrCreate( [ 'id' => 1 ] );
    }

    public function getDefaultImageUrlAttribute() {
        if ( empty( $this->attributes['default_image'] ) ) {
            return null;
        }

        return StorageService::get( $this->attributes['default_image'] );
    }
}
