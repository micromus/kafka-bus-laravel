<?php

namespace Micromus\KafkaBus\Connections;

use Micromus\KafkaBus\Contracts\Connections\Connection;
use Micromus\KafkaBus\Contracts\Consumers\Consumer;
use Micromus\KafkaBus\Contracts\Producers\Producer;

class KafkaConnection implements Connection
{
    public function createProducer(string $topic, ProducerConfiguration $configuration): Producer
    {
        // TODO: Implement createProducer() method.
    }

    public function createConsumer(string $topic, ConsumerConfiguration $configuration): Consumer
    {
        // TODO: Implement createConsumer() method.
    }
}
