<?php

namespace Armezit\Lunar\PurchaseLimit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Armezit\Lunar\PurchaseLimit\LunarPurchaseLimit
 */
class PurchaseLimit extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Armezit\Lunar\PurchaseLimit\PurchaseLimitManager::class;
    }
}
