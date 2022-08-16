<?php

namespace Armezit\GetCandy\PurchaseLimit\Tests;

use Armezit\GetCandy\PurchaseLimit\PurchaseLimitServiceProvider;
use Armezit\GetCandy\PurchaseLimit\Tests\Concerns\FixesSqliteDropForeign;
use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use GetCandy\GetCandyServiceProvider;
use GetCandy\Models\Language;
use GetCandy\Tests\Stubs\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use FixesSqliteDropForeign;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        $this->hotfixSqlite();
        parent::__construct($name, $data, $dataName);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Armezit\\GetCandy\\PurchaseLimit\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        // additional setup
        Config::set('auth.providers.users.model', User::class);
        Language::factory()->create(['default' => true]);
        activity()->disableLogging();
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/../vendor/getcandy/core/database/migrations');
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

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('getcandy.database.table_prefix', '');
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
