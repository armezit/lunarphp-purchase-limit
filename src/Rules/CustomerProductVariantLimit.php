<?php

namespace Armezit\Lunar\PurchaseLimit\Rules;

use Armezit\Lunar\PurchaseLimit\Exceptions\ProductVariantQuantityLimitException;
use Armezit\Lunar\PurchaseLimit\Exceptions\ProductVariantTotalLimitException;
use Lunar\Models\CartLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * check customer purchase limit on a specific product variant
 */
class CustomerProductVariantLimit implements CartLineRuleInterface
{
    private function getCustomerIds(CartLine $cartLine): Collection
    {
        return $cartLine->cart->user->customers->pluck('id');
    }

    private function getCustomerGroupIds(CartLine $cartLine): Collection
    {
        return $cartLine
            ->cart
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
    public function query(Builder $builder, CartLine $cartLine): Builder
    {
        if (! $cartLine->cart->user) {
            return $builder;
        }

        return $builder
            ->where([
                'product_id' => 0,
                'product_variant_id' => $cartLine->purchasable_id,
            ])
            ->where(function (Builder $q) use ($cartLine) {
                $q->whereIn('customer_id', $this->getCustomerIds($cartLine))
                  ->orWhereIn('customer_group_id', $this->getCustomerGroupIds($cartLine));
            });
    }

    /**
     * {@inheritDoc}
     */
    public function filter(Collection $purchaseLimits, CartLine $cartLine): Collection
    {
        if (! $cartLine->cart->user) {
            return collect();
        }

        return $purchaseLimits->filter(function ($limit) use ($cartLine) {
            return
                $limit->product_variant_id === $cartLine->purchasable_id &&
                $limit->product_id === 0 &&
                (
                    $this->getCustomerIds($cartLine)->contains($limit->customer_id) ||
                    $this->getCustomerGroupIds($cartLine)->contains($limit->customer_group_id)
                );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Collection $purchaseLimits, CartLine $cartLine): void
    {
        $limits = $this->filter($purchaseLimits, $cartLine);

        foreach ($limits as $limit) {
            if ($limit->max_quantity !== null && $limit->max_quantity < $cartLine->quantity) {
                throw new ProductVariantQuantityLimitException;
            }
            if ($limit->max_total !== null && $limit->max_total < $cartLine->subTotal->value) {
                throw new ProductVariantTotalLimitException;
            }
        }
    }
}
