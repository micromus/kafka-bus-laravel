<?php

namespace Micromus\KafkaBus\Connections\Registry;

use Micromus\KafkaBus\Contracts\Connections\Connection;
use Micromus\KafkaBus\Exceptions\Connections\ConnectionException;

class ConnectionRegistry
{
    protected array $activeConnections = [];

    public function __construct(
        protected DriverRegistry $driverRegistry,
        protected string $defaultConnection,
        protected array $connectionsConfig
    ) {
    }

    public function connection(string $connectionName = null): Connection
    {
        $connectionName = $connectionName ?: $this->defaultConnection;

        if (!isset($this->activeConnections[$connectionName])) {
            $this->activeConnections[] = $this->makeConnection($connectionName);
        }

        return $this->activeConnections[$connectionName];
    }

    private function makeConnection(string $connectionName): Connection
    {
        $config = $this->getConnectionConfig($connectionName);

        return $this->driverRegistry->makeConnection($config['driver'], $config['options'] ?: []);
    }

    private function getConnectionConfig(string $connectionName): array
    {
        if (!isset($this->connectionsConfig[$connectionName])) {
            $availableConnections = implode(', ', array_keys($this->connectionsConfig));

            throw new ConnectionException(
                "Connection [$connectionName] not defined kafka-bus.connections.".
                    " Available connections: $availableConnections"
            );
        }

        return $this->connectionsConfig[$connectionName];
    }
}
