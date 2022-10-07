<?php

namespace Armezit\Lunar\PurchaseLimit\Exceptions;

class ProductQuantityLimitException extends PurchaseLimitException
{
    public function __construct()
    {
        parent::__construct('product quantity limit exceeded');
    }
}
