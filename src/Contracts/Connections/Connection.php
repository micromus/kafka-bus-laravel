<?php

namespace Micromus\KafkaBus\Contracts\Connections;

use Micromus\KafkaBus\Consumers\ConsumerConfiguration;
use Micromus\KafkaBus\Contracts\Consumers\Consumer;
use Micromus\KafkaBus\Contracts\Producers\Producer;
use Micromus\KafkaBus\Producers\ProducerConfiguration;

interface Connection
{
    public function createProducer(string $topic, ProducerConfiguration $configuration): Producer;

    public function createConsumer(string $topic, ConsumerConfiguration $configuration): Consumer;
}
