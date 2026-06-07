<?php

declare(strict_types=1);

namespace Micromus\KafkaBusLaravel\Testing;

use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionRegistryInterface;
use Micromus\KafkaBus\Producers\Messages\ProducerMessage;
use Micromus\KafkaBus\Testing\Connections\ConnectionFaker;
use Micromus\KafkaBus\Testing\Connections\ConnectionRegistryFaker;
use Micromus\KafkaBus\Topics\TopicRegistry;
use PHPUnit\Framework\Assert;
use RdKafka\Message;

final readonly class KafkaBusFaker
{
    private function __construct(
        private ConnectionFaker $connectionFaker,
        private TopicRegistry $topicRegistry = new TopicRegistry(),
    ) {
    }

    /**
     * Replace the ConnectionRegistryInterface binding with a ConnectionRegistryFaker
     * and rebuild the Bus singletons so they use the fake connection.
     * Call this at the start of each test that involves Kafka.
     */
    public static function make(): self
    {
        $topicRegistry = app(TopicRegistry::class);
        $connectionFaker = new ConnectionFaker($topicRegistry);
        $registryFaker = new ConnectionRegistryFaker($connectionFaker);

        app()->instance(ConnectionRegistryInterface::class, $registryFaker);
        app()->forgetInstance(TopicRegistry::class);
        app()->forgetInstance(BusInterface::class);

        return new self($connectionFaker, $topicRegistry);
    }

    // -------------------------------------------------------------------------
    // Producer assertions
    // -------------------------------------------------------------------------

    /**
     * Assert that a message of the given class was published,
     * optionally matching a callback condition on the resulting ProducerMessage.
     *
     * @param class-string $messageClass
     * @param callable(ProducerMessage): bool|null $callback
     */
    public function assertPublished(string $messageClass, ?callable $callback = null): self
    {
        $messages = $this->getPublished($messageClass);

        Assert::assertNotEmpty(
            $messages,
            "Expected [$messageClass] to be published, but it was not."
        );

        if ($callback !== null) {
            $matched = array_values(array_filter($messages, $callback));

            Assert::assertNotEmpty(
                $matched,
                "[$messageClass] was published but no message matched the given condition."
            );
        }

        return $this;
    }

    /**
     * Assert that a message of the given class was published exactly $times times.
     *
     * @param class-string $messageClass
     */
    public function assertPublishedTimes(string $messageClass, int $times): self
    {
        $count = count($this->getPublished($messageClass));

        Assert::assertSame(
            $times,
            $count,
            "Expected [$messageClass] to be published $times time(s), but it was published $count time(s)."
        );

        return $this;
    }

    /**
     * Assert that a message of the given class was NOT published.
     *
     * @param class-string $messageClass
     */
    public function assertNotPublished(string $messageClass): self
    {
        Assert::assertEmpty(
            $this->getPublished($messageClass),
            "Expected [$messageClass] not to be published, but it was."
        );

        return $this;
    }

    /**
     * Assert that no messages were published at all.
     */
    public function assertNothingPublished(): self
    {
        $all = array_merge(...array_values($this->connectionFaker->publishedMessages) ?: [[]]);

        Assert::assertEmpty($all, 'Expected no messages to be published, but some were.');

        return $this;
    }

    // -------------------------------------------------------------------------
    // Published message access
    // -------------------------------------------------------------------------

    /**
     * Return all ProducerMessage objects published for the given message class.
     * The returned objects represent the message after the full producer pipeline
     * (serialisation + middleware), so headers and payload reflect any modifications.
     *
     * @param class-string $messageClass
     * @return list<ProducerMessage>
     */
    public function getPublished(string $messageClass): array
    {
        $topicName = $this->topicNameFor($messageClass);

        if ($topicName === null) {
            return [];
        }

        return $this->connectionFaker->publishedMessages[$topicName] ?? [];
    }

    /**
     * Return all published ProducerMessage objects for every topic, flat.
     *
     * @return list<ProducerMessage>
     */
    public function allPublished(): array
    {
        if (empty($this->connectionFaker->publishedMessages)) {
            return [];
        }

        return array_merge(...array_values($this->connectionFaker->publishedMessages));
    }

    // -------------------------------------------------------------------------
    // Consumer commit assertions (addMessage + listener()->listen() path)
    // -------------------------------------------------------------------------

    /**
     * Assert that at least one message on the given topic was committed,
     * optionally matching a callback condition on the ConsumerMessageInterface.
     *
     * The callback receives a ConsumerMessageInterface instance, so you can inspect
     * payload(), headers(), key(), and — for the addMessage() path — original().
     *
     * @param callable(\Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageInterface): bool|null $callback
     */
    public function assertCommitted(string $topicKey, ?callable $callback = null): self
    {
        $messages = $this->getCommitted($topicKey);

        Assert::assertNotEmpty(
            $messages,
            "Expected a message on topic [$topicKey] to be committed, but none was."
        );

        if ($callback !== null) {
            $matched = array_values(array_filter($messages, $callback));

            Assert::assertNotEmpty(
                $matched,
                "A message on topic [$topicKey] was committed but none matched the given condition."
            );
        }

        return $this;
    }

    /**
     * Assert that exactly $times messages were committed on the given topic.
     */
    public function assertCommittedTimes(string $topicKey, int $times): self
    {
        $count = count($this->getCommitted($topicKey));

        Assert::assertSame(
            $times,
            $count,
            "Expected $times committed message(s) on topic [$topicKey], but got $count."
        );

        return $this;
    }

    /**
     * Assert that no messages were committed at all.
     */
    public function assertNothingCommitted(): self
    {
        $all = array_merge(...array_values($this->connectionFaker->committedMessages) ?: [[]]);

        Assert::assertEmpty($all, 'Expected no messages to be committed, but some were.');

        return $this;
    }

    /**
     * Return all committed ConsumerMessageInterface objects for the given topic key.
     *
     * @return list<\Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageInterface>
     */
    public function getCommitted(string $topicKey): array
    {
        $topicName = $this->topicRegistry->getTopicName($topicKey);

        return $this->connectionFaker->committedMessages[$topicName] ?? [];
    }

    // -------------------------------------------------------------------------
    // Consumer — queue messages for listener()->listen()
    // -------------------------------------------------------------------------
    public function addMessage(Message $message): self
    {
        $this->connectionFaker->addMessage($message);

        return $this;
    }

    public function listen(string $workerName): void
    {
        app(BusInterface::class)
            ->listener($workerName)
            ->listen();
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    /**
     * Resolve the full topic name for a given message class via the bus routes.
     * Returns null when no route is configured for the class.
     *
     * @param class-string $messageClass
     */
    private function topicNameFor(string $messageClass): ?string
    {
        foreach (app(BusInterface::class)->routes() as $route) {
            if ($route->messageClass === $messageClass) {
                return $route->topic->name;
            }
        }

        return null;
    }
}
