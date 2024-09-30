<?php

namespace Micromus\KafkaBus\Connections;

use Micromus\KafkaBus\Contracts\Connections\Connection;
use Micromus\KafkaBus\Contracts\Consumers\Consumer;
use Micromus\KafkaBus\Contracts\Producers\Producer;
use Micromus\KafkaBus\Testing\ProducerFaker;

class NullConnection implements Connection
{
    /**
     * @var Message[]
     */
    public array $publishedMessages = [];

    public function createProducer(string $topic): Producer
    {
        return new ProducerFaker($this);
    }

    public function createConsumer(string $topicKey): Consumer
    {
        // TODO: Implement createConsumer() method.
    }
}
