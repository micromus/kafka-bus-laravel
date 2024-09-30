<?php

namespace Micromus\KafkaBus\Testing;

use Micromus\KafkaBus\Connections\NullConnection;
use Micromus\KafkaBus\Contracts\Producers\Producer;

class ProducerFaker implements Producer
{
    public function __construct(
        protected NullConnection $connection
    ) {
    }
    public function produce(array $messages): void
    {
        $this->connection->publishedMessages = [
            ...$this->connection->publishedMessages,
            ...$messages
        ];
    }
}
