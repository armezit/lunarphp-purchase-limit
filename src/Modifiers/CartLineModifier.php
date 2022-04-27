<?php

namespace Armezit\GetCandy\PurchaseLimit\Modifiers;

use Armezit\GetCandy\PurchaseLimit\Exceptions\ProductVariantQuantityLimitException;
use Armezit\GetCandy\PurchaseLimit\Exceptions\ProductVariantTotalLimitException;
use Armezit\GetCandy\PurchaseLimit\Models\PurchaseLimit;
use Armezit\GetCandy\PurchaseLimit\Rules\CartLineRuleInterface;
use GetCandy\Base\CartLineModifier as BaseCartLineModifier;
use GetCandy\Models\CartLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CartLineModifier extends BaseCartLineModifier
{

    /**
     * @inheritDoc
     * @throws ProductVariantQuantityLimitException
     * @throws ProductVariantTotalLimitException
     */
    public function calculated(CartLine $cartLine)
    {
        $rules = $this->getRules(config('purchase-limit.cart_line_rules', []));

        $purchaseLimits = $this->getPurchaseLimits($rules, $cartLine);

        foreach ($rules as $rule) {
            $rule->execute($purchaseLimits, $cartLine);
        }
    }

    /**
     * @param array $classes
     * @return CartLineRuleInterface[]
     */
    public function getRules(array $classes): array
    {
        return array_map(fn($class) => app($class), $classes);
    }

    /**
     * get purchase limit collection of all rules in a single query
     * @param CartLineRuleInterface[] $rules
     * @param CartLine $cartLine
     * @return Collection
     */
    public function getPurchaseLimits(array $rules, CartLine $cartLine): Collection
    {
        $query = PurchaseLimit
            ::withoutTrashed()
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
