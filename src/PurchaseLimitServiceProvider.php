<?php

namespace Armezit\GetCandy\PurchaseLimit;

use Armezit\GetCandy\PurchaseLimit\Commands\ListPurchaseLimits;
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
}
