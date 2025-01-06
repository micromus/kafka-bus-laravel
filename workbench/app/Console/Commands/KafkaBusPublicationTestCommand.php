<?php

namespace Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Micromus\KafkaBusMessages\DomainEventEnum;
use Workbench\App\Kafka\Messages\ProductAttribute;
use Workbench\App\Kafka\Messages\ProductDomainMessage;

final class KafkaBusPublicationTestCommand extends Command
{
    protected $signature = 'kafka-bus:test {id} {name}';
    protected $description = 'Публикация тестового сообщения';

    public function handle(BusInterface $bus): void
    {
        $bus->publish([
            new ProductDomainMessage(
                attributes: new ProductAttribute(['id' => $this->argument('id'), 'name' => $this->argument('name')]),
                event: DomainEventEnum::Create
            )
        ]);

        $this->info('Сообщение опубликовано');
    }
}
