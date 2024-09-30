<?php

namespace Micromus\KafkaBus\Bus;

use Illuminate\Config\Repository;

class BusFactory
{
    public function __construct(
        protected ThreadRegistry $streamRegistry,
        protected Repository $configRepository
    ) {
    }

    public function create(): \Micromus\KafkaBus\Contracts\Bus\Bus
    {
        return new Bus(
            $this->streamRegistry,
            $this->configRepository->get('kafka-bus.default')
        );
    }
}
