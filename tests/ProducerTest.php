<?php

use Micromus\KafkaBus\Contracts\Bus\Bus;
use Micromus\KafkaBus\Testing\ProducerMessageFaker;

it('can produce message to kafka', function () {
    $produceMessage = new ProducerMessageFaker('test-message');

    config()->set('kafka-bus.topics', [
        'products' => [
            'name' => 'production.fact.products.1',
            'partitions' => 5,
        ],
    ]);

    config()->set('kafka-bus.producers.routes', [
        ProducerMessageFaker::class => [
            'topic_key' => 'products',
            'options' => [],
        ],
    ]);

    resolve(Bus::class)
        ->publish($produceMessage);
});
