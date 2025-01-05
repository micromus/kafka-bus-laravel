<?php

use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Micromus\KafkaBus\Testing\Messages\ProducerMessageFaker;

it('can produce message to kafka', function () {
    config()->set('kafka-bus.topics', ['products' => 'production.fact.products.1']);
    config()->set('kafka-bus.producers.routes', [ProducerMessageFaker::class => 'products']);

    resolve(BusInterface::class)
        ->publish([new ProducerMessageFaker('test-message')]);
});
