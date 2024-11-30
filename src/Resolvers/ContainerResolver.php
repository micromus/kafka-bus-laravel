<?php

namespace Micromus\KafkaBusLaravel\Resolvers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Micromus\KafkaBus\Interfaces\ResolverInterface;

final class ContainerResolver implements ResolverInterface
{
    public function __construct(
        protected Container $container
    ) {
    }

    /**
     * @param string $class
     * @return mixed
     *
     * @throws BindingResolutionException
     */
    public function resolve(string $class): mixed
    {
        return $this->container->make($class);
    }
}
