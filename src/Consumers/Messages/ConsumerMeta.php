<?php

namespace Micromus\KafkaBus\Consumers\Messages;

use RdKafka\Message;

readonly class ConsumerMeta
{
    public function __construct(
        public Message $message
    ) {
    }
}
