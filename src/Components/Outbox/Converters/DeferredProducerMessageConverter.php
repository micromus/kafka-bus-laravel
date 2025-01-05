<?php

namespace Micromus\KafkaBusLaravel\Components\Outbox\Converters;

use Micromus\KafkaBus\Producers\Messages\ProducerMessage as KafkaBusProducerMessage;
use Micromus\KafkaBusLaravel\Components\Outbox\Models\ProducerMessage;
use Micromus\KafkaBusOutbox\Messages\DeferredOutboxProducerMessage;
use Micromus\KafkaBusOutbox\Messages\OutboxProducerMessage;

final class DeferredProducerMessageConverter
{
    public function convert(ProducerMessage $messageProduce): DeferredOutboxProducerMessage
    {
        return new DeferredOutboxProducerMessage(
            (string) $messageProduce->id,
            new OutboxProducerMessage(
                $messageProduce->connection_name,
                $messageProduce->topic_name,
                new KafkaBusProducerMessage(
                    $messageProduce->payload,
                    $messageProduce->headers,
                    $messageProduce->partition,
                    $messageProduce->key
                )
            )
        );
    }
}
