<?php

namespace Armezit\GetCandy\PurchaseLimit\Modifiers;

use Armezit\GetCandy\PurchaseLimit\Exceptions\ProductVariantQuantityLimitException;
use Armezit\GetCandy\PurchaseLimit\Exceptions\ProductVariantTotalLimitException;
use Armezit\GetCandy\PurchaseLimit\Models\PurchaseLimit;
use Armezit\GetCandy\PurchaseLimit\Rules\CartRuleInterface;
use Closure;
use GetCandy\Base\CartModifier as BaseCartModifier;
use GetCandy\Models\Cart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
        $rules = $this->getRules(config('getcandy-purchase-limit.cart_rules', []));

        $purchaseLimits = $this->getPurchaseLimits($rules, $cart);

        foreach ($rules as $rule) {
            $rule->execute($purchaseLimits, $cart);
        }

        return $next($cart);
    }

    /**
     * @param  array  $classes
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
     * @param  Cart  $cart
     * @return Collection
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
