<?php

namespace Armezit\GetCandy\PurchaseLimit;

use Armezit\GetCandy\PurchaseLimit\Http\Livewire\Slots\CustomerPurchaseLimitSlot;
use Armezit\GetCandy\PurchaseLimit\Http\Livewire\Slots\ProductPurchaseLimitSlot;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class PurchaseLimitHubServiceProvider extends ServiceProvider
{
    protected string $root = __DIR__.'/..';

    /**
     * Boot up the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerLivewireComponents();
    }

    /**
     * Register the hub's Livewire components.
     *
     * @return void
     */
    protected function registerLivewireComponents()
    {
//        Livewire::component('hub.customer.slots.customer-purchase-limit-slot', CustomerPurchaseLimitSlot::class);
        Livewire::component('hub.product.slots.product-purchase-limit-slot', ProductPurchaseLimitSlot::class);
    }
}
