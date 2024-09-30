<?php

namespace Micromus\KafkaBus\Consumers\Messages;

use RdKafka\Message;

class ConsumerMessageConverter
{
    public function fromKafka(Message $message): ConsumerMessage
    {
        return new ConsumerMessage(
            $message->payload,
            $message->headers,
            new ConsumerMeta($message)
        );
    }
}
