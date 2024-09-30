<?php

namespace Micromus\KafkaBus\Producers;

use Micromus\KafkaBus\Contracts\Connections\Connection;
use Micromus\KafkaBus\Contracts\Messages\MessagePipelineFactory;
use Micromus\KafkaBus\Contracts\Producers\ProducerStream as ProducerStreamContract;
use Micromus\KafkaBus\Contracts\Producers\ProducerStreamFactory as ProducerStreamFactoryContract;
use Micromus\KafkaBus\Contracts\TopicNameResolver;
use Micromus\KafkaBus\Exceptions\Producers\RouteProducerException;

class ProducerStreamFactory implements ProducerStreamFactoryContract
{
    public function __construct(
        protected TopicNameResolver $topicNameResolver,
        protected MessagePipelineFactory $messagePipelineFactory,
        protected array $producerConfiguration,
        protected array $routes
    ) {}

    /**
     * @throws RouteProducerException
     */
    public function create(Connection $connection, string $messageClass): ProducerStreamContract
    {
        $rawConfiguration = $this->rawConfiguration($messageClass);
        $configuration = $this->makeProducerConfiguration($rawConfiguration);
        $topicName = $this->topicNameResolver->resolve($rawConfiguration['topic_key']);

        return new ProducerStream(
            $connection->createProducer($topicName, $configuration),
            $this->messagePipelineFactory->create($rawConfiguration['middlewares'])
        );
    }

    /**
     * @throws RouteProducerException
     */
    private function rawConfiguration(string $messageClass): array
    {
        if (! isset($this->routes[$messageClass])) {
            throw new RouteProducerException("Route for message [$messageClass] not found");
        }

        $routeConfiguration = $this->routes[$messageClass];

        if (! isset($routeConfiguration['topic_key'])) {
            throw new RouteProducerException("Parameter \"topic_key\" [$messageClass] is required");
        }

        return [
            ...$this->producerConfiguration,
            ...$routeConfiguration,

            'middlewares' => [
                ...($this->producerConfiguration['middlewares'] ?? []),
                ...($routeConfiguration['middlewares'] ?? []),
            ],
        ];
    }

    private function makeProducerConfiguration(array $rawConfiguration): ProducerConfiguration
    {
        return new ProducerConfiguration(
            compression: $rawConfiguration['compression'] ?? 'snappy',
            flushTimeout: $rawConfiguration['flush_timeout'] ?? 5000,
            flushRetries: $rawConfiguration['flush_retries'] ?? 5
        );
    }
}
