<?php

namespace Armezit\Lunar\PurchaseLimit\Tests\Unit\Rules;

use Armezit\Lunar\PurchaseLimit\Exceptions\ProductQuantityLimitException;
use Armezit\Lunar\PurchaseLimit\Exceptions\ProductTotalLimitException;
use Armezit\Lunar\PurchaseLimit\Models\PurchaseLimit;
use Armezit\Lunar\PurchaseLimit\Rules\ProductLimit;
use Armezit\Lunar\PurchaseLimit\Tests\TestCase;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ProductLimitTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_query_builder_is_correct()
    {
        $products = Product::factory()->count(2)->create();

        $productVariants = ProductVariant::factory()
            ->count(4)
            ->state(new Sequence(
                ['product_id' => $products[0]->id],
                ['product_id' => $products[1]->id],
            ))
            ->create();

        $cart = Cart::factory()->create();
        $cart->lines()->createMany([
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[0]->id,
                'quantity' => 1,
            ],
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[1]->id,
                'quantity' => 3,
            ],
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[2]->id,
                'quantity' => 5,
            ],
        ]);

        PurchaseLimit::factory()
                     ->count(4)
                     ->state(new Sequence(
                         ['product_id' => $products[0]->id],
                         ['product_id' => $products[1]->id],
                         ['product_variant_id' => $productVariants[0]->id],
                         ['customer_id' => $this->faker->numberBetween(1, 5)],
                     ))
                     ->create();

        $query = PurchaseLimit::withoutTrashed()->where(function ($q) use ($cart) {
            (new ProductLimit)->query($q, $cart);
        });

        $this->assertEquals(2, $query->count());
    }

    public function test_collection_filter_is_correct()
    {
        $products = Product::factory()->count(2)->create();

        $productVariants = ProductVariant::factory()
            ->count(4)
            ->state(new Sequence(
                ['product_id' => $products[0]->id],
                ['product_id' => $products[1]->id],
            ))
            ->create();

        $cart = Cart::factory()->create();
        $cart->lines()->createMany([
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[0]->id,
                'quantity' => 1,
            ],
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[1]->id,
                'quantity' => 3,
            ],
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[2]->id,
                'quantity' => 5,
            ],
        ]);

        PurchaseLimit::factory()
                     ->count(4)
                     ->state(new Sequence(
                         ['product_id' => $products[0]->id],
                         ['product_id' => $products[1]->id],
                         ['product_variant_id' => $productVariants[0]->id],
                         ['customer_id' => $this->faker->numberBetween(1, 5)],
                     ))
                     ->create();

        $limits = PurchaseLimit::withoutTrashed()->get();
        $limits = (new ProductLimit)->filter($limits, $cart);

        $this->assertCount(2, $limits);
    }

    public function test_throws_exception_on_max_quantity_limit_exceed()
    {
        $product = Product::factory()->create();

        $productVariants = ProductVariant::factory()
            ->count(2)
            ->create([
                'product_id' => $product->id,
            ]);

        $cart = Cart::factory()->create();
        $cart->lines()->createMany([
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[0]->id,
                'quantity' => 2,
            ],
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[1]->id,
                'quantity' => 3,
            ],
        ]);

        PurchaseLimit::factory()->create([
            'product_id' => $product->id,
            'max_quantity' => 4,
        ]);

        $limits = PurchaseLimit::withoutTrashed()->get();

        $this->expectException(ProductQuantityLimitException::class);
        (new ProductLimit)->execute($limits, $cart);
    }

    public function test_throws_exception_on_max_total_limit_exceed()
    {
        $product = Product::factory()->create();

        $productVariants = ProductVariant::factory()
            ->count(2)
            ->create([
                'product_id' => $product->id,
            ]);

        $currency = Currency::factory()->create([
            'decimal_places' => 0,
        ]);

        Price::factory()->createMany([
            [
                'price' => 60,
                'currency_id' => $currency->id,
                'tier' => 1,
                'priceable_type' => ProductVariant::class,
                'priceable_id' => $productVariants[0]->id,
            ],
            [
                'price' => 80,
                'currency_id' => $currency->id,
                'tier' => 1,
                'priceable_type' => ProductVariant::class,
                'priceable_id' => $productVariants[1]->id,
            ],
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);
        $cart->lines()->createMany([
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[0]->id,
                'quantity' => 2,
            ],
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[1]->id,
                'quantity' => 3,
            ],
        ]);
        $cart = $cart->getManager()->getCart();

        PurchaseLimit::factory()->create([
            'product_id' => $product->id,
            'max_total' => 300,
        ]);

        $limits = PurchaseLimit::withoutTrashed()->get();

        $this->expectException(ProductTotalLimitException::class);
        (new ProductLimit)->execute($limits, $cart);
    }
}
