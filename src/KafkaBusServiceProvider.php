<?php

namespace Micromus\KafkaBus;

use Micromus\KafkaBus\Commands\KafkaBusCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class KafkaBusServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-kafka-bus')
            ->hasConfigFile()
            ->hasCommand(KafkaBusCommand::class);
    }
}
