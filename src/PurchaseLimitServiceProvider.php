<?php

namespace Armezit\GetCandy\PurchaseLimit;

use Illuminate\Support\ServiceProvider;

class PurchaseLimitServiceProvider extends ServiceProvider
{

    protected string $root = __DIR__ . '/..';

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom("{$this->root}/config/purchase-limit.php", "purchase-limit");
    }

    /**
     * Boot up the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom("{$this->root}/database/migrations");
        $this->loadViewsFrom("{$this->root}/resources/views", 'purchase-limit');
        $this->loadTranslationsFrom("{$this->root}/resources/lang", 'purchase-limit');

        $this->registerPublishables();
    }

    /**
     * Register our publishables.
     *
     * @return void
     */
    private function registerPublishables()
    {
        $this->publishes([
            "{$this->root}/config/purchase-limit.php" => config_path("purchase-limit.php"),
        ], ['getcandy:purchase-limit:config']);

        $this->publishes([
            "{$this->root}/resources/lang" => $this->app->langPath('vendor/getcandy-purchase-limit'),
        ], ['getcandy:purchase-limit:lang']);
    }

}
