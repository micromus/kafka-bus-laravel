<?php

namespace Micromus\KafkaBusLaravel;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Micromus\KafkaBus\Bus;
use Micromus\KafkaBus\Bus\Listeners\ListenerFactory;
use Micromus\KafkaBus\Bus\Publishers\PublisherFactory;
use Micromus\KafkaBus\Bus\ThreadRegistry;
use Micromus\KafkaBus\Connections\Registry\ConnectionRegistry;
use Micromus\KafkaBus\Connections\Registry\DriverRegistry;
use Micromus\KafkaBus\Consumers\ConsumerStreamFactory;
use Micromus\KafkaBus\Consumers\Router\ConsumerRouterFactory;
use Micromus\KafkaBus\Contracts\Bus\Bus as BusContract;
use Micromus\KafkaBus\Contracts\Connections\ConnectionRegistry as ConnectionRegistryContract;
use Micromus\KafkaBus\Contracts\Consumers\ConsumerStreamFactory as ConsumerStreamFactoryContract;
use Micromus\KafkaBus\Contracts\Producers\ProducerStreamFactory as ProducerStreamFactoryContract;
use Micromus\KafkaBus\Messages\MessagePipelineFactory;
use Micromus\KafkaBus\Producers\ProducerStreamFactory;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBusLaravel\Commands\KafkaConsumeCommand;
use Micromus\KafkaBusLaravel\Factories\PublisherRoutesFactory;
use Micromus\KafkaBusLaravel\Factories\TopicRegistryFactory;
use Micromus\KafkaBusLaravel\Factories\WorkerRegistryFactory;
use Micromus\KafkaBusLaravel\Resolvers\ContainerResolver;

class KafkaBusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kafka-bus.php', 'kafka-bus');

        $this->app->bind(TopicRegistry::class, $this->makeTopicRegistry(...));

        $this->app->bind(ProducerStreamFactoryContract::class, $this->makeProducerStreamFactory(...));
        $this->app->bind(PublisherFactory::class, $this->makePublisherFactory(...));

        $this->app->bind(ConsumerStreamFactoryContract::class, $this->makeConsumerStreamFactory(...));
        $this->app->bind(ListenerFactory::class, $this->makeListenerFactory(...));

        $this->app->singleton(DriverRegistry::class, $this->makeDriverRegistry(...));
        $this->app->singleton(ThreadRegistry::class, $this->makeThreadRegistry(...));
        $this->app->singleton(ConnectionRegistryContract::class, $this->makeConnectionRegistry(...));

        $this->app->singleton(BusContract::class, $this->makeBus(...));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/kafka-bus.php' => config_path('kafka-bus.php'),
        ]);

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

    protected function makeProducerStreamFactory(Application $app): ProducerStreamFactoryContract
    {
        return new ProducerStreamFactory(
            new MessagePipelineFactory,
        );
    }

    protected function makeConsumerStreamFactory(Application $app): ConsumerStreamFactoryContract
    {
        return new ConsumerStreamFactory(
            new MessagePipelineFactory,
            new ConsumerRouterFactory(
                new ContainerResolver($app),
                $app->make(TopicRegistry::class),
            )
        );
    }

    protected function makePublisherFactory(Application $app): PublisherFactory
    {
        return new PublisherFactory(
            $app->make(ProducerStreamFactoryContract::class),
            $app->make(TopicRegistry::class),
            $app->make(PublisherRoutesFactory::class)->create()
        );
    }

    protected function makeListenerFactory(Application $app): ListenerFactory
    {
        return new ListenerFactory(
            $app->make(ConsumerStreamFactoryContract::class),
            $app->make(WorkerRegistryFactory::class)->create()
        );
    }

    protected function makeThreadRegistry(Application $app): ThreadRegistry
    {
        return new ThreadRegistry(
            $app->make(ConnectionRegistryContract::class),
            $app->make(PublisherFactory::class),
            $app->make(ListenerFactory::class),
        );
    }

    protected function makeBus(Application $app): BusContract
    {
        return new Bus(
            $app->make(ThreadRegistry::class),
            $app['config']->get('kafka-bus.default')
        );
    }

    protected function makeDriverRegistry(): DriverRegistry
    {
        return new DriverRegistry;
    }

    protected function makeConnectionRegistry(Application $app): ConnectionRegistryContract
    {
        return new ConnectionRegistry(
            $app->make(DriverRegistry::class),
            $app['config']->get('kafka-bus.connections', [])
        );
    }
}
