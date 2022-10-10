<?php

namespace Armezit\Lunar\PurchaseLimit\Database\Factories;

use Armezit\Lunar\PurchaseLimit\Models\PurchaseLimit;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseLimitFactory extends Factory
{
    protected $model = PurchaseLimit::class;

    public function definition()
    {
        return [
            'product_id' => 0,
            'product_variant_id' => 0,
            'customer_group_id' => 0,
            'customer_id' => 0,
            'period' => null,
            'max_quantity' => null,
            'max_total' => null,
        ];
    }
}
