<?php

namespace Micromus\KafkaBusLaravel;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Micromus\KafkaBus\Bus;
use Micromus\KafkaBus\Bus\Listeners\ListenerFactory;
use Micromus\KafkaBus\Bus\Publishers\PublisherFactory;
use Micromus\KafkaBus\Bus\ThreadRegistry;
use Micromus\KafkaBus\Connections\Registry\DriverRegistry;
use Micromus\KafkaBus\Consumers\ConsumerStreamFactory;
use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionRegistryInterface;
use Micromus\KafkaBus\Producers\ProducerStreamFactory;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBusLaravel\Commands\KafkaConsumeCommand;
use Micromus\KafkaBusLaravel\Commands\KafkaOffsetSetCommand;
use Micromus\KafkaBusLaravel\Commands\KafkaOffsetShowCommand;
use Micromus\KafkaBusLaravel\Commands\KafkaRouteListCommand;
use Micromus\KafkaBusLaravel\Commands\KafkaWorkerListCommand;
use Micromus\KafkaBusLaravel\Connections\ConnectionRegistryFactory;
use Micromus\KafkaBusLaravel\Factories\TopicRegistryFactory;
use Micromus\KafkaBusLaravel\Listeners\LaravelWorkerRegistry;
use Micromus\KafkaBusLaravel\Listeners\WorkerFactory;
use Micromus\KafkaBusLaravel\Publishers\LaravelPublisherRoutesFactory;

class KafkaBusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kafka-bus.php', 'kafka-bus');

        $this->app->bind(TopicRegistry::class, $this->makeTopicRegistry(...));

        $this->app->bind(PublisherFactory::class, $this->makePublisherFactory(...));
        $this->app->bind(ListenerFactory::class, $this->makeListenerFactory(...));

        $this->app->singleton(DriverRegistry::class, $this->makeDriverRegistry(...));
        $this->app->singleton(ThreadRegistry::class, $this->makeThreadRegistry(...));
        $this->app->singleton(ConnectionRegistryInterface::class, $this->makeConnectionRegistry(...));

        $this->app->singleton(BusInterface::class, $this->makeBus(...));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/kafka-bus.php' => config_path('kafka-bus.php'),
        ], 'kafka-bus');

        if ($this->app->runningInConsole()) {
            $this->commands([
                KafkaConsumeCommand::class,
                KafkaOffsetShowCommand::class,
                KafkaOffsetSetCommand::class,
                KafkaWorkerListCommand::class,
                KafkaRouteListCommand::class,
            ]);
        }
    }

    protected function makeTopicRegistry(Application $app): TopicRegistry
    {
        return $app->make(TopicRegistryFactory::class)
            ->create();
    }

    protected function makePublisherFactory(Application $app): PublisherFactory
    {
        return new PublisherFactory(
            new ProducerStreamFactory(),
            $app->make(LaravelPublisherRoutesFactory::class)->create(),
        );
    }

    protected function makeListenerFactory(Application $app): ListenerFactory
    {
        return new ListenerFactory(
            new ConsumerStreamFactory(),
            new LaravelWorkerRegistry($app->make(WorkerFactory::class)),
        );
    }

    protected function makeThreadRegistry(Application $app): ThreadRegistry
    {
        return new ThreadRegistry(
            $app->make(ConnectionRegistryInterface::class),
            new Bus\ThreadFactory(
                $app->make(ListenerFactory::class),
                $app->make(PublisherFactory::class),
            )
        );
    }

    protected function makeBus(Application $app): BusInterface
    {
        return new Bus(
            $app->make(ThreadRegistry::class),
            $app['config']->get('kafka-bus.default')
        );
    }

    protected function makeDriverRegistry(): DriverRegistry
    {
        return new DriverRegistry();
    }

    protected function makeConnectionRegistry(Application $app): ConnectionRegistryInterface
    {
        return $app->make(ConnectionRegistryFactory::class)
            ->create($app->make(DriverRegistry::class));
    }
}
