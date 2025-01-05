<?php

namespace Micromus\KafkaBusLaravel\Components\Repeaters;

use Illuminate\Support\ServiceProvider;
use Micromus\KafkaBusLaravel\Components\Repeaters\Repositories\ConsumerMessageFailedRepository;
use Micromus\KafkaBusLaravel\Components\Repeaters\Repositories\ConsumerMessageRepository;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageFailedRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageRepositoryInterface;
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
    }
}
