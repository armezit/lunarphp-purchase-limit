<?php

namespace Armezit\GetCandy\PurchaseLimit\Rules;

use Armezit\GetCandy\PurchaseLimit\Exceptions\ProductVariantQuantityLimitException;
use Armezit\GetCandy\PurchaseLimit\Exceptions\ProductVariantTotalLimitException;
use GetCandy\Models\CartLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface CartLineRuleInterface
{

    /**
     * get builder for retrieving purchase limits of this rule
     * @param Builder $query
     * @param CartLine $cartLine
     */
    public function query(Builder $query, CartLine $cartLine): Builder;

    /**
     * filter purchase limits collection which this rule is responsible for
     * @param Collection $purchaseLimits
     * @param CartLine $cartLine
     */
    public function filter(Collection $purchaseLimits, CartLine $cartLine): Collection;

    /**
     * check rule against a collection of purchase limits
     * @param Collection $purchaseLimits
     * @param CartLine $cartLine
     * @return void
     * @throws ProductVariantQuantityLimitException
     * @throws ProductVariantTotalLimitException
     */
    public function execute(Collection $purchaseLimits, CartLine $cartLine): void;

}
