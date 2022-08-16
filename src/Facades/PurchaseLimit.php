<?php

namespace Armezit\GetCandy\PurchaseLimit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Armezit\GetCandy\PurchaseLimit\GetCandyPurchaseLimit
 */
class PurchaseLimit extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Armezit\GetCandy\PurchaseLimit\PurchaseLimitManager::class;
    }
}
