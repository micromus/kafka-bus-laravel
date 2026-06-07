<?php

declare(strict_types=1);

namespace Micromus\KafkaBusLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use Micromus\KafkaBus\Bus\Listeners\Listener;
use Micromus\KafkaBus\Bus\MessageBatch;
use Micromus\KafkaBus\Bus\Publishers\Router\Route;
use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Micromus\KafkaBus\Interfaces\Bus\ThreadInterface;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use Micromus\KafkaBus\Interfaces\Producers\Messages\ProducerMessageInterface;
use Micromus\KafkaBus\Producers\Messages\ProducerMessage;
use Micromus\KafkaBusLaravel\Testing\FakeBus;
use RdKafka\Message;

/**
 * @method static list<Route> routes()
 * @method static void publish(ProducerMessageInterface $message)
 * @method static void publishBatch(MessageBatch $messageBatch)
 * @method static Listener listener(string $listenerWorkerName)
 * @method static ThreadInterface onConnection(string $connectionName)
 *
 * @method static void assertPublished(string $messageClass, callable|null $callback = null)
 * @method static void assertPublishedTimes(string $messageClass, int $times)
 * @method static void assertNotPublished(string $messageClass)
 * @method static void assertNothingPublished()
 * @method static void assertCommitted(string $topicKey, callable|null $callback = null)
 * @method static void assertCommittedTimes(string $topicKey, int $times)
 * @method static void assertNothingCommitted()
 * @method static list<ProducerMessage> getPublished(string $messageClass)
 * @method static list<ProducerMessage> allPublished()
 * @method static list<ConsumerMessageInterface> getCommitted(string $topicKey)
 * @method static void addMessage(Message $message)
 * @method static void listen(string $workerName)
 *
 * @see FakeBus
 */
class KafkaBus extends Facade
{
    public static function fake(): void
    {
        self::swap(FakeBus::make());
    }

    protected static function getFacadeAccessor(): string
    {
        return BusInterface::class;
    }
}
