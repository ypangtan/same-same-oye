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
        'last_give_birthday_gift',
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
        'platform',
        'age_group',
        'membership',
        'rank_id',
    ];

    public function wallets()
    {
        return $this->hasMany(Wallet::class, 'user_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany( WalletTransaction::class, 'user_id');
    }

    public function getNeedBirthdayPopAnnouncementAttribute() {
        $voucher = UserVoucher::where( 'type', 2 )->where( 'user_id', $this->attributes['id'] )->exists();

        return $voucher;
    }

    public function referral() {
        return $this->belongsTo( User::class, 'referral_id' );
    }

    public function downlines() {
        return $this->hasMany( User::class, 'referral_id', 'id' );
    }

    public function socialLogins() {
        return $this->hasMany( UserSocial::class, 'user_id' );
    }

    public function getReferralCodeAttribute() {
        $referral = $this->referral()->first();

        return $referral ? ( $referral->invitation_code ?? '' ) : null;
    }

    public function getProfilePicturePathNewAttribute() {
        return $this->attributes['profile_picture'] ? asset( 'storage/' . $this->attributes['profile_picture'] ) : asset( 'admin/images/profile_image.png' ) . Helper::assetVersion();
    }

    public function groups() {
        return $this->hasManyThrough( User::class, UserStructure::class, 'referral_id', 'id', 'id', 'user_id' );
    }

    public function uplines() {
        return $this->hasManyThrough( User::class, UserStructure::class, 'user_id', 'id', 'id', 'referral_id' )
            ->orderBy( 'level', 'ASC' );
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
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
        'platform',
        'age_group',
        'membership',
        'rank_id',
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
