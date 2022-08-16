<?php

namespace Armezit\GetCandy\PurchaseLimit\Tests\Unit\Models;

use Armezit\GetCandy\PurchaseLimit\Models\PurchaseLimit;
use Armezit\GetCandy\PurchaseLimit\Tests\TestCase;
use GetCandy\Models\Customer;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PurchaseLimitTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_can_make_a_purchase_limit_with_minimum_attributes()
    {
        $limit = [];
        PurchaseLimit::create($limit);

        $this->assertDatabaseHas('purchase_limits', $limit);
    }

    public function test_can_make_a_purchase_limit()
    {
        $limit = [
            'product_variant_id' => $this->faker->numberBetween(1, 1000),
            'product_id' => $this->faker->numberBetween(1, 1000),
            'customer_group_id' => $this->faker->numberBetween(1, 1000),
            'customer_id' => $this->faker->numberBetween(1, 1000),
            'period' => $this->faker->numberBetween(1, 10),
            'max_quantity' => $this->faker->numberBetween(1, 10),
            'max_total' => $this->faker->numberBetween(1, 1000),
            'starts_at' => $this->faker->date(),
            'ends_at' => $this->faker->date(),
        ];
        PurchaseLimit::create($limit);

        $this->assertDatabaseHas('purchase_limits', $limit);
    }

    public function test_can_associate_to_product()
    {
        $product = Product::factory()->create();
        $purchaseLimit = PurchaseLimit::factory()->create([
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(Product::class, $purchaseLimit->product);
    }

    public function test_can_associate_to_product_variant()
    {
        $productVariant = ProductVariant::factory()->create();
        $purchaseLimit = PurchaseLimit::factory()->create([
            'product_variant_id' => $productVariant->id,
        ]);

        $this->assertInstanceOf(ProductVariant::class, $purchaseLimit->productVariant);
    }

    public function test_can_associate_to_customer()
    {
        $customer = Customer::factory()->create();
        $purchaseLimit = PurchaseLimit::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $this->assertInstanceOf(Customer::class, $purchaseLimit->customer);
    }

    public function test_can_associate_to_customer_group()
    {
        $customerGroup = CustomerGroup::factory()->create();
        $purchaseLimit = PurchaseLimit::factory()->create([
            'customer_group_id' => $customerGroup->id,
        ]);

        $this->assertInstanceOf(CustomerGroup::class, $purchaseLimit->customerGroup);
    }
}
