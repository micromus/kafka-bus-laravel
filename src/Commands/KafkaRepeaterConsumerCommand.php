<?php

namespace Micromus\KafkaBusLaravel\Commands;

use Illuminate\Console\Command;
use Micromus\KafkaBusRepeater\Interfaces\Consumers\RepeaterConsumerStreamInterface;
use Symfony\Component\Console\Command\SignalableCommandInterface;

final class KafkaRepeaterConsumerCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'kafka:repeater:consume {--once}';
    protected $description = 'Reading messages that ended with an error';

    protected RepeaterConsumerStreamInterface|null $consumerStream = null;

    public function handle(RepeaterConsumerStreamInterface $stream): int
    {
        $this->info('Repeater consumer started');

        $this->consumerStream = $stream;
        $this->consumerStream->process(once: (bool) $this->option('once'));

        $this->info('Repeater consumer finished');

        return self::SUCCESS;
    }

    public function getSubscribedSignals(): array
    {
        return [SIGTERM, SIGINT, SIGQUIT];
    }

    public function handleSignal(int $signal, false|int $previousExitCode = 0): int|false
    {
        $this->consumerStream?->forceStop();

        return $previousExitCode;
    }
}
