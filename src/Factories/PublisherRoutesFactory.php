<?php

namespace Micromus\KafkaBusLaravel\Factories;

use Illuminate\Config\Repository;
use Micromus\KafkaBus\Bus\Publishers\Router\Options;
use Micromus\KafkaBus\Bus\Publishers\Router\PublisherRoutes;
use Micromus\KafkaBus\Bus\Publishers\Router\Route;
use Micromus\KafkaBusLaravel\Exceptions\KafkaBusConfigurationException;

final class PublisherRoutesFactory
{
    public function __construct(
        protected Repository $configRepository,
        protected OptionsMerger $optionsMerger,
    ) {
    }

    public function create(): PublisherRoutes
    {
        $publisherRoutes = new PublisherRoutes();
        $globalOptions = $this->configRepository->get('kafka-bus.producers', []);
        $routes = $this->configRepository->get('kafka-bus.producers.routes', []);

        foreach ($routes as $messageClass => $route) {
            if (is_string($route)) {
                $publisherRoutes->add($this->createPublisherRouteWithoutOptions($messageClass, $route, $globalOptions));

                continue;
            }

            $topicKey = $route['topic_key']
                ?? throw new KafkaBusConfigurationException("Param [kafka-bus.producers.routes.$messageClass.topic_key] is required]");

            $publisherRoute = new Route($messageClass, $topicKey, $this->makeOptions($route['options'] ?? [], $globalOptions));
            $publisherRoutes->add($publisherRoute);
        }

        return $publisherRoutes;
    }

    protected function createPublisherRouteWithoutOptions(string $messageClass, string $topicKey, array $globalOptions): Route
    {
        return new Route($messageClass, $topicKey, $this->makeOptions([], $globalOptions));
    }

    protected function makeOptions(array $routeOptions, array $globalOptions): Options
    {
        $options = $this->optionsMerger
            ->merge($routeOptions, $globalOptions);

        return new Options(
            additionalOptions: $options['additional_options'],
            middlewares: $options['middlewares'],
            flushTimeout: $options['flush_timeout'] ?? 5000,
            flushRetries: $options['flush_retries'] ?? 5,
        );
    }
}
