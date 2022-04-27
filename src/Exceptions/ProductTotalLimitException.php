<?php

namespace Armezit\GetCandy\PurchaseLimit\Exceptions;

class ProductTotalLimitException extends PurchaseLimitException
{
    public function __construct()
    {
        parent::__construct('product total limit exceeded');
    }
}
