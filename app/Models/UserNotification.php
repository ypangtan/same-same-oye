<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

// use App\Traits\HasTranslations;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Helper;

class UserNotification extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'system_title',
        'system_content',
        'meta_data',
        'url_slug',
        'image',
        'type',
        'is_broadcast',
        'key',
    ];

    protected $appends = [
        'path',
        'encrypted_id',
    ];

    public function setTitleAttribute($value)
    {
        $languages = array_keys(Config::get('languages'));

        $translations = [];
        foreach ($languages as $lang) {
            App::setLocale($lang);
            $translations[$lang] = __($value);
        }

        $this->attributes['title'] = json_encode($translations);
    }

    public function setContentAttribute($value)
    {
        $languages = array_keys(Config::get('languages'));

        $translations = [];
        foreach ($languages as $lang) {
            App::setLocale($lang);
            $translations[$lang] = __($value);
        }

        $this->attributes['content'] = json_encode($translations);
    }

    public function getContentAttribute($value)
    {
        $translations = json_decode($value, true) ?? [];


        // Return translation for the current locale or fallback to default
        return $translations;
    }

    public function getTitleAttribute($value)
    {
        $translations = json_decode($value, true) ?? [];


        // Return translation for the current locale or fallback to default
        return $translations;
    }

    public function UserNotificationUsers() {
        return $this->hasMany( UserNotificationUser::class, 'user_notification_id' );
    }

    public function user() {
        return $this->belongsTo( User::class, 'user_id' )->withTrashed();
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : null;
    }

    public function getPathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : null;
    }

    public function getDisplayStatusAttribute() {

        $status = [
            '1' => __( 'datatables.pending' ),
            '10' => __( 'datatables.published' ),
            '20' => __( 'promotion.unpublished' ),
        ];

        return $status[ $this->attributes['status'] ];
    }

    // public function getContentAttribute($value)
    // {
    //     $user = request()->user();

    //     $value = str_replace('{username}', $user->username ?? null, $value);
    //     $value = str_replace('{fullname}', $user->fullname ?? null, $value);
    //     $value = str_replace('{phone_number}', $user->phone_number ?? null, $value);
    //     $value = str_replace('{email}', $user->email ?? null, $value);

    //     return $value;
    // }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getSystemTitleAttribute() {
        $metaData = json_decode( $this->attributes['meta_data'], true );
        return __( $this->attributes['system_title'], $metaData ? $metaData : [] );
    }

    public function getSystemContentAttribute() {
        $metaData = json_decode( $this->attributes['meta_data'], true );
        return __( $this->attributes['system_content'], $metaData ? $metaData : [] );
    }

    // public $translatable = [ 'title', 'content' ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'user_id',
        'title',
        'content',
        'system_title',
        'system_content',
        'meta_data',
        'url_slug',
        'image',
        'type',
        'is_broadcast',
        'key',
    ];

    protected static $logName = 'user_notifications';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} user notification";
    }
}
