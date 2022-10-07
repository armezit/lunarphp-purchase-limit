<?php

namespace Armezit\Lunar\PurchaseLimit;

use Armezit\Lunar\PurchaseLimit\Commands\ListPurchaseLimits;
use Armezit\Lunar\PurchaseLimit\Modifiers\CartLineModifier;
use Armezit\Lunar\PurchaseLimit\Modifiers\CartModifier;
use Lunar\Base\CartLineModifiers;
use Lunar\Base\CartModifiers;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PurchaseLimitServiceProvider extends PackageServiceProvider
{
    public static string $name = 'lunarphp-purchase-limit';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                'create_purchase_limits_table',
            ])
            ->runsMigrations()
            ->hasCommands([
                ListPurchaseLimits::class,
            ]);
    }

    public function packageBooted()
    {
        if (config('lunarphp-purchase-limit.register_cart_modifiers', true)) {
            $this->app->get(CartModifiers::class)->add(CartModifier::class);
            $this->app->get(CartLineModifiers::class)->add(CartLineModifier::class);
        }
    }
}
