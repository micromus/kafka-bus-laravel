<?php

namespace Micromus\KafkaBusLaravel\Tests;

use Micromus\KafkaBusLaravel\KafkaBusServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            KafkaBusServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
