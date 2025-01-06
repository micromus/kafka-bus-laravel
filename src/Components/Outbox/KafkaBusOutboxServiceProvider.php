<?php

namespace Micromus\KafkaBusLaravel\Components\Outbox;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Micromus\KafkaBus\Connections\Registry\DriverRegistry;
use Micromus\KafkaBus\Interfaces\BusLoggerInterface;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionInterface;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionRegistryInterface;
use Micromus\KafkaBusLaravel\Components\Outbox\Repositories\ProducerMessageRepository;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Interfaces\Producers\OutboxProducerInterface;
use Micromus\KafkaBusOutbox\Interfaces\Producers\OutboxProducerStreamInterface;
use Micromus\KafkaBusOutbox\Interfaces\Savers\ProducerMessageSaverFactoryInterface;
use Micromus\KafkaBusOutbox\OutboxKafkaConnection;
use Micromus\KafkaBusOutbox\Producers\LoggerOutboxProducer;
use Micromus\KafkaBusOutbox\Producers\OutboxProducer;
use Micromus\KafkaBusOutbox\Producers\OutboxProducerStream;
use Micromus\KafkaBusOutbox\Producers\ProducerBag;
use Micromus\KafkaBusOutbox\Savers\LoggerProducerMessageSaverFactory;
use Micromus\KafkaBusOutbox\Savers\ProducerMessageSaverFactory;

final class KafkaBusOutboxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->afterResolving(DriverRegistry::class, function (DriverRegistry $driverRegistry) {
            $driverRegistry->add('kafka_outbox', $this->makeOutboxDriver(...));

            return $driverRegistry;
        });

        $this->app->bind(
            ProducerMessageRepositoryInterface::class,
            ProducerMessageRepository::class
        );

        $this->app->bind(ProducerMessageSaverFactoryInterface::class, $this->makeProducerMessageSaverFactory(...));
        $this->app->bind(OutboxProducerStreamInterface::class, $this->makeOutboxProducerStream(...));
        $this->app->bind(OutboxProducerInterface::class, $this->makeOutboxProducer(...));
    }

    protected function makeOutboxDriver(array $options): ConnectionInterface
    {
        return new OutboxKafkaConnection(
            producerMessageSaverFactory: $this->app->make(ProducerMessageSaverFactoryInterface::class),
            connectionRegistry: $this->app->make(ConnectionRegistryInterface::class),
            sourceConnectionName: $options['connection_for']
        );
    }

    protected function makeProducerMessageSaverFactory(Application $app): ProducerMessageSaverFactoryInterface
    {
        return new LoggerProducerMessageSaverFactory(
            $app->make(BusLoggerInterface::class),
            new ProducerMessageSaverFactory($this->app->make(ProducerMessageRepositoryInterface::class))
        );
    }

    protected function makeOutboxProducerStream(Application $app): OutboxProducerStreamInterface
    {
        return new OutboxProducerStream(
            $app->make(OutboxProducerInterface::class),
            $app->make(ProducerMessageRepositoryInterface::class),
        );
    }

    protected function makeOutboxProducer(Application $app): OutboxProducerInterface
    {
        return new LoggerOutboxProducer(
            $app->make(BusLoggerInterface::class),
            new OutboxProducer(
                new ProducerBag($app->make(ConnectionRegistryInterface::class))
            )
        );
    }
}
