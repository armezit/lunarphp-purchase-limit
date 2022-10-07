<?php

namespace Armezit\Lunar\PurchaseLimit\Rules;

use Armezit\Lunar\PurchaseLimit\Exceptions\ProductQuantityLimitException;
use Armezit\Lunar\PurchaseLimit\Exceptions\ProductTotalLimitException;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * check customer purchase limit on a specific product
 */
class CustomerProductLimit implements CartRuleInterface
{
    private function getProductIds(Cart $cart): Collection
    {
        return $cart->lines()
                    ->with('purchasable')
                    ->get()
                    ->pluck('purchasable')
                    ->flatten()
                    ->pluck('product_id')
                    ->unique();
    }

    private function getCustomerIds(Cart $cart): Collection
    {
        return $cart->user->customers->pluck('id');
    }

    private function getCustomerGroupIds(Cart $cart): Collection
    {
        return $cart
            ->user
            ->customers()
            ->with('customerGroups')
            ->get()
            ->pluck('customerGroups')
            ->flatten()
            ->pluck('id');
    }

    /**
     * {@inheritDoc}
     */
    public function query(Builder $builder, Cart $cart): Builder
    {
        if (! $cart->user) {
            return $builder;
        }

        return $builder
            ->where(['product_variant_id' => 0])
            ->whereIn('product_id', $this->getProductIds($cart))
            ->where(function (Builder $q) use ($cart) {
                $q->whereIn('customer_id', $this->getCustomerIds($cart))
                  ->orWhereIn('customer_group_id', $this->getCustomerGroupIds($cart));
            });
    }

    /**
     * {@inheritDoc}
     */
    public function filter(Collection $purchaseLimits, Cart $cart): Collection
    {
        if (! $cart->user) {
            return collect();
        }

        return $purchaseLimits->filter(function ($limit) use ($cart) {
            return
                $limit->product_variant_id === 0 &&
                $this->getProductIds($cart)->contains($limit->product_id) &&
                (
                    $this->getCustomerIds($cart)->contains($limit->customer_id) ||
                    $this->getCustomerGroupIds($cart)->contains($limit->customer_group_id)
                );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Collection $purchaseLimits, Cart $cart): void
    {
        $limits = $this->filter($purchaseLimits, $cart);

        foreach ($limits as $limit) {

            // calculate total quantity of product
            $quantity = $cart->lines->filter(function (CartLine $cartLine) use ($limit) {
                return $cartLine->purchasable->product_id === $limit->product_id;
            })->sum('quantity');

            if ($limit->max_quantity !== null && $limit->max_quantity < $quantity) {
                throw new ProductQuantityLimitException;
            }

            // calculate total price of product
            $subTotal = $cart->lines->filter(function (CartLine $cartLine) use ($limit) {
                return $cartLine->purchasable->product_id === $limit->product_id;
            })->sum('subTotal.value');

            if ($limit->max_total !== null && $limit->max_total < $subTotal) {
                throw new ProductTotalLimitException;
            }
        }
    }
}
