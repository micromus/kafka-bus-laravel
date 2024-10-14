<?php

use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Micromus\KafkaBus\Testing\Messages\ProducerMessageFaker;

it('can produce message to kafka', function () {
    $produceMessage = new ProducerMessageFaker('test-message');

    config()->set('kafka-bus.topics', [
        'products' => 'production.fact.products.1',
    ]);

    config()->set('kafka-bus.producers.routes', [
        ProducerMessageFaker::class => [
            'topic_key' => 'products',
            'options' => [],
        ],
    ]);

    resolve(BusInterface::class)
        ->publish($produceMessage);
});
