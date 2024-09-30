<?php

namespace Micromus\KafkaBus\Contracts\Consumers;

interface ConsumerStream
{
    public function listen(): void;
}
