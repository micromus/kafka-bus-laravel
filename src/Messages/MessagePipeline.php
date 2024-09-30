<?php

namespace Micromus\KafkaBus\Messages;

use Closure;
use Illuminate\Support\Facades\Pipeline;
use Micromus\KafkaBus\Contracts\Messages\MessagePipeline as MessagePipelineContract;

class MessagePipeline implements MessagePipelineContract
{
    public function __construct(
        protected array $middlewares = []
    ) {
    }

    public function then(mixed $message, Closure $destination): mixed
    {
        return Pipeline::send($message)
            ->through($this->middlewares)
            ->then($destination);
    }
}
