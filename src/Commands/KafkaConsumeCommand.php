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

    protected ?Listener $listener;

    /**
     * @param BusInterface $bus
     * @param LoggerInterface $logger
     * @return void
     *
     * @throws MessageConsumerNotHandledException
     */
    public function handle(BusInterface $bus, LoggerInterface $logger): void
    {
        $workerName = $this->argument('workerName');

        try {
            $this->info("Start consuming for \"$workerName\"");

            $this->listener = $bus->createListener($workerName);
            $this->listener->listen();

            $this->info('Consumer finished');
        }
        catch (ConsumerException $exception) {
            $logger->error($exception->getMessage(), ['exception' => $exception]);

            $this->error("Consumer stopped. Error: {$exception->getMessage()}");
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
