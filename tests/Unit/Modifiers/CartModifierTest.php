<?php

namespace Armezit\Lunar\PurchaseLimit\Tests\Unit\Modifiers;

use Armezit\Lunar\PurchaseLimit\Models\PurchaseLimit;
use Armezit\Lunar\PurchaseLimit\Modifiers\CartModifier;
use Armezit\Lunar\PurchaseLimit\Rules\CartRuleInterface;
use Armezit\Lunar\PurchaseLimit\Rules\CustomerLimit;
use Armezit\Lunar\PurchaseLimit\Rules\CustomerProductLimit;
use Armezit\Lunar\PurchaseLimit\Rules\ProductLimit;
use Armezit\Lunar\PurchaseLimit\Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Stubs\User;

class CartModifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_rule_objects_are_correct()
    {
        $rules = (new CartModifier)->getRules([
            ProductLimit::class,
            CustomerLimit::class,
        ]);
        foreach ($rules as $rule) {
            $this->assertInstanceOf(CartRuleInterface::class, $rule);
        }
    }

    public function test_can_query_purchase_limits_of_all_rules()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $customer->users()->attach($user);

        $customerGroups = CustomerGroup::factory()->count(2)->create();
        $customer->customerGroups()->attach($customerGroups->first());

        $product = Product::factory()->create();
        $productVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        $currency = Currency::factory()->create([
            'decimal_places' => 0,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'user_id' => $user->getKey(),
        ]);
        $cart->lines()->create([
            'purchasable_type' => get_class($productVariant),
            'purchasable_id' => $productVariant->id,
            'quantity' => 1,
        ]);

        PurchaseLimit::factory()
                     ->count(5)
                     ->state(new Sequence(
                         [
                             'product_id' => $product->id,
                             'customer_id' => $customer->id,
                         ],
                         [
                             'product_id' => $product->id,
                         ],
                         [
                             'customer_id' => $customer->id,
                         ],
                         [
                             'customer_group_id' => $customerGroups[0]->id,
                         ],
                         [
                             'customer_group_id' => $customerGroups[1]->id,
                         ],
                     ))
                     ->create();

        $cartModifier = new CartModifier;
        $rules = $cartModifier->getRules([
            ProductLimit::class,
            CustomerLimit::class,
            CustomerProductLimit::class,
        ]);

        $limits = $cartModifier->getPurchaseLimits($rules, $cart);

        $this->assertCount(4, $limits);
    }
}
