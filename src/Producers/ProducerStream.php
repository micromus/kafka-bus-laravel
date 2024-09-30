<?php

namespace Micromus\KafkaBus\Producers;

use Micromus\KafkaBus\Contracts\Messages\Message;
use Micromus\KafkaBus\Contracts\Messages\MessagePipeline;
use Micromus\KafkaBus\Contracts\Producers\Producer as ProducerContract;
use Micromus\KafkaBus\Contracts\Producers\ProducerStream as ProducerStreamContract;
use Micromus\KafkaBus\Producers\Messages\ProducerMessage;

class ProducerStream implements ProducerStreamContract
{
    public function __construct(
        protected ProducerContract $producer,
        protected MessagePipeline $messagePipeline
    ) {
    }

    public function handle(array $messages): void
    {
        $producerMessages = array_map(
            fn (Message $message) => $this->mapProducerMessage($message),
            $messages
        );

        $this->producer
            ->produce($producerMessages);
    }

    private function mapProducerMessage(Message $message): ProducerMessage
    {
        return $this->messagePipeline
            ->then($message, fn (Message $message) => new ProducerMessage($message->toPayload()));
    }
}
