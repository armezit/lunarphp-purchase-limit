<?php

namespace Armezit\Lunar\PurchaseLimit\Tests\Unit\Rules;

use Armezit\Lunar\PurchaseLimit\Exceptions\ProductVariantQuantityLimitException;
use Armezit\Lunar\PurchaseLimit\Exceptions\ProductVariantTotalLimitException;
use Armezit\Lunar\PurchaseLimit\Models\PurchaseLimit;
use Armezit\Lunar\PurchaseLimit\Rules\CustomerProductVariantLimit;
use Armezit\Lunar\PurchaseLimit\Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Carts\CalculateLine;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Stubs\User;

class CustomerProductVariantLimitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->customer->users()->attach($this->user);
    }

    public function test_query_builder_is_correct()
    {
        $customerGroups = CustomerGroup::factory()->count(2)->create();
        $this->customer->customerGroups()->attach($customerGroups->first());

        $productVariant = ProductVariant::factory()->create();

        $currency = Currency::factory()->create([
            'decimal_places' => 0,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'user_id' => $this->user->getKey(),
        ]);
        $cart->lines()->create([
            'purchasable_type' => get_class($productVariant),
            'purchasable_id' => $productVariant->id,
            'quantity' => 1,
        ]);

        PurchaseLimit::factory()
                     ->count(7)
                     ->state(new Sequence(
                         [
                             'product_variant_id' => $productVariant->id,
                             'customer_id' => $this->customer->id,
                         ],
                         [
                             'product_variant_id' => $productVariant->id,
                         ],
                         [
                             'product_variant_id' => $productVariant->id,
                             'customer_group_id' => $customerGroups[0]->id,
                         ],
                         [
                             'product_variant_id' => $productVariant->id,
                             'customer_group_id' => $customerGroups[1]->id,
                         ],
                         [
                             'customer_id' => $this->customer->id,
                         ],
                         [
                             'customer_group_id' => $customerGroups[0]->id,
                         ],
                         [
                             'customer_group_id' => $customerGroups[1]->id,
                         ],
                     ))
                     ->create();

        $query = PurchaseLimit::withoutTrashed()->where(function ($q) use ($cart) {
            (new CustomerProductVariantLimit)->query($q, $cart->lines->first());
        });

        $this->assertEquals(2, $query->count());
    }

    public function test_collection_filter_is_correct()
    {
        $customerGroups = CustomerGroup::factory()->count(2)->create();
        $this->customer->customerGroups()->attach($customerGroups->first());

        $productVariant = ProductVariant::factory()->create();

        $currency = Currency::factory()->create([
            'decimal_places' => 0,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'user_id' => $this->user->getKey(),
        ]);
        $cart->lines()->create([
            'purchasable_type' => get_class($productVariant),
            'purchasable_id' => $productVariant->id,
            'quantity' => 1,
        ]);

        PurchaseLimit::factory()
                     ->count(7)
                     ->state(new Sequence(
                         [
                             'product_variant_id' => $productVariant->id,
                             'customer_id' => $this->customer->id,
                         ],
                         [
                             'product_variant_id' => $productVariant->id,
                         ],
                         [
                             'product_variant_id' => $productVariant->id,
                             'customer_group_id' => $customerGroups[0]->id,
                         ],
                         [
                             'product_variant_id' => $productVariant->id,
                             'customer_group_id' => $customerGroups[1]->id,
                         ],
                         [
                             'customer_id' => $this->customer->id,
                         ],
                         [
                             'customer_group_id' => $customerGroups[0]->id,
                         ],
                         [
                             'customer_group_id' => $customerGroups[1]->id,
                         ],
                     ))
                     ->create();

        $limits = PurchaseLimit::withoutTrashed()->get();
        $limits = (new CustomerProductVariantLimit)->filter($limits, $cart->lines->first());

        $this->assertCount(2, $limits);
    }

    public function test_throws_exception_on_max_quantity_limit_exceed()
    {
        $productVariant = ProductVariant::factory()->create();

        $currency = Currency::factory()->create([
            'decimal_places' => 0,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'user_id' => $this->user->getKey(),
        ]);
        $cart->lines()->create([
            'purchasable_type' => get_class($productVariant),
            'purchasable_id' => $productVariant->id,
            'quantity' => 5,
        ]);

        PurchaseLimit::factory()->create([
            'product_variant_id' => $productVariant->id,
            'customer_id' => $this->customer->id,
            'max_quantity' => 2,
        ]);

        $limits = PurchaseLimit::withoutTrashed()->get();

        $this->expectException(ProductVariantQuantityLimitException::class);
        (new CustomerProductVariantLimit)->execute($limits, $cart->lines()->first());
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
            'user_id' => $this->user->getKey(),
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
            'customer_id' => $this->customer->id,
            'max_total' => 100,
        ]);

        $limits = PurchaseLimit::withoutTrashed()->get();

        $this->expectException(ProductVariantTotalLimitException::class);
        (new CustomerProductVariantLimit)->execute($limits, $cartLine);
    }

    public function test_can_work_without_error_for_guest_cart()
    {
        $productVariant = ProductVariant::factory()->create();

        $currency = Currency::factory()->create([
            'decimal_places' => 0,
        ]);

        // note that cart does not belong to any user
        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);
        $cart->lines()->create([
            'purchasable_type' => get_class($productVariant),
            'purchasable_id' => $productVariant->id,
            'quantity' => 1,
        ]);

        PurchaseLimit::factory()->create([
            'product_variant_id' => $productVariant->id,
            'customer_id' => $this->customer->id,
        ]);

        /*
         * check query builder is correct
         */
        $query = PurchaseLimit::withoutTrashed();
        $filteredQuery = $query->where(function ($q) use ($cart) {
            (new CustomerProductVariantLimit)->query($q, $cart->lines->first());
        });
        $this->assertSame($filteredQuery, $query);

        /*
         * check executes without error
         */
        $limits = PurchaseLimit::withoutTrashed()->get();
        (new CustomerProductVariantLimit)->execute($limits, $cart->lines()->first());
        $this->assertTrue(true);
    }
}
