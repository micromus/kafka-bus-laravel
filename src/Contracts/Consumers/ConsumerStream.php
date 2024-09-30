<?php

namespace Micromus\KafkaBus\Contracts\Consumers;

use Micromus\KafkaBus\Consumers\Messages\ConsumerMessage;

interface ConsumerStream
{
    public function listen(): void;
}
