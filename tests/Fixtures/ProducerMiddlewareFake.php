<?php

declare(strict_types=1);

namespace Micromus\KafkaBusLaravel\Tests\Fixtures;

use Micromus\KafkaBus\Interfaces\Pipelines\PipelineInterface;
use Micromus\KafkaBus\Producers\Pipelines\ProducerPipelineHandler;
use Micromus\KafkaBus\Producers\Pipelines\ProducerPipelineMiddleware;

final class ProducerMiddlewareFake implements ProducerPipelineMiddleware
{
    /**
     * @param PipelineInterface<ProducerPipelineHandler> $pipeline
     * @return PipelineInterface<ProducerPipelineHandler>
     */
    public function handle(PipelineInterface $pipeline): PipelineInterface
    {
        return $pipeline->continue();
    }
}
