<?php

namespace Micromus\KafkaBus\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Micromus\KafkaBus\KafkaBusServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Micromus\\KafkaBus\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            KafkaBusServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-kafka-bus_table.php.stub';
        $migration->up();
        */
    }
}
