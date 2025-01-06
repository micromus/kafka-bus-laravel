<?php

namespace Micromus\KafkaBusLaravel\Components\Repeaters\Converters;

use DateTimeImmutable;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\WorkerConsumerMessageInterface;
use Micromus\KafkaBusLaravel\Components\Repeaters\Models\MessageFailed;
use Ramsey\Uuid\UuidFactory;

final class MessageFailedConverter
{
    public function __construct(
        protected UuidFactory $uuidFactory,
    ) {
    }

    public function convert(WorkerConsumerMessageInterface $message): MessageFailed
    {
        $messageFailed = new MessageFailed();
        $messageFailed->id = $this->generateId();
        $messageFailed->key = $message->key();
        $messageFailed->worker_name = $message->workerName();
        $messageFailed->topic_name = $message->topicName();
        $messageFailed->payload = $message->payload();
        $messageFailed->headers = $message->headers();

        $messageFailed->partition = $message->original()->partition;
        $messageFailed->offset = $message->original()->offset;
        $messageFailed->timestamp = $message->original()->timestamp;

        return $messageFailed;
    }

    private function generateId(): string
    {
        return $this->uuidFactory->uuid7(new DateTimeImmutable())->toString();
    }
}
