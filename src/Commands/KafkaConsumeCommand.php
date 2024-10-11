<?php

namespace Micromus\KafkaBusLaravel\Commands;

use Illuminate\Console\Command;
use Micromus\KafkaBus\Interfaces\Bus\BusInterface;

final class KafkaConsumeCommand extends Command
{
    protected $signature = 'kafka:consume {listenerGroupName}';

    public function handle(BusInterface $bus): void
    {
        $bus->listen($this->argument('listenerGroupName'));
    }
}
