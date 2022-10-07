<?php

namespace Armezit\Lunar\PurchaseLimit\Tests\Concerns;

trait FixesSqliteDropForeign
{
    /**
     * Fix for: BadMethodCallException : SQLite doesn't support dropping foreign keys (you would need to re-create the table).
     *
     * @see https://github.com/laravel/framework/issues/25475#issuecomment-439342648
     */
    public function hotfixSqlite()
    {
        \Illuminate\Database\Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config) {
            return new class($connection, $database, $prefix, $config) extends \Illuminate\Database\SQLiteConnection
            {
                public function getSchemaBuilder()
                {
                    if ($this->schemaGrammar === null) {
                        $this->useDefaultSchemaGrammar();
                    }

                    return new class($this) extends \Illuminate\Database\Schema\SQLiteBuilder
                    {
                        protected function createBlueprint($table, \Closure $callback = null)
                        {
                            return new class($table, $callback) extends \Illuminate\Database\Schema\Blueprint
                            {
                                public function dropForeign($index)
                                {
                                    return new \Illuminate\Support\Fluent();
                                }
                            };
                        }
                    };
                }
            };
        });
    }
}
