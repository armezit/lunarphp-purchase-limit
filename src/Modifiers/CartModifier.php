<?php

namespace Armezit\Lunar\PurchaseLimit\Modifiers;

use Armezit\Lunar\PurchaseLimit\Exceptions\ProductVariantQuantityLimitException;
use Armezit\Lunar\PurchaseLimit\Exceptions\ProductVariantTotalLimitException;
use Armezit\Lunar\PurchaseLimit\Models\PurchaseLimit;
use Armezit\Lunar\PurchaseLimit\Rules\CartRuleInterface;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lunar\Base\CartModifier as BaseCartModifier;
use Lunar\Models\Cart;

class CartModifier extends BaseCartModifier
{
    /**
     * {@inheritDoc}
     *
     * @throws ProductVariantQuantityLimitException
     * @throws ProductVariantTotalLimitException
     */
    public function calculated(Cart $cart, Closure $next): Cart
    {
        $rules = $this->getRules(config('lunarphp-purchase-limit.cart_rules', []));

        $purchaseLimits = $this->getPurchaseLimits($rules, $cart);

        foreach ($rules as $rule) {
            $rule->execute($purchaseLimits, $cart);
        }

        return $next($cart);
    }

    /**
     * @return CartRuleInterface[]
     */
    public function getRules(array $classes): array
    {
        return array_map(fn ($class) => app($class), $classes);
    }

    /**
     * get purchase limit collection of all rules in a single query
     *
     * @param  CartRuleInterface[]  $rules
     */
    public function getPurchaseLimits(array $rules, Cart $cart): Collection
    {
        $query = PurchaseLimit::withoutTrashed()
            ->where(function (Builder $query) use ($cart, $rules) {
                foreach ($rules as $rule) {
                    $query->orWhere(function (Builder $q) use ($cart, $rule) {
                        $rule->query($q, $cart);
                    });
                }
            });

        return $query->get();
    }
}
