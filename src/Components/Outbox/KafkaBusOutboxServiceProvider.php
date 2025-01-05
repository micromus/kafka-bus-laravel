<?php

namespace Micromus\KafkaBusLaravel\Components\Outbox;

use Illuminate\Support\ServiceProvider;
use Micromus\KafkaBus\Connections\Registry\DriverRegistry;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionInterface;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionRegistryInterface;
use Micromus\KafkaBusLaravel\Components\Outbox\Repositories\ProducerMessageRepository;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\OutboxKafkaConnection;

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
    }

    protected function makeOutboxDriver(array $options): ConnectionInterface
    {
        return new OutboxKafkaConnection(
            producerMessageRepository: $this->app->make(ProducerMessageRepositoryInterface::class),
            connectionRegistry: $this->app->make(ConnectionRegistryInterface::class),
            sourceConnectionName: $options['connection_for']
        );
    }
}
