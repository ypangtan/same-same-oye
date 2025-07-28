<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSocial extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'identifier',
        'uuid',
    ];

    public function getPlatformLabelAttribute()
    {
        $platforms = [
            '1' => __('user.google'),
            '2' => __('user.facebook'),
            '3' => __('user.apple_id'),
        ];

        return $platforms[$this->attributes['platform']] ?? null;
    }
}
