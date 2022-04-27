<?php

namespace Armezit\GetCandy\PurchaseLimit\Tests\Unit\Rules;

use Armezit\GetCandy\PurchaseLimit\Exceptions\ProductQuantityLimitException;
use Armezit\GetCandy\PurchaseLimit\Exceptions\ProductTotalLimitException;
use Armezit\GetCandy\PurchaseLimit\Models\PurchaseLimit;
use Armezit\GetCandy\PurchaseLimit\Rules\CustomerProductLimit;
use Armezit\GetCandy\PurchaseLimit\Tests\TestCase;
use GetCandy\Models\Cart;
use GetCandy\Models\Currency;
use GetCandy\Models\Customer;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\Price;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use GetCandy\Tests\Stubs\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CustomerProductLimitTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

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

        $products = Product::factory()->count(3)->create();

        $productVariants = ProductVariant
            ::factory()
            ->count(3)
            ->state(new Sequence(
                ['product_id' => $products[0]->id],
                ['product_id' => $products[1]->id],
                ['product_id' => $products[2]->id],
            ))
            ->create();

        $cart = Cart::factory()->create([
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
                     ->count(6)
                     ->state(new Sequence(
                         ['product_id' => $products[0]->id],
                         ['product_id' => $products[1]->id],
                         ['product_variant_id' => $productVariants[0]->id],
                         ['customer_id' => $this->customer->id],
                         [
                             'product_id' => $products[0]->id,
                             'customer_id' => $this->customer->id,
                         ],
                         [
                             'product_id' => $products[1]->id,
                             'customer_id' => $this->customer->id,
                         ],
                         [
                             'product_id' => $products[2]->id,
                             'customer_group_id' => $customerGroups[0]->id,
                         ],
                     ))
                     ->create();

        $query = PurchaseLimit::withoutTrashed()->where(function ($q) use ($cart) {
            (new CustomerProductLimit)->query($q, $cart);
        });

        $this->assertEquals(2, $query->count());
    }

    public function test_collection_filter_is_correct()
    {
        $customerGroups = CustomerGroup::factory()->count(2)->create();
        $this->customer->customerGroups()->attach($customerGroups->first());

        $products = Product::factory()->count(3)->create();

        $productVariants = ProductVariant
            ::factory()
            ->count(3)
            ->state(new Sequence(
                ['product_id' => $products[0]->id],
                ['product_id' => $products[1]->id],
                ['product_id' => $products[2]->id],
            ))
            ->create();

        $cart = Cart::factory()->create([
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
                     ->count(6)
                     ->state(new Sequence(
                         ['product_id' => $products[0]->id],
                         ['product_id' => $products[1]->id],
                         ['product_variant_id' => $productVariants[0]->id],
                         ['customer_id' => $this->customer->id],
                         [
                             'product_id' => $products[0]->id,
                             'customer_id' => $this->customer->id,
                         ],
                         [
                             'product_id' => $products[1]->id,
                             'customer_id' => $this->customer->id,
                         ],
                         [
                             'product_id' => $products[2]->id,
                             'customer_group_id' => $customerGroups[0]->id,
                         ],
                     ))
                     ->create();

        $limits = PurchaseLimit::withoutTrashed()->get();
        $limits = (new CustomerProductLimit)->filter($limits, $cart);

        $this->assertCount(2, $limits);
    }

    public function test_throws_exception_on_max_quantity_limit_exceed()
    {
        $customerGroups = CustomerGroup::factory()->count(2)->create();
        $this->customer->customerGroups()->attach($customerGroups->first());

        $product = Product::factory()->create();

        $productVariants = ProductVariant::factory()->count(2)->create([
            'product_id' => $product->id
        ]);

        $cart = Cart::factory()->create([
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
            'product_id' => $product->id,
            'customer_id' => $this->customer->id,
            'max_quantity' => 2,
        ]);

        $limits = PurchaseLimit::withoutTrashed()->get();

        $this->expectException(ProductQuantityLimitException::class);
        (new CustomerProductLimit)->execute($limits, $cart);
    }

    public function test_throws_exception_on_max_total_limit_exceed()
    {
        $customerGroups = CustomerGroup::factory()->count(2)->create();
        $this->customer->customerGroups()->attach($customerGroups->first());

        $product = Product::factory()->create();

        $productVariants = ProductVariant::factory()->count(2)->create([
            'product_id' => $product->id
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
            'product_id' => $product->id,
            'customer_group_id' => $customerGroups[0]->id,
            'max_total' => 300,
        ]);

        $limits = PurchaseLimit::withoutTrashed()->get();

        $this->expectException(ProductTotalLimitException::class);
        (new CustomerProductLimit)->execute($limits, $cart);
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
            'product_id' => $productVariant->product_id,
            'customer_id' => $this->customer->id,
            'max_quantity' => 2,
        ]);

        /*
         * check query builder is correct
         */
        $query = PurchaseLimit::withoutTrashed();
        $filteredQuery = $query->where(function ($q) use ($cart) {
            (new CustomerProductLimit)->query($q, $cart);
        });
        $this->assertSame($filteredQuery, $query);

        /*
         * check executes without error
         */
        $limits = PurchaseLimit::withoutTrashed()->get();
        (new CustomerProductLimit)->execute($limits, $cart);
        $this->assertTrue(true);
    }

}
