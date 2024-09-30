<?php

namespace Micromus\KafkaBus\Connections\Registry;

use Illuminate\Config\Repository;

class ConnectionRegistryFactory
{
    public function __construct(
        protected DriverRegistry $driverRegistry,
        protected Repository $configRepository
    ) {
    }

    public function create(): ConnectionRegistry
    {
        return new ConnectionRegistry(
            $this->driverRegistry,
            $this->configRepository->get('kafka-bus.default'),
            $this->configRepository->get('kafka-bus.connections')
        );
    }
}
