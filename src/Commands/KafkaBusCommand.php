<?php

namespace Micromus\KafkaBus\Commands;

use Illuminate\Console\Command;

class KafkaBusCommand extends Command
{
    public $signature = 'laravel-kafka-bus';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
