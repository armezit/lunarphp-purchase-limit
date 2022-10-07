<?php

namespace Armezit\Lunar\PurchaseLimit\Tests\Unit\Rules;

use Armezit\Lunar\PurchaseLimit\Exceptions\ProductVariantQuantityLimitException;
use Armezit\Lunar\PurchaseLimit\Exceptions\ProductVariantTotalLimitException;
use Armezit\Lunar\PurchaseLimit\Models\PurchaseLimit;
use Armezit\Lunar\PurchaseLimit\Rules\ProductVariantLimit;
use Armezit\Lunar\PurchaseLimit\Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Lunar\Actions\Carts\CalculateLine;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;

class ProductVariantLimitTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_query_builder_is_correct()
    {
        $productVariant = ProductVariant::factory()->create();

        $cartLine = CartLine::factory()->create([
            'purchasable_id' => $productVariant->id,
        ]);

        PurchaseLimit::factory()
            ->count(2)
            ->state(new Sequence(
                ['product_variant_id' => $productVariant->id],
                ['product_variant_id' => 0],
            ))
            ->create();

        $query = PurchaseLimit::withoutTrashed()->where(function ($q) use ($cartLine) {
            (new ProductVariantLimit)->query($q, $cartLine);
        });

        $this->assertEquals(1, $query->count());
    }

    public function test_collection_filter_is_correct()
    {
        $productVariant = ProductVariant::factory()->create();

        $cartLine = CartLine::factory()->create([
            'purchasable_id' => $productVariant->id,
        ]);

        PurchaseLimit::factory()
            ->count(2)
            ->state(new Sequence(
                ['product_variant_id' => $productVariant->id],
                [
                    'product_variant_id' => $productVariant->id,
                    'customer_id' => $this->faker->numberBetween(1, 5),
                ],
                ['product_variant_id' => null],
            ))
            ->create();

        $limits = PurchaseLimit::withoutTrashed()->get();
        $limits = (new ProductVariantLimit)->filter($limits, $cartLine);

        $this->assertCount(1, $limits);
    }

    public function test_throws_exception_on_max_quantity_limit_exceed()
    {
        $productVariant = ProductVariant::factory()->create();

        $cartLine = CartLine::factory()->create([
            'purchasable_id' => $productVariant->id,
            'quantity' => 5,
        ]);

        PurchaseLimit::factory()->create([
            'product_variant_id' => $productVariant->id,
            'max_quantity' => 2,
        ]);

        $limits = PurchaseLimit::withoutTrashed()->get();

        $this->expectException(ProductVariantQuantityLimitException::class);
        (new ProductVariantLimit)->execute($limits, $cartLine);
    }

    public function test_throws_exception_on_max_total_limit_exceed()
    {
        $productVariant = ProductVariant::factory()->create();

        $currency = Currency::factory()->create([
            'decimal_places' => 0,
        ]);

        Price::factory()->create([
            'price' => 60,
            'currency_id' => $currency->id,
            'tier' => 1,
            'priceable_type' => get_class($productVariant),
            'priceable_id' => $productVariant->id,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);
        $cart->lines()->create([
            'purchasable_type' => get_class($productVariant),
            'purchasable_id' => $productVariant->id,
            'quantity' => 2,
        ]);
        $customerGroups = CustomerGroup::factory()->count(2)->create();

        $cartLine = app(CalculateLine::class)->execute(
            $cart->lines()->first(),
            $customerGroups
        );

        PurchaseLimit::factory()->create([
            'product_variant_id' => $productVariant->id,
            'max_total' => 100,
        ]);

        $limits = PurchaseLimit::withoutTrashed()->get();

        $this->expectException(ProductVariantTotalLimitException::class);
        (new ProductVariantLimit)->execute($limits, $cartLine);
    }
}
