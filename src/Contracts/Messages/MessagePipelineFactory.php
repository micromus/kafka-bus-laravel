<?php

namespace Micromus\KafkaBus\Contracts\Messages;

interface MessagePipelineFactory
{
    public function create(array $middlewares): MessagePipeline;
}
