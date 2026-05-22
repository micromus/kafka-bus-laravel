<?php

namespace Micromus\KafkaBusLaravel\Connections\Drivers;

use Micromus\KafkaBus\Connections\Config\NullConnectionConfig;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionConfigInterface;

final class NullDriverConnectionFactory extends DriverConnectionFactory
{
    public function create(array $options): ConnectionConfigInterface
    {
        return new NullConnectionConfig();
    }
}
