<?php

namespace Database\Factories;

use App\Models\OrderMeta;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderMetaFactory extends Factory
{
    protected $model = OrderMeta::class;

    public function definition()
    {
        return [
            'order_id' => null,
            'product_id' => 2,
            'product_bundle_id' => null,
            'froyos' => "[1]",
            'syrups' => "[1]",
            'toppings' => "[1]",
            'total_price' => 6.97,
            'status' => 10,
        ];
    }
}
