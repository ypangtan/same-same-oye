<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderMeta;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'product_id' => null,
            'product_bundle_id' => null,
            'outlet_id' => null,
            'user_id' => 40, // Change this dynamically if needed
            'vending_machine_id' => 1, // Update accordingly
            'total_price' => 6.97,
            'discount' => 0,
            'reference' => null,
            'payment_method' => 1,
            'status' => 3,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Order $order) {

            $order->update([
                'reference' => Helper::generateOrderReference() . $order->id,
            ]);
            
            OrderMeta::factory()->count(1)->create([
                'order_id' => $order->id,
            ]);
        });
    }
}
