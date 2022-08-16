<?php

namespace Armezit\GetCandy\PurchaseLimit;

use Armezit\GetCandy\PurchaseLimit\Commands\ListPurchaseLimits;
use Armezit\GetCandy\PurchaseLimit\Modifiers\CartLineModifier;
use Armezit\GetCandy\PurchaseLimit\Modifiers\CartModifier;
use GetCandy\Base\CartLineModifiers;
use GetCandy\Base\CartModifiers;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PurchaseLimitServiceProvider extends PackageServiceProvider
{
    public static string $name = 'getcandy-purchase-limit';

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
        if (config('getcandy-purchase-limit.register_cart_modifiers', true)) {
            $this->app->get(CartModifiers::class)->add(CartModifier::class);
            $this->app->get(CartLineModifiers::class)->add(CartLineModifier::class);
        }
    }
}
