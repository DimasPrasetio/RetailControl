<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function refreshTestDatabase()
    {
        $this->wipeMysqlTestDatabase();
        $this->artisan('migrate');
        $this->app[Kernel::class]->setArtisan(null);
    }

    private function wipeMysqlTestDatabase(): void
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'mysql') {
            $this->artisan('migrate:fresh');
            return;
        }

        $connection->statement('SET FOREIGN_KEY_CHECKS=0');

        $views = collect($connection->select("SHOW FULL TABLES WHERE Table_type = 'VIEW'"));
        foreach ($views as $view) {
            $name = array_values((array) $view)[0];
            $connection->statement("DROP VIEW IF EXISTS `{$name}`");
        }

        $tables = collect($connection->select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'"));
        foreach ($tables as $table) {
            $name = array_values((array) $table)[0];
            $connection->statement("DROP TABLE IF EXISTS `{$name}`");
        }

        $connection->statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
