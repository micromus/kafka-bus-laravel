<?php

namespace Micromus\KafkaBusLaravel\Commands;

use Illuminate\Console\Command;
use Micromus\KafkaBusOutbox\Interfaces\Producers\OutboxProducerStreamInterface;
use Symfony\Component\Console\Command\SignalableCommandInterface;

final class KafkaOutboxProducerCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'kafka:outbox:produce {--once}';
    protected $description = 'Publishing messages that are in the buffer';

    protected OutboxProducerStreamInterface|null $producerStream = null;

    public function handle(OutboxProducerStreamInterface $stream): int
    {
        $this->info('Outbox producer started');

        $this->producerStream = $stream;
        $this->producerStream->process(once: (bool) $this->option('once'));

        $this->info('Outbox producer finished');

        return self::SUCCESS;
    }

    public function getSubscribedSignals(): array
    {
        return [SIGTERM, SIGINT, SIGQUIT];
    }

    public function handleSignal(int $signal, false|int $previousExitCode = 0): int|false
    {
        $this->producerStream?->forceStop();

        return $previousExitCode;
    }
}
