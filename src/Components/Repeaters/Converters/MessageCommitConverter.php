<?php

namespace Micromus\KafkaBusLaravel\Components\Repeaters\Converters;

use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use Micromus\KafkaBusLaravel\Components\Repeaters\Models\MessageCommit;

final class MessageCommitConverter
{
    public function convert(ConsumerMessageInterface $message): MessageCommit
    {
        $messageCommit = new MessageCommit();
        $messageCommit->id = $message->msgId();
        $messageCommit->topic_name = $message->topicName();
        $messageCommit->key = $message->key();
        $messageCommit->timestamp = $message->original()->timestamp;

        return $messageCommit;
    }
}
