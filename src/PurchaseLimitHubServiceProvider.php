<?php

namespace Armezit\Lunar\PurchaseLimit;

use Armezit\Lunar\PurchaseLimit\Http\Livewire\Slots\CustomerPurchaseLimitSlot;
use Armezit\Lunar\PurchaseLimit\Http\Livewire\Slots\ProductPurchaseLimitSlot;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lunar\Hub\Facades\Slot;

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
        $this->registerHubSlots();
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

    protected function registerHubSlots()
    {
        if (config('lunarphp-purchase-limit.register_hub_slots', true)) {
            Slot::register(
                config('lunarphp-purchase-limit.product_purchase_limit_slot', 'product.all'),
                ProductPurchaseLimitSlot::class
            );
        }
    }
}
