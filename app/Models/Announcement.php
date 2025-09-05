<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;
use Illuminate\Support\Facades\App;

use Helper;

class Announcement extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'voucher_id',
        'title',
        'description',
        'en_title',
        'en_description',
        'zh_title',
        'zh_description',
        'image',
        'view_once',
        'new_user_only',
        'min_spend',
        'min_order',
        'discount_type',
        'discount_amount',
        'expired_in',
        'status',
        'start_date',
        'expired_date',
        'promo_code',
        'unclaimed_image',
        'claiming_image',
        'claimed_image',
    ];

    public function getTitleAttribute(){
        
        $nowLocale = App::getLocale();

        switch( $nowLocale ) {
            case 'zh':
                return $this->attributes['zh_title'] ?? ( $this->attributes['en_title'] ?? '' );
                break;
            default:
                return $this->attributes['en_title'] ?? $this->attributes['title'];
                break;
        }
    }

    public function getDescriptionAttribute(){
        
        $nowLocale = App::getLocale();

        switch( $nowLocale ) {
            case 'zh':
                return $this->attributes['zh_description'] ?? ( $this->attributes['en_description'] ?? '' );
                break;
            default:
                return $this->attributes['en_description'] ?? $this->attributes['description'];
                break;
        }
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getUnclaimedImagePathAttribute() {
        return $this->attributes['unclaimed_image'] ? asset( 'storage/' . $this->attributes['unclaimed_image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getClaimingImagePathAttribute() {
        return $this->attributes['claiming_image'] ? asset( 'storage/' . $this->attributes['claiming_image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getClaimedImagePathAttribute() {
        return $this->attributes['claimed_image'] ? asset( 'storage/' . $this->attributes['claimed_image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getDiscountTypeLabelAttribute()
    {
        $discountTypes = [
            '1' => __('announcement.percentage'),
            '2' => __('announcement.fixed_amount'),
            '3' => __('announcement.free_cup'),
        ];

        return $discountTypes[$this->attributes['discount_type']] ?? null;
    }
    
    // public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'voucher_id',
        'title',
        'description',
        'en_title',
        'en_description',
        'zh_title',
        'zh_description',
        'image',
        'view_once',
        'new_user_only',
        'min_spend',
        'min_order',
        'discount_type',
        'discount_amount',
        'expired_in',
        'status',
        'start_date',
        'expired_date',
        'promo_code',
        'unclaimed_image',
        'claiming_image',
        'claimed_image',
    ];

    protected static $logName = 'announcements';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} announcement";
    }
}
