<?php

namespace Micromus\KafkaBusLaravel\Connections\Drivers;

use Micromus\KafkaBus\Connections\Config\KafkaConnectionConfig;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionConfigInterface;

final class KafkaDriverConnectionFactory extends DriverConnectionFactory
{
    public function create(array $options): ConnectionConfigInterface
    {
        $debug = $options['debug'] ?? false;

        if (isset($options['debug'])) {
            unset($options['debug']);
        }

        return new KafkaConnectionConfig(
            broketList: $options['metadata.broker.list'],
            debug: $debug,
            extra: $options
        );
    }
}
