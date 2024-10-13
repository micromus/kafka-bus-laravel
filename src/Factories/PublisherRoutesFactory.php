<?php

namespace Micromus\KafkaBusLaravel\Factories;

use Illuminate\Config\Repository;
use Micromus\KafkaBus\Bus\Publishers\Router\Options;
use Micromus\KafkaBus\Bus\Publishers\Router\PublisherRoutes;
use Micromus\KafkaBusLaravel\Exceptions\KafkaBusConfigurationException;

class PublisherRoutesFactory
{
    public function __construct(
        protected Repository $configRepository
    ) {
    }

    public function create(): PublisherRoutes
    {
        $publisherRoutes = new PublisherRoutes();
        $globalOptions = $this->configRepository->get('kafka-bus.producers', []);
        $routes = $this->configRepository->get('kafka-bus.producers.routes', []);

        foreach ($routes as $messageClass => $route) {
            $topicKey = $route['topic_key']
                ?? throw new KafkaBusConfigurationException("Param [kafka-bus.producers.routes.$messageClass.topic_key] is required]");

            $publisherRoutes->add($messageClass, $topicKey, $this->makeOptions($route['options'] ?? [], $globalOptions));
        }

        return $publisherRoutes;
    }

    protected function makeOptions(array $routeOptions, array $globalOptions): Options
    {
        $configuration = [
            ...$globalOptions,
            ...$routeOptions,

            'middlewares' => [
                ...($globalOptions['middlewares'] ?? []),
                ...($routeOptions['middlewares'] ?? []),
            ],

            'additional_options' => [
                ...($globalOptions['additional_options'] ?? []),
                ...($routeOptions['additional_options'] ?? []),
            ],
        ];

        return new Options(
            additionalOptions: $configuration['additional_options'],
            middlewares: $configuration['middlewares'],
            flushTimeout: $configuration['flush_timeout'] ?? 5000,
            flushRetries: $configuration['flush_retries'] ?? 5,
        );
    }
}
