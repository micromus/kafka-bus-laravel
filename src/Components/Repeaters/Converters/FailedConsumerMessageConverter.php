<?php

namespace Micromus\KafkaBusLaravel\Components\Repeaters\Converters;

use Micromus\KafkaBusLaravel\Components\Repeaters\Models\MessageFailed;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Messages\FailedConsumerMessage;
use RdKafka\Message;

final class FailedConsumerMessageConverter
{
    public function convert(MessageFailed $message): FailedConsumerMessageInterface
    {
        return new FailedConsumerMessage(
            $message->id,
            $message->worker_name,
            $this->makeKafkaMessage($message)
        );
    }

    private function makeKafkaMessage(MessageFailed $message): Message
    {
        $kafkaMessage = new Message();
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $kafkaMessage->key = $message->key;
        $kafkaMessage->payload = $message->payload;
        $kafkaMessage->topic_name = $message->topic_name;
        $kafkaMessage->partition = $message->partition;
        $kafkaMessage->offset = $message->offset;
        $kafkaMessage->headers = $message->headers;
        $kafkaMessage->timestamp = $message->timestamp;

        return $kafkaMessage;
    }
}
