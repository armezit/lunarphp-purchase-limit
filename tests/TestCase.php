<?php

namespace Armezit\Lunar\PurchaseLimit\Tests;

use Armezit\Lunar\PurchaseLimit\PurchaseLimitServiceProvider;
use Armezit\Lunar\PurchaseLimit\Tests\Concerns\FixesSqliteDropForeign;
use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Models\Language;
use Lunar\Tests\Stubs\User;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use FixesSqliteDropForeign;

    public function __construct(string $name = null, array $data = [], string $dataName = '')
    {
        $this->hotfixSqlite();
        parent::__construct($name, $data, $dataName);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Armezit\\Lunar\\PurchaseLimit\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        // additional setup
        Config::set('auth.providers.users.model', User::class);
        Language::factory()->create(['default' => true]);
        activity()->disableLogging();
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/../vendor/lunar/core/database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
            PurchaseLimitServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('lunar.database.table_prefix', '');
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migrationFiles = glob(__DIR__.'/../database/migrations/*.php.stub');
        foreach ($migrationFiles as $migrationFile) {
            $migration = include $migrationFile;
            $migration->up();
        }
    }
}
