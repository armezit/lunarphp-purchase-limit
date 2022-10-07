<?php

namespace Armezit\Lunar\PurchaseLimit\Tests\Unit\Rules;

use Armezit\Lunar\PurchaseLimit\Exceptions\CustomerQuantityLimitException;
use Armezit\Lunar\PurchaseLimit\Exceptions\CustomerTotalLimitException;
use Armezit\Lunar\PurchaseLimit\Models\PurchaseLimit;
use Armezit\Lunar\PurchaseLimit\Rules\CustomerLimit;
use Armezit\Lunar\PurchaseLimit\Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Stubs\User;

class CustomerLimitTest extends TestCase
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

        $productVariants = ProductVariant::factory()->count(2)->create();

        $currency = Currency::factory()->create([
            'decimal_places' => 0,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'user_id' => $this->user->getKey(),
        ]);

        $cart->lines()->createMany([
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[0]->id,
                'quantity' => 1,
            ],
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[1]->id,
                'quantity' => 2,
            ],
        ]);

        PurchaseLimit::factory()
                     ->count(4)
                     ->state(new Sequence(
                         ['product_variant_id' => $productVariants[0]->id],
                         ['product_id' => $productVariants[0]->product_id],
                         ['customer_id' => $this->customer->id],
                         ['customer_group_id' => $customerGroups[0]->id],
                     ))
                     ->create();

        $query = PurchaseLimit::withoutTrashed()->where(function ($q) use ($cart) {
            (new CustomerLimit)->query($q, $cart);
        });

        $this->assertEquals(2, $query->count());
    }

    public function test_collection_filter_is_correct()
    {
        $customerGroups = CustomerGroup::factory()->count(2)->create();
        $this->customer->customerGroups()->attach($customerGroups->first());

        $productVariants = ProductVariant::factory()->count(2)->create();

        $currency = Currency::factory()->create([
            'decimal_places' => 0,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'user_id' => $this->user->getKey(),
        ]);

        $cart->lines()->createMany([
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[0]->id,
                'quantity' => 1,
            ],
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[1]->id,
                'quantity' => 2,
            ],
        ]);

        PurchaseLimit::factory()
                     ->count(4)
                     ->state(new Sequence(
                         ['product_variant_id' => $productVariants[0]->id],
                         ['product_id' => $productVariants[0]->product_id],
                         ['customer_id' => $this->customer->id],
                         ['customer_group_id' => $customerGroups[0]->id],
                     ))
                     ->create();

        $limits = PurchaseLimit::withoutTrashed()->get();
        $limits = (new CustomerLimit)->filter($limits, $cart);

        $this->assertCount(2, $limits);
    }

    public function test_throws_exception_on_max_quantity_limit_exceed()
    {
        $productVariants = ProductVariant::factory()->count(2)->create();

        $currency = Currency::factory()->create([
            'decimal_places' => 0,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'user_id' => $this->user->getKey(),
        ]);

        $cart->lines()->createMany([
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[0]->id,
                'quantity' => 1,
            ],
            [
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $productVariants[1]->id,
                'quantity' => 2,
            ],
        ]);

        PurchaseLimit::factory()->create([
            'customer_id' => $this->customer->id,
            'max_quantity' => 2,
        ]);

        $limits = PurchaseLimit::withoutTrashed()->get();

        $this->expectException(CustomerQuantityLimitException::class);
        (new CustomerLimit)->execute($limits, $cart);
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
            'user_id' => $this->user->getKey(),
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
            'customer_id' => $this->customer->id,
            'max_total' => 300,
        ]);

        $limits = PurchaseLimit::withoutTrashed()->get();

        $this->expectException(CustomerTotalLimitException::class);
        (new CustomerLimit)->execute($limits, $cart);
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
            'quantity' => 3,
        ]);

        PurchaseLimit::factory()->create([
            'customer_id' => $this->customer->id,
            'max_quantity' => 2,
        ]);

        /*
         * check query builder is correct
         */
        $query = PurchaseLimit::withoutTrashed();
        $filteredQuery = $query->where(function ($q) use ($cart) {
            (new CustomerLimit)->query($q, $cart);
        });
        $this->assertSame($filteredQuery, $query);

        /*
         * check executes without error
         */
        $limits = PurchaseLimit::withoutTrashed()->get();
        (new CustomerLimit)->execute($limits, $cart);
        $this->assertTrue(true);
    }
}
