<?php

namespace Micromus\KafkaBusLaravel\Producers;

use Illuminate\Foundation\Application;
use Micromus\KafkaBus\Bus\Publishers\Router\Options;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionInterface;
use Micromus\KafkaBus\Interfaces\Producers\ProducerStreamFactoryInterface;
use Micromus\KafkaBus\Interfaces\Producers\ProducerStreamInterface;
use Micromus\KafkaBus\Messages\MessagePipelineFactory;
use Micromus\KafkaBus\Producers\ProducerStreamFactory;
use Micromus\KafkaBus\Topics\Topic;
use Micromus\KafkaBusLaravel\Resolvers\ContainerResolver;

final class LaravelProducerStreamFactory implements ProducerStreamFactoryInterface
{
    protected ProducerStreamFactoryInterface $factory;

    public function __construct(Application $app)
    {
        $this->factory = new ProducerStreamFactory(new MessagePipelineFactory(new ContainerResolver($app)));
    }

    public function create(ConnectionInterface $connection, Topic $topic, Options $options): ProducerStreamInterface
    {
        return $this->factory
            ->create($connection, $topic, $options);
    }
}
