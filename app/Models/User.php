<?php

namespace App\Models;

use App\Services\StorageService;
use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class User extends Model implements AuthenticatableContract
{
    use HasFactory, LogsActivity, HasApiTokens, Authenticatable;

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
        'nationality',
        'membership',
        'rank_id',
        'is_first_login',
    ];

    public function subscriptionGroup() {
        return $this->hasMany( SubscriptionGroupMember::class, 'leader_id' );
    }

    public function subscriptions()
    {
        return $this->hasMany( UserSubscription::class, 'user_id' );
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class, 'user_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany( WalletTransaction::class, 'user_id');
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
        
        if( $this->attributes['profile_picture'] ) {
            $localPath = storage_path ('app/public/' . $this->attributes['profile_picture'] );
            if ( file_exists( $localPath ) ) {
                return asset( 'storage/' . $this->attributes['profile_picture'] );
            }

            return StorageService::get( $this->attributes['profile_picture'] );
        } else {
            return asset( 'admin/images/profile_image.png' );
        }
    }

    public function checkPlanValidity() {
        $have_plan = $this->subscriptions()->isActive()->first();
        if( $have_plan ) {
            $this->update( [ 'membership' => $have_plan->type ] );
            return ;
        } else {
            // check group plan
            $group = $this->subscriptionGroup()->first();
            if( $group ) {
                // check if leader have active plan
                $plan = $group->leader()->subscriptions()->isGroup()->isActive()->first();
                if( $plan ) {
                    $this->update( [ 'membership' => $plan->type ] );
                    return ;
                } else {
                    // remove from group if leader plan expired
                    // $group->delete();
                }
            }
        }

        $this->update( [ 'membership' => 0 ] );
        return ;
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
        'nationality',
        'membership',
        'rank_id',
        'is_first_login',
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
