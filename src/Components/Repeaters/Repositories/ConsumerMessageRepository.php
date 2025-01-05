<?php

namespace Micromus\KafkaBusLaravel\Components\Repeaters\Repositories;

use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use Micromus\KafkaBusLaravel\Components\Repeaters\Converters\MessageCommitConverter;
use Micromus\KafkaBusLaravel\Components\Repeaters\Models\MessageCommit;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageRepositoryInterface;

final class ConsumerMessageRepository implements ConsumerMessageRepositoryInterface
{
    public function __construct(
        protected MessageCommitConverter $messageCommitConverter,
    ) {
    }

    public function commit(ConsumerMessageInterface $message): void
    {
        $messageCommit = $this->messageCommitConverter->convert($message);
        $messageCommit->save();
    }

    public function exists(ConsumerMessageInterface $message): bool
    {
        return MessageCommit::query()
            ->where('id', $message->msgId())
            ->exists();
    }
}
