<?php

namespace Armezit\Lunar\PurchaseLimit\Rules;

use Armezit\Lunar\PurchaseLimit\Exceptions\CustomerQuantityLimitException;
use Armezit\Lunar\PurchaseLimit\Exceptions\CustomerTotalLimitException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lunar\Models\Cart;

/**
 * check general purchase limit for a customer
 */
class CustomerLimit implements CartRuleInterface
{
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
            ->where([
                'product_variant_id' => 0,
                'product_id' => 0,
            ])
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
                $limit->product_id == 0 &&
                $limit->product_variant_id === 0 &&
                (
                    $this->getCustomerIds($cart)->contains($limit->customer_id) ||
                    $this->getCustomerGroupIds($cart)->contains($limit->customer_group_id)
                );
        });
    }

    /**
     * {@inheritDoc}
     *
     * @throws CustomerQuantityLimitException
     * @throws CustomerTotalLimitException
     */
    public function execute(Collection $purchaseLimits, Cart $cart): void
    {
        $limits = $this->filter($purchaseLimits, $cart);

        foreach ($limits as $limit) {
            // calculate total quantity of cart
            $quantity = $cart->lines->sum('quantity');

            if ($limit->max_quantity !== null && $limit->max_quantity < $quantity) {
                throw new CustomerQuantityLimitException;
            }

            // calculate total price of cart
            $subTotal = $cart->lines->sum('subTotal.value');

            if ($limit->max_total !== null && $limit->max_total < $subTotal) {
                throw new CustomerTotalLimitException;
            }
        }
    }
}
