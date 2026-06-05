<?php

declare(strict_types=1);

namespace Micromus\KafkaBusLaravel\Tests\Fixtures;

use Micromus\KafkaBus\Consumers\Pipelines\MessagePipelineHandler;
use Micromus\KafkaBus\Consumers\Router\MessagePipelineMiddleware;
use Micromus\KafkaBus\Interfaces\Pipelines\PipelineInterface;

final class MessageMiddlewareFake implements MessagePipelineMiddleware
{
    /**
     * @param PipelineInterface<MessagePipelineHandler> $pipeline
     * @return PipelineInterface<MessagePipelineHandler>
     */
    public function handle(PipelineInterface $pipeline): PipelineInterface
    {
        return $pipeline->continue();
    }
}
