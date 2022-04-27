<?php

namespace Armezit\GetCandy\PurchaseLimit\Tests;

use Armezit\GetCandy\PurchaseLimit\PurchaseLimitServiceProvider;
use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use GetCandy\GetCandyServiceProvider;
use GetCandy\Models\Language;
use GetCandy\Tests\Stubs\User;
use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        // additional setup
        Config::set('auth.providers.users.model', User::class);
        Language::factory()->create(['default' => true]);
        activity()->disableLogging();
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../vendor/getcandy/core/database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            GetCandyServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
            PurchaseLimitServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('getcandy.database.table_prefix', '');
    }

}
