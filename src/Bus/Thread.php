<?php

namespace Micromus\KafkaBus\Bus;

use Micromus\KafkaBus\Contracts\Bus\Thread as ThreadContract;
use Micromus\KafkaBus\Contracts\Connections\Connection;
use Micromus\KafkaBus\Contracts\Consumers\ConsumerStreamFactory;
use Micromus\KafkaBus\Contracts\Messages\Message;
use Micromus\KafkaBus\Contracts\Producers\ProducerStream;
use Micromus\KafkaBus\Contracts\Producers\ProducerStreamFactory;

class Thread implements ThreadContract
{
    protected array $activeProducerStreams = [];

    public function __construct(
        protected Connection $connection,
        protected ProducerStreamFactory $producerStreamFactory,
        protected ConsumerStreamFactory $consumerStreamFactory
    ) {
    }

    public function publish(Message $message): void
    {
        $this->publishMany([$message]);
    }

    public function publishMany(array $messages): void
    {
        $groupMessages = $this->groupMessagesByClass($messages);

        foreach ($groupMessages as $messageClass => $messages) {
            $this->getOrCreateProducerStream($messageClass)
                ->handle($messages);
        }
    }

    private function groupMessagesByClass(array $messages): array
    {
        $result = [];

        foreach ($messages as $message) {
            $result[get_class($message)][] = $message;
        }

        return $result;
    }

    private function getOrCreateProducerStream(string $messageClass): ProducerStream
    {
        if (!isset($this->activeProducerStreams[$messageClass])) {
            $this->activeProducerStreams[$messageClass] = $this->producerStreamFactory
                ->create($this->connection, $messageClass);
        }

        return $this->activeProducerStreams[$messageClass];
    }

    public function listen(string $listenerName = null): void
    {
        $this->consumerStreamFactory
            ->create($this->connection, $listenerName)
            ->listen();
    }
}
