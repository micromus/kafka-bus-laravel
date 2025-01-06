<?php

namespace Micromus\KafkaBusLaravel\Commands;

use Illuminate\Console\Command;
use Micromus\KafkaBus\Bus\Listeners\Listener;
use Micromus\KafkaBus\Exceptions\Consumers\ConsumerException;
use Micromus\KafkaBus\Exceptions\Consumers\MessageConsumerNotHandledException;
use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\SignalableCommandInterface;

final class KafkaConsumeCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'kafka:consume {workerName}';
    protected $description = 'Reading messages from Apache Kafka by worker name';

    protected ?Listener $listener;

    /**
     * @param BusInterface $bus
     * @param LoggerInterface $logger
     * @return int
     *
     * @throws MessageConsumerNotHandledException
     */
    public function handle(BusInterface $bus, LoggerInterface $logger): int
    {
        $workerName = $this->argument('workerName');

        try {
            $this->info("Start consuming for \"$workerName\"");

            $this->listener = $bus->createListener($workerName);
            $this->listener->listen();

            $this->info('Consumer finished');

            return self::SUCCESS;
        }
        catch (ConsumerException $exception) {
            $logger->error($exception->getMessage(), ['exception' => $exception]);

            $this->error("Consumer stopped. Error: {$exception->getMessage()}");

            return self::FAILURE;
        }
    }

    public function getSubscribedSignals(): array
    {
        return [SIGTERM, SIGINT, SIGQUIT];
    }

    public function handleSignal(int $signal, false|int $previousExitCode = 0): int|false
    {
        $this->listener?->forceStop();

        return $previousExitCode;
    }
}
