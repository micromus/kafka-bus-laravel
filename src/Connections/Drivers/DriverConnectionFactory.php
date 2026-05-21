<?php

namespace Micromus\KafkaBusLaravel\Connections\Drivers;

use Micromus\KafkaBus\Interfaces\Connections\ConnectionConfigInterface;

abstract class DriverConnectionFactory
{
    abstract public function create(array $options): ConnectionConfigInterface;
}
