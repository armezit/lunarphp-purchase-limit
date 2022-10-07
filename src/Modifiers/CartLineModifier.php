<?php

namespace Armezit\Lunar\PurchaseLimit\Modifiers;

use Armezit\Lunar\PurchaseLimit\Exceptions\ProductVariantQuantityLimitException;
use Armezit\Lunar\PurchaseLimit\Exceptions\ProductVariantTotalLimitException;
use Armezit\Lunar\PurchaseLimit\Models\PurchaseLimit;
use Armezit\Lunar\PurchaseLimit\Rules\CartLineRuleInterface;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lunar\Base\CartLineModifier as BaseCartLineModifier;
use Lunar\Models\CartLine;

class CartLineModifier extends BaseCartLineModifier
{
    /**
     * {@inheritDoc}
     *
     * @throws ProductVariantQuantityLimitException
     * @throws ProductVariantTotalLimitException
     */
    public function calculated(CartLine $cartLine, Closure $next): CartLine
    {
        $rules = $this->getRules(config('lunarphp-purchase-limit.cart_line_rules', []));

        $purchaseLimits = $this->getPurchaseLimits($rules, $cartLine);

        foreach ($rules as $rule) {
            $rule->execute($purchaseLimits, $cartLine);
        }

        return $next($cartLine);
    }

    /**
     * @param  array  $classes
     * @return CartLineRuleInterface[]
     */
    public function getRules(array $classes): array
    {
        return array_map(fn ($class) => app($class), $classes);
    }

    /**
     * get purchase limit collection of all rules in a single query
     *
     * @param  CartLineRuleInterface[]  $rules
     * @param  CartLine  $cartLine
     * @return Collection
     */
    public function getPurchaseLimits(array $rules, CartLine $cartLine): Collection
    {
        $query = PurchaseLimit::withoutTrashed()
            ->where(function (Builder $query) use ($cartLine, $rules) {
                foreach ($rules as $rule) {
                    $query->orWhere(function (Builder $q) use ($cartLine, $rule) {
                        $rule->query($q, $cartLine);
                    });
                }
            });

        return $query->get();
    }
}
