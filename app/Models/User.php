<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class User extends Model
{
    use HasFactory, LogsActivity, HasApiTokens;

    protected $hidden = ['password'];

    protected $fillable = [
        'username',
        'fullname',
        'email',
        'email_verified_at',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'calling_code',
        'status',
        'phone_number',
        'account_type',
        'date_of_birth',
        'check_in_streak',
        'total_check_in',        
        'referral_id',
        'invitation_code',
        'referral_structure',
        'profile_picture',
        'first_name',
        'last_name',
        'is_social_account',
    ];

    public function wallets()
    {
        return $this->hasMany(Wallet::class, 'user_id');
    }

    public function referral() {
        return $this->hasOne( User::class, 'id', 'referral_id' );
    }

    public function downlines() {
        return $this->hasMany( User::class, 'referral_id', 'id' );
    }

    public function getProfilePicturePathAttribute() {
        return $this->attributes['profile_picture'] ? asset( 'storage/' . $this->attributes['profile_picture'] ) : asset( 'admin/images/profile_image.png' ) . Helper::assetVersion();
    }

    public function groups() {
        return $this->hasManyThrough( User::class, UserStructure::class, 'referral_id', 'id', 'id', 'user_id' );
    }

    public function uplines() {
        return $this->hasManyThrough( User::class, UserStructure::class, 'user_id', 'id', 'id', 'referral_id' )
            ->orderBy( 'level', 'ASC' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'username',
        'fullname',
        'email',
        'email_verified_at',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'calling_code',
        'status',
        'phone_number',
        'account_type',
        'date_of_birth',
        'check_in_streak',
        'total_check_in',        
        'referral_id',
        'invitation_code',
        'referral_structure',
        'profile_picture',
        'first_name',
        'last_name',
        'is_social_account',
    ];

    protected static $logName = 'users';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} user";
    }
}
