<?php

namespace Micromus\KafkaBus\Contracts\Consumers;

use Micromus\KafkaBus\Contracts\Connections\Connection;

interface ConsumerStreamFactory
{
    public function create(Connection $connection, string $listenerName = null): ConsumerStream;
}
