<?php

namespace Micromus\KafkaBus\Contracts\Producers;

use Micromus\KafkaBus\Contracts\Connections\Connection;

interface ProducerStreamFactory
{
    public function create(Connection $connection, string $messageClass): ProducerStream;
}
