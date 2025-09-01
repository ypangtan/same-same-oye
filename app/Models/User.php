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
        'platform',
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

    public function getTotalSpendingAttribute()
    {
        $totalPoints = $this->hasMany(WalletTransaction::class, 'user_id')
            ->where('transaction_type', 12)
            ->whereHas('invoice') // Ensure only transactions with invoices are counted
            ->with('invoice')
            ->get()
            ->sum(function ($transaction) {
                return $transaction->invoice->total_price ?? 0;
            });
    
        return Helper::numberFormat($totalPoints, 2);
    }

    public function getTotalAccumulateSpendingAttribute()
    {
        $totalPoints = $this->hasMany(WalletTransaction::class, 'user_id')
        ->where('transaction_type', 12)
        ->whereHas('invoice') // Ensure only transactions with invoices are counted
        ->with('invoice')
        ->get()
        ->sum(function ($transaction) {
            return $transaction->invoice->total_price ?? 0;
        });

        return Helper::numberFormat($totalPoints, 2);
    }

    public function getCurrentRankAttribute()
    {
        $totalPoints = $this->hasMany(WalletTransaction::class, 'user_id')
        ->where('transaction_type', 12)
        ->whereHas('invoice') // Ensure only transactions with invoices are counted
        ->with('invoice')
        ->get()
        ->sum(function ($transaction) {
            return $transaction->invoice->total_price ?? 0;
        });

        $rank = Rank::where('min_target', '<=', $totalPoints)
            ->where( 'status', 10 )
            ->orderBy('priority', 'DESC')
            ->first();

        return $rank ? $rank->title : 'Member';
    
        // if ($totalPoints >= 100000) {
        //     return 'Premium';
        // } elseif ($totalPoints >= 10000) {
        //     return 'Gold';
        // } elseif ($totalPoints >= 1000) {
        //     return 'Silver';
        // }
    
        // return 'Member';
    }
    
    public function getRequiredPointsAttribute()
    {
        $totalPoints = $this->hasMany(WalletTransaction::class, 'user_id')
        ->where('transaction_type', 12)
        ->whereHas('invoice') // Ensure only transactions with invoices are counted
        ->with('invoice')
        ->get()
        ->sum(function ($transaction) {
            return $transaction->invoice->total_price ?? 0;
        });
    
        $rank = Rank::where( 'status', 10 )
            ->orderBy('priority', 'ASC')
            ->first();

        $data = [];
        
        foreach ( $rank as $v ) {
            $data[$v->title] = [
                'current_points' => \Helper::numberFormat( $totalPoints, 2 ),
                'required_points'  => Helper::numberFormat( ( ($v->min_target - $totalPoints > 0) ? $v->min_target - $totalPoints : 0), 2 ),
                'next_level_target'  => $v->min_target,
            ];
        }

        // return [
        //     'Member'  => [
        //         'current_points' => Helper::numberFormat( $totalPoints, 2 ),
        //         'required_points'  => Helper::numberFormat( ( (1000 - $totalPoints > 0) ? 1000 - $totalPoints : 0), 2 ),
        //         'next_level_target'  => 1000,
        //     ],
        //     'Silver'  => [
        //         'current_points' => Helper::numberFormat( $totalPoints, 2 ),
        //         'required_points'  => Helper::numberFormat( ( (9999 - $totalPoints > 0) ? 9999 - $totalPoints : 0), 2 ),
        //         'next_level_target'  => 9999,
        //     ],
        //     'Gold'    => [
        //         'current_points' => Helper::numberFormat( $totalPoints, 2 ),
        //         'required_points'  => Helper::numberFormat( ( (99999 - $totalPoints > 0) ? 99999 - $totalPoints : 0), 2 ),
        //         'next_level_target'  => 99999,
        //     ],
        //     'Premium' => [
        //         'current_points' => Helper::numberFormat( $totalPoints, 2 ),
        //         'required_points'  => Helper::numberFormat( ( (1000000 - $totalPoints > 0) ? 1000000 - $totalPoints : 0), 2 ),
        //         'next_level_target'  => 1000000,
        //     ],
        // ];
    }

    public function referral() {
        return $this->hasOne( User::class, 'id', 'referral_id' );
    }

    public function downlines() {
        return $this->hasMany( User::class, 'referral_id', 'id' );
    }

    public function socialLogins() {
        return $this->hasMany( UserSocial::class, 'user_id' );
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
