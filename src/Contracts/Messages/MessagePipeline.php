<?php

namespace Micromus\KafkaBus\Contracts\Messages;

use Closure;

interface MessagePipeline
{
    public function then(mixed $message, Closure $destination): mixed;
}
