<?php

namespace Micromus\KafkaBus\Producers\Messages;

class ProducerMessage
{
    /**
     * @param string $payload
     * @param array<string, string> $headers
     */
    public function __construct(
        public string $payload,
        public array $headers = []
    ) {
    }
}
