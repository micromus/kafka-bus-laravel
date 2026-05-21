<?php

namespace Micromus\KafkaBusLaravel\Publishers;

use Illuminate\Config\Repository;
use Micromus\KafkaBus\Bus\Publishers\Router\PublisherRoutes;

final readonly class LaravelPublisherRoutesFactory
{
    public function __construct(
        private Repository $config,
        private RouteFactory $routeFactory,
    ) {
    }

    public function create(): PublisherRoutes
    {
        $routes = new PublisherRoutes();
        $messageClasses = array_keys($this->config->get('kafka-bus.producers.routes'));

        foreach ($messageClasses as $messageClass) {
            $routes->add($this->routeFactory->create($messageClass));
        }

        return $routes;
    }
}
