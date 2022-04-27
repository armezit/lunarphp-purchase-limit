<?php

namespace Armezit\GetCandy\PurchaseLimit\Exceptions;

class ProductVariantTotalLimitException extends PurchaseLimitException
{
    public function __construct()
    {
        parent::__construct('product variant total limit exceeded');
    }
}
