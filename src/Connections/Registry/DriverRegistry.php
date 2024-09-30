<?php

namespace Micromus\KafkaBus\Connections\Registry;

use Micromus\KafkaBus\Contracts\Connections\Connection;
use Micromus\KafkaBus\Exceptions\Connections\DriverException;

class DriverRegistry
{
    protected array $drivers = [];

    public function add(string $driverName, callable $connectionMaker): void
    {
        $this->drivers[$driverName] = $connectionMaker;
    }

    public function makeConnection(string $driverName, array $options): Connection
    {
        if (! isset($this->drivers[$driverName])) {
            $availableDrivers = implode(', ', array_keys($this->drivers));

            throw new DriverException("Driver [$driverName] not defined. Available drivers: $availableDrivers");
        }

        return call_user_func($this->drivers[$driverName], $options);
    }
}
