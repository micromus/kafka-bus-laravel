<?php

namespace Micromus\KafkaBus\Contracts\Producers;

use Micromus\KafkaBus\Producers\Messages\ProducerMessage;

interface Producer
{
    /**
     * @param  ProducerMessage[]  $messages
     */
    public function produce(array $messages): void;
}
