<?php

namespace Micromus\KafkaBusLaravel\Factories;

use Illuminate\Config\Repository;
use Micromus\KafkaBus\Bus\Listeners\Workers\Options;
use Micromus\KafkaBus\Bus\Listeners\Workers\Route;
use Micromus\KafkaBus\Bus\Listeners\Workers\Worker;
use Micromus\KafkaBus\Bus\Listeners\Workers\WorkerRegistry;
use Micromus\KafkaBus\Bus\Listeners\Workers\WorkerRoutes;

final class WorkerRegistryFactory
{
    public function __construct(
        protected Repository $configRepository,
        protected OptionsMerger $optionsMerger,
    ) {
    }

    public function create(): WorkerRegistry
    {
        $groupRegistry = new WorkerRegistry();
        $globalOptions = $this->configRepository->get('kafka-bus.consumers', []);
        $workers = $this->configRepository->get('kafka-bus.consumers.workers', []);

        foreach ($workers as $workerName => $worker) {
            if (is_string($worker)) {
                $groupRegistry->add($this->createWorker($workerName, $worker, $globalOptions));

                continue;
            }

            if (isset($worker['handler'])) {
                $groupRegistry->add($this->createWorkerWithOneTopic($workerName, $worker, $globalOptions));

                continue;
            }

            $groupRegistry->add($this->createWorkerWithMultiplyTopics($workerName, $worker, $globalOptions));
        }

        return $groupRegistry;
    }

    private function createWorker(string $topicKey, string $handlerClass, array $globalOptions): Worker
    {
        return new Worker(
            name:  $topicKey,
            routes: $this->makeWorkerRoutes([$topicKey => $handlerClass]),
            options: $this->makeOptions([], $globalOptions)
        );
    }

    private function createWorkerWithMultiplyTopics(string $workerName, array $worker, array $globalOptions): Worker
    {
        return new Worker(
            name:  $workerName,
            routes: $this->makeWorkerRoutes($worker['topics'] ?? []),
            options: $this->makeOptions($worker['options'] ?? [], $globalOptions)
        );
    }

    private function createWorkerWithOneTopic(string $workerName, array $worker, array $globalOptions): Worker
    {
        $topicKey = $worker['topic_key'] ?? $workerName;
        $handlerClass = $worker['handler'];

        return new Worker(
            name:  $workerName,
            routes: $this->makeWorkerRoutes([$topicKey => $handlerClass]),
            options: $this->makeOptions($worker['options'] ?? [], $globalOptions)
        );
    }

    protected function makeWorkerRoutes(array $routes): WorkerRoutes
    {
        $workerRoutes = new WorkerRoutes();

        foreach ($routes as $topicKey => $handlerClass) {
            $workerRoutes->add(new Route($topicKey, $handlerClass));
        }

        return $workerRoutes;
    }

    protected function makeOptions(array $workerOptions, array $globalOptions): Options
    {
        $options = $this->optionsMerger
            ->merge($workerOptions, $globalOptions);

        return new Options(
            additionalOptions: $options['additional_options'],
            middlewares: $options['middlewares'],
            autoCommit: $options['auto_commit'] ?? true,
            consumerTimeout: $options['consumer_timeout'] ?? 5000,
        );
    }
}
