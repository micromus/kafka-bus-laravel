<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Workbench\App\Console\Commands\KafkaBusPublicationTestCommand;

final class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app['config']->set('kafka-bus', require __DIR__.'/../../config/kafka-bus.php');

        $this->commands([
            KafkaBusPublicationTestCommand::class,
        ]);
    }
}
