<?php

namespace Micromus\KafkaBusLaravel\Connections;

use Illuminate\Container\Container;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionConfigInterface;
use Micromus\KafkaBusLaravel\Connections\Drivers\DriverConnectionFactory;
use Micromus\KafkaBusLaravel\Connections\Drivers\KafkaDriverConnectionFactory;
use Micromus\KafkaBusLaravel\Connections\Drivers\NullDriverConnectionFactory;
use Micromus\KafkaBusLaravel\Exceptions\KafkaBusConfigurationException;

final readonly class ConnectionFactory
{
    public function __construct(
        private Container $container,
    ) {
    }

    public function create(string $driver, array $options): ConnectionConfigInterface
    {
        $factoryClass = match ($driver) {
            'null' => NullDriverConnectionFactory::class,
            'kafka' => KafkaDriverConnectionFactory::class,
            default => $driver,
        };

        if (is_subclass_of($factoryClass, DriverConnectionFactory::class)) {
            return $this->container->make($factoryClass)
                ->create($options);
        }

        throw new KafkaBusConfigurationException("Unsupported driver [$driver]");
    }
}
