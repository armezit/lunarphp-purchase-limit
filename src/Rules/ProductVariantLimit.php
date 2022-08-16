<?php

namespace Armezit\GetCandy\PurchaseLimit\Rules;

use Armezit\GetCandy\PurchaseLimit\Exceptions\ProductVariantQuantityLimitException;
use Armezit\GetCandy\PurchaseLimit\Exceptions\ProductVariantTotalLimitException;
use GetCandy\Models\CartLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * check general purchase limit on a specific product variant
 */
class ProductVariantLimit implements CartLineRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function query(Builder $builder, CartLine $cartLine): Builder
    {
        return $builder
            ->where([
                'product_id' => 0,
                'customer_id' => 0,
                'customer_group_id' => 0,
                'product_variant_id' => $cartLine->purchasable_id,
            ]);
    }

    /**
     * {@inheritDoc}
     */
    public function filter(Collection $purchaseLimits, CartLine $cartLine): Collection
    {
        return $purchaseLimits->filter(function ($limit) use ($cartLine) {
            return
                $limit->product_variant_id === $cartLine->purchasable_id &&
                $limit->product_id === 0 &&
                $limit->customer_id === 0 &&
                $limit->customer_group_id === 0;
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
