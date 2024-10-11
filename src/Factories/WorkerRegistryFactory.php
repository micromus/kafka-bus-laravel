<?php

namespace Micromus\KafkaBusLaravel\Factories;

use Illuminate\Config\Repository;
use Micromus\KafkaBus\Bus\Listeners\Workers\Options;
use Micromus\KafkaBus\Bus\Listeners\Workers\Route;
use Micromus\KafkaBus\Bus\Listeners\Workers\Worker;
use Micromus\KafkaBus\Bus\Listeners\Workers\WorkerRegistry;
use Micromus\KafkaBus\Bus\Listeners\Workers\WorkerRoutes;
use Micromus\KafkaBus\Messages\NativeMessageFactory;
use Micromus\KafkaBusLaravel\Exceptions\KafkaBusConfigurationException;

class WorkerRegistryFactory
{
    public function __construct(
        protected Repository $configRepository
    ) {
    }

    public function create(): WorkerRegistry
    {
        $groupRegistry = new WorkerRegistry();
        $globalOptions = $this->configRepository->get('kafka_bus.consumers', []);
        $workers = $this->configRepository->get('kafka-bus.consumers.workers', []);

        foreach ($workers as $workerName => $worker) {
            $routes = $this->makeWorkerRoutes($workerName, $worker['topics'] ?? []);
            $options = $this->makeOptions($worker['options'] ?? [], $globalOptions);
            $maxMessages = $worker['max_messages'] ?? -1;
            $maxTime = $worker['max_time'] ?? -1;

            $groupRegistry->add($workerName, new Worker($routes, $options, $maxMessages, $maxTime));
        }

        return $groupRegistry;
    }

    protected function makeWorkerRoutes(string $workerName, array $routes): WorkerRoutes
    {
        $workerRoutes = new WorkerRoutes();

        foreach ($routes as $topicKey => $route) {
            $handlerClass = $route['handler']
                ?? throw new KafkaBusConfigurationException("Param [kafka-bus.consumers.workers.$workerName.topics.$topicKey.handler] is required");

            $messageFactoryClass = $route['message_factory']
                ?? NativeMessageFactory::class;

            $workerRoutes->add(new Route($topicKey, $handlerClass, $messageFactoryClass));
        }

        return $workerRoutes;
    }

    protected function makeOptions(array $groupOptions, array $globalOptions): Options
    {
        $options = [
            ...$globalOptions,
            ...$groupOptions,

            'middlewares' => [
                ...($globalOptions['middlewares'] ?? []),
                ...($groupOptions['middlewares'] ?? []),
            ],

            'additional_options' => [
                ...($globalOptions['additional_options'] ?? []),
                ...($groupOptions['additional_options'] ?? []),
            ]
        ];

        return new Options(
            additionalOptions: $options['additional_options'],
            middlewares: $options['middlewares'],
            autoCommit: $options['auto_commit'] ?? true,
            consumerTimeout: $options['consumer_timeout'] ?? 2000,
        );
    }
}
