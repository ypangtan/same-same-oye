<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'banner_id',
        'user_id',
    ];

    public function banner()
    {
        return $this->belongsTo(Banner::class, 'banner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
