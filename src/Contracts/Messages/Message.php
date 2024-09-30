<?php

namespace Micromus\KafkaBus\Contracts\Messages;

interface Message
{
    public function toPayload(): mixed;
}
