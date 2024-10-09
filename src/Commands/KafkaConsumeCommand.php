<?php

namespace Micromus\KafkaBusLaravel\Commands;

use Illuminate\Console\Command;
use Micromus\KafkaBus\Contracts\Bus\Bus;

final class KafkaConsumeCommand extends Command
{
    protected $signature = 'kafka:consume {listenerGroupName}';

    public function handle(Bus $bus): void
    {
        $bus->listen($this->argument('listenerGroupName'));
    }
}
