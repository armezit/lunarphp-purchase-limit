<?php

namespace Armezit\Lunar\PurchaseLimit\Rules;

use Armezit\Lunar\PurchaseLimit\Exceptions\ProductVariantQuantityLimitException;
use Armezit\Lunar\PurchaseLimit\Exceptions\ProductVariantTotalLimitException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lunar\Models\CartLine;

interface CartLineRuleInterface
{
    /**
     * get builder for retrieving purchase limits of this rule
     */
    public function query(Builder $query, CartLine $cartLine): Builder;

    /**
     * filter purchase limits collection which this rule is responsible for
     */
    public function filter(Collection $purchaseLimits, CartLine $cartLine): Collection;

    /**
     * check rule against a collection of purchase limits
     *
     *
     * @throws ProductVariantQuantityLimitException
     * @throws ProductVariantTotalLimitException
     */
    public function execute(Collection $purchaseLimits, CartLine $cartLine): void;
}
