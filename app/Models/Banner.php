<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class Banner extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'voucher_id',
        'title',
        'description',
        'image',
        'sequence',
        'status',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
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
            '1' => __('banner.percentage'),
            '2' => __('banner.fixed_amount'),
            '3' => __('banner.free_cup'),
        ];

        return $discountTypes[$this->attributes['discount_type']] ?? null;
    }

    public function getBannerTypeLabelAttribute()
    {
        $discountTypes = [
            '1' => __('banner.public_banner'),
            '2' => __('banner.user_specific_banner'),
        ];

        return $discountTypes[$this->attributes['type']] ?? null;
    }

    public function getBannerTypeAttribute()
    {
        return $this->attributes['type'] ?? null;
    }
    
    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'voucher_id',
        'title',
        'description',
        'image',
        'sequence',
        'status',
    ];

    protected static $logName = 'banners';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} banner";
    }
}
