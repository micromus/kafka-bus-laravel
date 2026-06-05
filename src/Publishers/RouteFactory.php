<?php

namespace Micromus\KafkaBusLaravel\Publishers;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Micromus\KafkaBus\Bus\Publishers\Router\Options;
use Micromus\KafkaBus\Bus\Publishers\Router\Route;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBusLaravel\Exceptions\KafkaBusConfigurationException;
use Micromus\KafkaBusLaravel\Factories\OptionsMerger;

final readonly class RouteFactory
{
    /**
     * @var array<class-string, mixed>
     */
    private array $routes;

    private OptionsMerger $optionsMerger;

    public function __construct(
        private Container $container,
        private TopicRegistry $topicRegistry,
        Repository $config
    ) {
        $this->optionsMerger = new OptionsMerger($config->get('kafka-bus.producers', []));
        $this->routes = $config->get('kafka-bus.producers.routes', []);
    }

    public function create(string $messageClass): Route
    {
        $route = $this->routes[$messageClass]
            ?? throw new KafkaBusConfigurationException("Message [$messageClass] not registered in kafka-bus.php");

        if (is_string($route)) {
            return new Route(
                messageClass: $messageClass,
                topic: $this->topicRegistry->get($route),
                options: $this->makeOptions()
            );
        }

        $topicKey = $route['topic_key']
            ?? throw new KafkaBusConfigurationException("Param [kafka-bus.producers.routes.$messageClass.topic_key] is required]");

        return new Route(
            messageClass: $messageClass,
            topic: $this->topicRegistry->get($topicKey),
            options: $this->makeOptions($route)
        );
    }

    private function makeOptions(array $routeOptions = []): Options
    {
        $options = $this->optionsMerger
            ->merge($routeOptions);

        return new Options(
            additionalOptions: $options['additional_options'],
            middleware: array_map($this->container->make(...), $options['middleware']),
            flushTimeout: $options['flush_timeout'] ?? 5000,
            flushRetries: $options['flush_retries'] ?? 5,
        );
    }
}
