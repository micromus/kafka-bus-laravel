<?php

namespace Micromus\KafkaBusLaravel\Components\Outbox\Converters;

use Micromus\KafkaBusLaravel\Components\Outbox\Models\ProducerMessage;
use Micromus\KafkaBusOutbox\Messages\OutboxProducerMessage;

final class ProducerMessageConverter
{
    public function convert(OutboxProducerMessage $producerMessage): ProducerMessage
    {
        $message = new ProducerMessage();
        $message->connection_name = $producerMessage->connectionName;
        $message->topic_name = $producerMessage->topicName;
        $message->payload = $producerMessage->original->payload;
        $message->headers = $producerMessage->original->headers;
        $message->partition = $producerMessage->original->partition;
        $message->key = $producerMessage->original->key;

        return $message;
    }
}
