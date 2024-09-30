<?php

namespace Micromus\KafkaBus\Consumers;

readonly class ConsumerConfiguration
{
    public function __construct(
        public string $groupId,
        public bool $authCommit = true,
        public int $consumerTimeout = 2000
    ) {
    }
}
