<?php

namespace Workbench\App\Kafka\Consumers;

use Micromus\KafkaBus\Consumers\Attributes\MessageFactory;
use Psr\Log\LoggerInterface;
use Workbench\App\Kafka\Messages\ProductDomainMessage;
use Workbench\App\Kafka\Messages\ProductDomainMessageFactory;

class ProductsTopicConsumer
{
    public function __construct(
        protected LoggerInterface $logger
    ) {
    }

    #[MessageFactory(ProductDomainMessageFactory::class)]
    public function execute(ProductDomainMessage $message): void
    {
        $this->logger
            ->info($message->attributes->name, ['message' => $message]);
    }
}
