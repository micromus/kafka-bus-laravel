<?php

declare(strict_types=1);

namespace Micromus\KafkaBusLaravel\Listeners;

use Illuminate\Contracts\Container\BindingResolutionException;
use Micromus\KafkaBus\Bus\Listeners\Workers\Worker;
use Micromus\KafkaBus\Interfaces\Bus\Listeners\WorkerRegistryInterface;

/**
 * @internal
 */
final class LaravelWorkerRegistry implements WorkerRegistryInterface
{
    /**
     * @var array<string, Worker>
     */
    private array $cached = [];

    public function __construct(
        private readonly WorkerFactory $workerFactory
    ) {
    }

    /**
     * @param string $workerName
     * @return Worker|null
     *
     * @throws BindingResolutionException
     */
    public function get(string $workerName): ?Worker
    {
        return $this->cached[$workerName] ??= $this->workerFactory->create($workerName);
    }
}
