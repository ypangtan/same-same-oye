<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteBannerClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_banner_id',
        'user_id',
    ];

    public function websiteBanner()
    {
        return $this->belongsTo(WebsiteBanner::class, 'website_banner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
