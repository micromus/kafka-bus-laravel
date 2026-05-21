<?php

declare(strict_types=1);

namespace Micromus\KafkaBusLaravel\Listeners;

use Illuminate\Config\Repository;
use Micromus\KafkaBus\Bus\Listeners\Workers\Worker;

/**
 * @internal
 */
final readonly class WorkerFactory
{
    private array $globalOptions;

    private array $workers;

    public function __construct(Repository $config)
    {
        $this->globalOptions = $config->get('kafka-bus.consumers', []);
        $this->workers = $config->get('kafka-bus.consumers.workers', []);
    }

    public function create(string $workerName): Worker
    {
        $globalOptions = $this->config->get('kafka-bus.consumers', []);

        return new Worker($this->config->get("kafka_bus.workers.$workerName"));
    }
}