<?php

namespace Micromus\KafkaBus\Bus;

use Micromus\KafkaBus\Connections\Registry\ConnectionRegistry;
use Micromus\KafkaBus\Contracts\Consumers\ConsumerStreamFactory;
use Micromus\KafkaBus\Contracts\Producers\ProducerStreamFactory;

class ThreadRegistry
{
    protected array $activeThreads = [];

    public function __construct(
        protected ConnectionRegistry $connectionRegistry,
        protected ProducerStreamFactory $producerStreamFactory,
        protected ConsumerStreamFactory $consumerStreamFactory
    ) {}

    public function thread(string $connectionName): Thread
    {
        if (! isset($this->activeThreads[$connectionName])) {
            $this->activeThreads[$connectionName] = $this->makeThread($connectionName);
        }

        return $this->activeThreads[$connectionName];
    }

    private function makeThread(string $connectionName): Thread
    {
        $connection = $this->connectionRegistry
            ->connection($connectionName);

        return new Thread($connection, $this->producerStreamFactory);
    }
}
