<?php

namespace Armezit\Lunar\PurchaseLimit\Exceptions;

class CustomerQuantityLimitException extends PurchaseLimitException
{
    public function __construct()
    {
        parent::__construct('customer quantity limit exceeded');
    }
}
