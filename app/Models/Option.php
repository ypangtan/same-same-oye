<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'option_name',
        'option_value',
    ];

    public static function getRegisterBonusSettings(){
        return self::where('option_name', 'REGISTER_BONUS')->first();
    }

    public static function getReferralBonusSettings(){
        return self::where('option_name', 'REFERRAL_REGISTER')->first();
    }

    public static function getReferralSpendingSettings(){
        return self::where('option_name', 'REFERRAL_SPENDING')->first();
    }

    public static function getSpendingSettings(){
        return self::where('option_name', 'CONVERTION_RATE')->first();
    }

    public static function getTaxesSettings(){
        return self::where('option_name', 'TAXES')->first();
    }

    public static function getSpecialOtpSettings(){
        return self::where('option_name', 'SPECIAL_REGISTRATION_OTP')->first();
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }
}
