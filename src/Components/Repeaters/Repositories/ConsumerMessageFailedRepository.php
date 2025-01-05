<?php

namespace Micromus\KafkaBusLaravel\Components\Repeaters\Repositories;

use Micromus\KafkaBus\Interfaces\Consumers\Messages\WorkerConsumerMessageInterface;
use Micromus\KafkaBusLaravel\Components\Repeaters\Converters\FailedConsumerMessageConverter;
use Micromus\KafkaBusLaravel\Components\Repeaters\Converters\MessageFailedConverter;
use Micromus\KafkaBusLaravel\Components\Repeaters\Models\MessageFailed;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageFailedRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageInterface;

final class ConsumerMessageFailedRepository implements ConsumerMessageFailedRepositoryInterface
{
    public function __construct(
        protected MessageFailedConverter $messageFailedConverter,
        protected FailedConsumerMessageConverter $failedConsumerMessageConverter
    ) {
    }

    public function get(): ?FailedConsumerMessageInterface
    {
        /** @var MessageFailed|null $messageFailed */
        $messageFailed = MessageFailed::query()
            ->first();

        if (is_null($messageFailed)) {
            return null;
        }

        return $this->failedConsumerMessageConverter
            ->convert($messageFailed);
    }

    public function save(WorkerConsumerMessageInterface $message): FailedConsumerMessageInterface
    {
        $messageFailed = $this->messageFailedConverter->convert($message);
        $messageFailed->save();
    }

    public function delete(string $id): void
    {
        MessageFailed::query()
            ->where('id', $id)
            ->delete();
    }
}
