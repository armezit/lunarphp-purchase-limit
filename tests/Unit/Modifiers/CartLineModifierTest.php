<?php

namespace Armezit\Lunar\PurchaseLimit\Tests\Unit\Modifiers;

use Armezit\Lunar\PurchaseLimit\Models\PurchaseLimit;
use Armezit\Lunar\PurchaseLimit\Modifiers\CartLineModifier;
use Armezit\Lunar\PurchaseLimit\Rules\CartLineRuleInterface;
use Armezit\Lunar\PurchaseLimit\Rules\CustomerProductVariantLimit;
use Armezit\Lunar\PurchaseLimit\Rules\ProductVariantLimit;
use Armezit\Lunar\PurchaseLimit\Tests\TestCase;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Stubs\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartLineModifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_rule_objects_are_correct()
    {
        $rules = (new CartLineModifier)->getRules([
            ProductVariantLimit::class,
            CustomerProductVariantLimit::class,
        ]);
        foreach ($rules as $rule) {
            $this->assertInstanceOf(CartLineRuleInterface::class, $rule);
        }
    }

    public function test_can_query_purchase_limits_of_all_rules()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $customer->users()->attach($user);

        $customerGroups = CustomerGroup::factory()->count(2)->create();
        $customer->customerGroups()->attach($customerGroups->first());

        $productVariant = ProductVariant::factory()->create();

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
                     ->count(7)
                     ->state(new Sequence(
                         [
                             'product_variant_id' => $productVariant->id,
                             'customer_id' => $customer->id,
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

        $cartLineModifier = new CartLineModifier;
        $rules = $cartLineModifier->getRules([
            ProductVariantLimit::class,
            CustomerProductVariantLimit::class,
        ]);

        $limits = $cartLineModifier->getPurchaseLimits($rules, $cart->lines()->first());

        $this->assertCount(3, $limits);
    }
}
