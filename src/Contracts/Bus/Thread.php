<?php

namespace Micromus\KafkaBus\Contracts\Bus;

use Micromus\KafkaBus\Contracts\Messages\Message;

interface Thread
{
    public function publish(Message $message): void;

    /**
     * @param Message[] $messages
     */
    public function publishMany(array $messages): void;

    public function listen(string $listenerName = null): void;
}
