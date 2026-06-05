<?php

declare(strict_types=1);

namespace Micromus\KafkaBusLaravel\Commands;

use Illuminate\Console\Command;
use Micromus\KafkaBus\Interfaces\Bus\BusInterface;

final class KafkaRouteListCommand extends Command
{
    protected $signature = 'kafka:route:list';
    protected $description = 'Display the list of registered producer message routes';

    public function handle(BusInterface $bus): int
    {
        $routes = $bus->routes();

        if ($routes === []) {
            $this->warn('No routes registered');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($routes as $route) {
            $rows[] = [
                $route->messageClass,
                $route->topic->key,
                $route->topic->name,
                implode("\n", array_map(get_class(...), $route->options->middleware)),
            ];
        }

        $this->table(
            ['Message', 'Topic key', 'Topic name', 'Middleware'],
            $rows
        );

        return self::SUCCESS;
    }
}
