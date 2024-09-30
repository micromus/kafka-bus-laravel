<?php

namespace Micromus\KafkaBus\Contracts\Producers;

use Micromus\KafkaBus\Contracts\Messages\Message;

interface ProducerStream
{
    /**
     * @param  Message[]  $messages
     */
    public function handle(array $messages): void;
}
