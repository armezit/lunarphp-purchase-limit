<?php

namespace Armezit\GetCandy\PurchaseLimit\Rules;

use Armezit\GetCandy\PurchaseLimit\Exceptions\ProductQuantityLimitException;
use Armezit\GetCandy\PurchaseLimit\Exceptions\ProductTotalLimitException;
use GetCandy\Models\Cart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface CartRuleInterface
{
    /**
     * get builder for retrieving purchase limits of this rule
     *
     * @param  Builder  $query
     * @param  Cart  $cart
     */
    public function query(Builder $query, Cart $cart): Builder;

    /**
     * filter purchase limits collection which this rule is responsible for
     *
     * @param  Collection  $purchaseLimits
     * @param  Cart  $cart
     */
    public function filter(Collection $purchaseLimits, Cart $cart): Collection;

    /**
     * check rule against a collection of purchase limits
     *
     * @param  Collection  $purchaseLimits
     * @param  Cart  $cart
     * @return void
     *
     * @throws ProductQuantityLimitException
     * @throws ProductTotalLimitException
     */
    public function execute(Collection $purchaseLimits, Cart $cart): void;
}
