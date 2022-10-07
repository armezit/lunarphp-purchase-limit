<?php

namespace Armezit\Lunar\PurchaseLimit\Exceptions;

class ProductVariantQuantityLimitException extends PurchaseLimitException
{
    public function __construct()
    {
        parent::__construct('product variant quantity limit exceeded');
    }
}
