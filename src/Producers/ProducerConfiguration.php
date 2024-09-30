<?php

namespace Micromus\KafkaBus\Producers;

readonly class ProducerConfiguration
{
    public function __construct(
        public string $compression = 'snappy',
        public int $flushTimeout = 5000,
        public int $flushRetries = 10
    ) {
    }
}
