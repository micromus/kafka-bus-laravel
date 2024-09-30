<?php

namespace Micromus\KafkaBus\Messages;

use Micromus\KafkaBus\Contracts\Messages\MessagePipeline as MessagePipelineContract;
use Micromus\KafkaBus\Contracts\Messages\MessagePipelineFactory as MessagePipelineFactoryContract;

class MessagePipelineFactory implements MessagePipelineFactoryContract
{
    public function create(array $middlewares): MessagePipelineContract
    {
        return new MessagePipeline($middlewares);
    }
}
