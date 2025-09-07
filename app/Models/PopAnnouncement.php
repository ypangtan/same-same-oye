<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class PopAnnouncement extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'en_title',
        'zh_title',
        'image',
        'en_text',
        'zh_text',
        'status',
    ];

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : null;
    }

    public function getTitleAttribute(){
        
        $nowLocale = App::getLocale();

        switch( $nowLocale ) {
            case 'zh':
                return $this->attributes['zh_title'] ?? ( $this->attributes['en_title'] ?? '' );
                break;
            default:
                return $this->attributes['en_title'] ?? '-';
                break;
        }
    }

    public function getTextAttribute(){
        
        $nowLocale = App::getLocale();

        switch( $nowLocale ) {
            case 'zh':
                return $this->attributes['zh_text'] ?? ( $this->attributes['en_text'] ?? '' );
                break;
            default:
                return $this->attributes['en_text'] ?? '-';
                break;
        }
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'en_title',
        'zh_title',
        'image',
        'en_text',
        'zh_text',
        'status',
    ];

    protected static $logName = 'pop_announcements';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
