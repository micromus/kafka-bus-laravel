<?php

namespace Workbench\App\Kafka\Consumers;

use Micromus\KafkaBus\Consumers\Messages\ConsumerMessage;
use Psr\Log\LoggerInterface;

class ProductsTopicConsumer
{
    public function __construct(
        protected LoggerInterface $logger
    ) {
    }

    public function execute(ConsumerMessage $message): void
    {
        $this->logger->info($message->payload, ['message' => $message]);
    }
}
