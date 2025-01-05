<?php

namespace Micromus\KafkaBusLaravel;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Micromus\KafkaBus\Bus;
use Micromus\KafkaBus\Bus\Listeners\ListenerFactory;
use Micromus\KafkaBus\Bus\Publishers\PublisherFactory;
use Micromus\KafkaBus\Bus\ThreadRegistry;
use Micromus\KafkaBus\BusLogger;
use Micromus\KafkaBus\Connections\Registry\ConnectionRegistry;
use Micromus\KafkaBus\Connections\Registry\DriverRegistry;
use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Micromus\KafkaBus\Interfaces\BusLoggerInterface;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionRegistryInterface;
use Micromus\KafkaBus\Interfaces\Consumers\ConsumerStreamFactoryInterface;
use Micromus\KafkaBus\Interfaces\Producers\ProducerStreamFactoryInterface;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBusLaravel\Commands\KafkaConsumeCommand;
use Micromus\KafkaBusLaravel\Components\Outbox\KafkaBusOutboxServiceProvider;
use Micromus\KafkaBusLaravel\Components\Repeaters\KafkaBusRepeaterServiceProvider;
use Micromus\KafkaBusLaravel\Factories\PublisherRoutesFactory;
use Micromus\KafkaBusLaravel\Factories\TopicRegistryFactory;
use Micromus\KafkaBusLaravel\Factories\WorkerRegistryFactory;

class KafkaBusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kafka-bus.php', 'kafka-bus');

        $this->app->bind(TopicRegistry::class, $this->makeTopicRegistry(...));

        $this->app->bind(ProducerStreamFactoryInterface::class, $this->app['config']->get('kafka-bus.producers.stream_factory'));
        $this->app->bind(PublisherFactory::class, $this->makePublisherFactory(...));

        $this->app->bind(ConsumerStreamFactoryInterface::class, $this->makeConsumerStreamFactory(...));
        $this->app->bind(ListenerFactory::class, $this->makeListenerFactory(...));

        $this->app->bind(BusLoggerInterface::class, $this->makeBusLogger(...));

        $this->app->singleton(DriverRegistry::class, $this->makeDriverRegistry(...));
        $this->app->singleton(ThreadRegistry::class, $this->makeThreadRegistry(...));
        $this->app->singleton(ConnectionRegistryInterface::class, $this->makeConnectionRegistry(...));

        $this->app->singleton(BusInterface::class, $this->makeBus(...));

        $this->registerOutboxServiceProvider();
        $this->registerRepeaterServiceProvider();
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/kafka-bus.php' => config_path('kafka-bus.php'),
            __DIR__.'/../migrations/1970_01_01_000000_create_kafka_bus_message_commits_table.php' => database_path('1970_01_01_000000_create_kafka_bus_message_commits_table.php'),
            __DIR__.'/../migrations/1970_01_01_000000_create_kafka_bus_message_fails_table.php' => database_path('1970_01_01_000000_create_kafka_bus_message_fails_table.php'),
            __DIR__.'/../migrations/1970_01_01_000000_create_kafka_bus_producer_messages_table.php' => database_path('1970_01_01_000000_create_kafka_bus_producer_messages_table.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands(KafkaConsumeCommand::class);
        }
    }

    protected function makeTopicRegistry(Application $app): TopicRegistry
    {
        return $app->make(TopicRegistryFactory::class)
            ->create();
    }

    protected function makeProducerStreamFactory(Application $app): ProducerStreamFactoryInterface
    {
        return $app->make($app['config']->get('kafka-bus.producers.stream_factory'));
    }

    protected function makeConsumerStreamFactory(Application $app): ConsumerStreamFactoryInterface
    {
        return $app->make($app['config']->get('kafka-bus.consumers.stream_factory'));
    }

    protected function makePublisherFactory(Application $app): PublisherFactory
    {
        return new PublisherFactory(
            $app->make(ProducerStreamFactoryInterface::class),
            $app->make(TopicRegistry::class),
            $app->make(PublisherRoutesFactory::class)->create()
        );
    }

    protected function makeListenerFactory(Application $app): ListenerFactory
    {
        return new ListenerFactory(
            $app->make(ConsumerStreamFactoryInterface::class),
            $app->make(WorkerRegistryFactory::class)->create()
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

    protected function makeBusLogger(Application $app): BusLoggerInterface
    {
        return new BusLogger(Log::channel($app['config']->get('kafka-bus.log_channel')));
    }

    protected function registerOutboxServiceProvider(): void
    {
        $this->app->register(KafkaBusOutboxServiceProvider::class);
    }

    protected function registerRepeaterServiceProvider(): void
    {
        $this->app->register(KafkaBusRepeaterServiceProvider::class);
    }
}
