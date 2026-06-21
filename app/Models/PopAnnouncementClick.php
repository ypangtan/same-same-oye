<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopAnnouncementClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'pop_announcement_id',
        'user_id',
    ];

    public function popAnnouncement()
    {
        return $this->belongsTo(PopAnnouncement::class, 'pop_announcement_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
