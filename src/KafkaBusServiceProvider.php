<?php

namespace Micromus\KafkaBusLaravel;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Micromus\KafkaBus\Bus;
use Micromus\KafkaBus\Bus\Listeners\ListenerFactory;
use Micromus\KafkaBus\Bus\Publishers\PublisherFactory;
use Micromus\KafkaBus\Bus\ThreadRegistry;
use Micromus\KafkaBus\Connections\Registry\ConnectionRegistry;
use Micromus\KafkaBus\Connections\Registry\DriverRegistry;
use Micromus\KafkaBus\Consumers\ConsumerStreamFactory;
use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionRegistryInterface;
use Micromus\KafkaBus\Interfaces\Consumers\ConsumerStreamFactoryInterface;
use Micromus\KafkaBus\Interfaces\Producers\ProducerStreamFactoryInterface;
use Micromus\KafkaBus\Producers\ProducerStreamFactory;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBusLaravel\Commands\KafkaConsumeCommand;
use Micromus\KafkaBusLaravel\Factories\PublisherRoutesFactory;
use Micromus\KafkaBusLaravel\Factories\TopicRegistryFactory;
use Micromus\KafkaBusLaravel\Listeners\LaravelWorkerRegistry;
use Micromus\KafkaBusLaravel\Listeners\WorkerFactory;

class KafkaBusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kafka-bus.php', 'kafka-bus');

        $this->app->bind(TopicRegistry::class, $this->makeTopicRegistry(...));

        $this->app->bind(PublisherFactory::class, $this->makePublisherFactory(...));
        $this->app->bind(ListenerFactory::class, $this->makeListenerFactory(...));

        $this->app->bind(ProducerStreamFactoryInterface::class, ProducerStreamFactory::class);
        $this->app->bind(ConsumerStreamFactoryInterface::class, ConsumerStreamFactory::class);

        $this->app->singleton(DriverRegistry::class, $this->makeDriverRegistry(...));
        $this->app->singleton(ThreadRegistry::class, $this->makeThreadRegistry(...));
        $this->app->singleton(ConnectionRegistryInterface::class, $this->makeConnectionRegistry(...));

        $this->app->singleton(BusInterface::class, $this->makeBus(...));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/kafka-bus.php' => config_path('kafka-bus.php'),
        ], 'kafka-bus-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                KafkaConsumeCommand::class,
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
            $app->make(PublisherRoutesFactory::class)->create()
        );
    }

    protected function makeListenerFactory(Application $app): ListenerFactory
    {
        return new ListenerFactory(
            new ConsumerStreamFactory(),
            new LaravelWorkerRegistry(new WorkerFactory($app['config'])),
        );
    }

    protected function makeThreadRegistry(Application $app): ThreadRegistry
    {
        return new ThreadRegistry(
            $app->make(ConnectionRegistryInterface::class),
            $app->make(PublisherFactory::class),
            $app->make(ListenerFactory::class),
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
        return new ConnectionRegistry(
            $app->make(DriverRegistry::class),
            $app['config']->get('kafka-bus.connections', [])
        );
    }
}
