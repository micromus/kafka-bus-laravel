<?php

namespace Micromus\KafkaBusLaravel\Components\Outbox\Repositories;

use Micromus\KafkaBusLaravel\Components\Outbox\Converters\DeferredProducerMessageConverter;
use Micromus\KafkaBusLaravel\Components\Outbox\Converters\ProducerMessageConverter;
use Micromus\KafkaBusLaravel\Components\Outbox\Models\ProducerMessage;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;

final class ProducerMessageRepository implements ProducerMessageRepositoryInterface
{
    public function __construct(
        protected DeferredProducerMessageConverter $deferredProducerMessageConverter,
        protected ProducerMessageConverter $messageProduceConverter
    ) {
    }

    public function get(int $limit = 100): array
    {
        $messageForProduceCollection = ProducerMessage::query()
            ->orderBy('id')
            ->limit($limit)
            ->get();

        return $messageForProduceCollection
            ->map($this->deferredProducerMessageConverter->convert(...))
            ->toArray();
    }

    public function save(array $messages): void
    {
        $messages = array_map(
            $this->messageProduceConverter->convert(...),
            $messages
        );

        ProducerMessage::query()
            ->insert($messages);
    }

    public function delete(array $ids): void
    {
        ProducerMessage::query()
            ->whereIn('id', array_map(intval(...), $ids))
            ->delete();
    }
}
