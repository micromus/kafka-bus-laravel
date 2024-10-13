<?php

namespace Micromus\KafkaBusLaravel\Commands;

use Illuminate\Console\Command;
use Micromus\KafkaBus\Bus\Listeners\Listener;
use Micromus\KafkaBus\Exceptions\Consumers\ConsumerException;
use Micromus\KafkaBus\Exceptions\Consumers\MessagesCompletedConsumerException;
use Micromus\KafkaBus\Exceptions\Consumers\TimeoutConsumerException;
use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Symfony\Component\Console\Command\SignalableCommandInterface;

final class KafkaConsumeCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'kafka:consume {listenerGroupName}';

    protected ?Listener $listener;

    public function handle(BusInterface $bus): void
    {
        $listenerGroupName = $this->argument('listenerGroupName');

        try {
            $this->info("Start consuming for \"$listenerGroupName\"");

            $this->listener = $bus->listener($listenerGroupName);
            $this->listener->listen();

            $this->info('Consumer finished');
        }
        catch (MessagesCompletedConsumerException) {
            $this->info('Consumer stopped. Consumer exceeded the maximum number of messages read');
        }
        catch (TimeoutConsumerException) {
            $this->info('Consumer stopped. Consumer exceeded the maximum amount of time');
        }
        catch (ConsumerException $exception) {
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
