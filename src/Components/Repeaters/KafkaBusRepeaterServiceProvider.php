<?php

namespace Micromus\KafkaBusLaravel\Components\Repeaters;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Micromus\KafkaBus\Bus\Listeners\Workers\WorkerRegistry;
use Micromus\KafkaBus\Consumers\Messages\ConsumerMessageHandlerFactory;
use Micromus\KafkaBus\Consumers\Router\ConsumerRouterFactory;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageHandlerFactoryInterface;
use Micromus\KafkaBus\Pipelines\PipelineFactory;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBusLaravel\Components\Repeaters\Repositories\ConsumerMessageFailedRepository;
use Micromus\KafkaBusLaravel\Components\Repeaters\Repositories\ConsumerMessageRepository;
use Micromus\KafkaBusLaravel\Factories\WorkerRegistryFactory;
use Micromus\KafkaBusLaravel\Resolvers\ContainerResolver;
use Micromus\KafkaBusRepeater\Consumers\RepeaterConsumer;
use Micromus\KafkaBusRepeater\Consumers\RepeaterConsumerStream;
use Micromus\KafkaBusRepeater\Consumers\RepeaterHandlers;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageFailedRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\Consumers\RepeaterConsumerInterface;
use Micromus\KafkaBusRepeater\Interfaces\Consumers\RepeaterConsumerStreamInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageSaverInterface;
use Micromus\KafkaBusRepeater\Messages\FailedConsumerMessageSaver;

final class KafkaBusRepeaterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            FailedConsumerMessageSaverInterface::class,
            FailedConsumerMessageSaver::class
        );

        $this->app->bind(
            ConsumerMessageFailedRepositoryInterface::class,
            ConsumerMessageFailedRepository::class
        );

        $this->app->bind(
            ConsumerMessageRepositoryInterface::class,
            ConsumerMessageRepository::class
        );

        $this->app->bind(RepeaterConsumerStreamInterface::class, $this->makeRepeaterConsumerStream(...));
        $this->app->bind(RepeaterConsumerInterface::class, $this->makeRepeaterConsumer(...));
    }

    private function makeRepeaterConsumerStream(Application $app): RepeaterConsumerStreamInterface
    {
        return new RepeaterConsumerStream(
            $app->make(RepeaterConsumerInterface::class),
            $app->make(ConsumerMessageFailedRepositoryInterface::class)
        );
    }

    private function makeRepeaterConsumer(Application $app): RepeaterConsumerInterface
    {
        $containerResolver = new ContainerResolver($app);
        $pipelineFactory = new PipelineFactory($containerResolver);

        return new RepeaterConsumer(
            $app->make(ConsumerMessageFailedRepositoryInterface::class),
            new RepeaterHandlers(
                $app->make(WorkerRegistryFactory::class)->create(),
                new ConsumerMessageHandlerFactory(
                    $pipelineFactory,
                    new ConsumerRouterFactory(
                        $containerResolver,
                        $pipelineFactory,
                        $app->make(TopicRegistry::class)
                    )
                ),
            )
        );
    }
}
