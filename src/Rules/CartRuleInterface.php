<?php

namespace Armezit\Lunar\PurchaseLimit\Rules;

use Armezit\Lunar\PurchaseLimit\Exceptions\ProductQuantityLimitException;
use Armezit\Lunar\PurchaseLimit\Exceptions\ProductTotalLimitException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lunar\Models\Cart;

interface CartRuleInterface
{
    /**
     * get builder for retrieving purchase limits of this rule
     */
    public function query(Builder $query, Cart $cart): Builder;

    /**
     * filter purchase limits collection which this rule is responsible for
     */
    public function filter(Collection $purchaseLimits, Cart $cart): Collection;

    /**
     * check rule against a collection of purchase limits
     *
     *
     * @throws ProductQuantityLimitException
     * @throws ProductTotalLimitException
     */
    public function execute(Collection $purchaseLimits, Cart $cart): void;
}
