<?php

namespace Micromus\KafkaBusLaravel\Resolvers;

use Illuminate\Contracts\Container\Container;
use Micromus\KafkaBus\Interfaces\ResolverInterface;

class ContainerResolver implements ResolverInterface
{
    public function __construct(
        protected Container $container
    ) {}

    public function resolve(string $class): mixed
    {
        return $this->container->make($class);
    }
}
