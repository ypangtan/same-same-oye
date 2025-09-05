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

class Voucher extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'promo_code',
        'title',
        'description',
        'en_title',
        'en_description',
        'zh_title',
        'zh_description',
        'image',
        'start_date',
        'expired_date',
        'discount_type',
        'discount_amount',
        'type',
        'status',
        'usable_amount',
        'points_required',
        'min_spend',
        'min_order',
        'buy_x_get_y_adjustment',
        'total_claimable',
        'validity_days',
        'claim_per_user',
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

    public function announcement()
    {
        return $this->hasOne( Announcement::class, 'voucher_id' );
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }
    
    public function getDecodedAdjustmentAttribute()
    {
        if (!$this->attributes['buy_x_get_y_adjustment']) {
            return null;
        }
    
        $adjustment = json_decode($this->attributes['buy_x_get_y_adjustment'], true);

        $adjustment['discount_type'] = $this->discount_type_label;

        if (isset($adjustment['buy_products']) && is_array($adjustment['buy_products'])) {

            $products = Product::whereIn('id', $adjustment['buy_products'])->get(['id', 'title']);
    
            $adjustment['buy_products_info'] = $products->toArray();
            $adjustment['buy_products_info'] = $products->toArray();
        }
    
        if (isset($adjustment['get_product'])) {
            $getProduct = Product::find($adjustment['get_product'], ['id', 'title']);
    
            if ($getProduct) {
                $adjustment['get_product_info'] = $getProduct->toArray();
            }
        }
    
        return $adjustment;
    }

    public function getDiscountTypeLabelAttribute()
    {
        $discountTypes = [
            '1' => __('voucher.percentage'),
            '2' => __('voucher.fixed_amount'),
            '3' => __('voucher.free_cup'),
        ];

        return $discountTypes[$this->attributes['discount_type']] ?? null;
    }

    public function getVoucherTypeLabelAttribute()
    {
        $discountTypes = [
            '1' => __('voucher.public_voucher'),
            '2' => __('voucher.user_specific_voucher'),
        ];

        return $discountTypes[$this->attributes['type']] ?? null;
    }

    public function getVoucherTypeAttribute()
    {
        return $this->attributes['type'] ?? null;
    }
    
    // public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'promo_code',
        'title',
        'description',
        'en_title',
        'en_description',
        'zh_title',
        'zh_description',
        'image',
        'start_date',
        'expired_date',
        'discount_type',
        'discount_amount',
        'type',
        'status',
        'usable_amount',
        'points_required',
        'min_spend',
        'min_order',
        'buy_x_get_y_adjustment',
        'total_claimable',
        'validity_days',
        'claim_per_user',
    ];

    protected static $logName = 'vouchers';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} voucher";
    }
}
