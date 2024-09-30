<?php

namespace Micromus\KafkaBus\Contracts\Consumers;

use Micromus\KafkaBus\Consumers\Messages\ConsumerMessage;
use Micromus\KafkaBus\Exceptions\Consumers\ConsumerException;

interface Consumer
{
    /**
     * @throws ConsumerException
     */
    public function getMessage(): ConsumerMessage;

    public function commit(ConsumerMessage $consumerMessage): void;
}
