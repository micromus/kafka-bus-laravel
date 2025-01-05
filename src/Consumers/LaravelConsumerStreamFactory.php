<?php

namespace Micromus\KafkaBusLaravel\Consumers;

use Illuminate\Foundation\Application;
use Micromus\KafkaBus\Bus\Listeners\Workers\Worker;
use Micromus\KafkaBus\Consumers\ConsumerStreamFactory;
use Micromus\KafkaBus\Consumers\Messages\ConsumerMessageHandlerFactory;
use Micromus\KafkaBus\Consumers\Router\ConsumerRouterFactory;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionInterface;
use Micromus\KafkaBus\Interfaces\Consumers\ConsumerStreamFactoryInterface;
use Micromus\KafkaBus\Interfaces\Consumers\ConsumerStreamInterface;
use Micromus\KafkaBus\Pipelines\PipelineFactory;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBusLaravel\Resolvers\ContainerResolver;

final class LaravelConsumerStreamFactory implements ConsumerStreamFactoryInterface
{
    protected ConsumerStreamFactoryInterface $factory;

    public function __construct(Application $app)
    {
        $containerResolver = new ContainerResolver($app);
        $pipelineFactory = new PipelineFactory($containerResolver);

        $this->factory = new ConsumerStreamFactory(
            new ConsumerMessageHandlerFactory(
                $pipelineFactory,
                new ConsumerRouterFactory(
                    $containerResolver,
                    $pipelineFactory,
                    $app->make(TopicRegistry::class)
                )
            )
        );
    }

    public function create(ConnectionInterface $connection, Worker $worker): ConsumerStreamInterface
    {
        return $this->factory
            ->create($connection, $worker);
    }
}
