<?php

use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Micromus\KafkaBus\Testing\Messages\ProducerMessageFaker;
use Micromus\KafkaBusLaravel\Facades\KafkaBus;

it('can produce message to kafka', function () {
    config()->set('kafka-bus.topics', ['products' => 'production.fact.products.1']);
    config()->set('kafka-bus.producers.routes', [ProducerMessageFaker::class => 'products']);

    Micromus\KafkaBusLaravel\Facades\KafkaBus::fake();

    resolve(BusInterface::class)
        ->publish(new ProducerMessageFaker('test-message'));

    KafkaBus::assertPublished(ProducerMessageFaker::class);
});
