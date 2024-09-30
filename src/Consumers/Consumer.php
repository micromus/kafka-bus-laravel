<?php

namespace Micromus\KafkaBus\Consumers;

use Micromus\KafkaBus\Consumers\Messages\ConsumerMessage;
use Micromus\KafkaBus\Consumers\Messages\ConsumerMessageConverter;
use Micromus\KafkaBus\Contracts\Consumers\Consumer as ConsumerContract;
use Micromus\KafkaBus\Exceptions\Consumers\ConsumerException;
use Micromus\KafkaBus\Exceptions\Consumers\MessageConsumerException;
use Micromus\KafkaBus\Support\RetryRepeater;
use RdKafka\Exception;
use RdKafka\KafkaConsumer;

class Consumer implements ConsumerContract
{
    protected ConsumerMessageConverter $consumerMessageNormalizer;

    public function __construct(
        protected KafkaConsumer $consumer,
        protected array $topicNames,
        protected RetryRepeater $retryRepeater = new RetryRepeater,
        protected int $consumerTimeout = 2000
    ) {
        $this->consumerMessageNormalizer = new ConsumerMessageConverter;
        $this->consumer->subscribe($this->topicNames);
    }

    public function __destruct()
    {
        $this->consumer->unsubscribe();
        $this->consumer->close();
    }

    public function getMessage(): ConsumerMessage
    {
        try {
            $message = $this->consumer
                ->consume($this->consumerTimeout);

            if ($message->err !== RD_KAFKA_RESP_ERR_NO_ERROR) {
                throw new MessageConsumerException($message);
            }

            return $this->consumerMessageNormalizer
                ->fromKafka($message);
        } catch (Exception $exception) {
            throw new ConsumerException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function commit(ConsumerMessage $consumerMessage): void
    {
        $this->retryRepeater
            ->execute(function () use ($consumerMessage) {
                $this->consumer
                    ->commit($consumerMessage->meta->message);
            });
    }
}
