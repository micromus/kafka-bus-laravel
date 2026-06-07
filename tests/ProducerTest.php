<?php

use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Micromus\KafkaBus\Testing\Messages\ProducerMessageFaker;
use Micromus\KafkaBusLaravel\Testing\KafkaBusFaker;

it('can produce message to kafka', function () {
    config()->set('kafka-bus.topics', ['products' => 'production.fact.products.1']);
    config()->set('kafka-bus.producers.routes', [ProducerMessageFaker::class => 'products']);

    $faker = KafkaBusFaker::make();

    resolve(BusInterface::class)
        ->publish(new ProducerMessageFaker('test-message'));

    $faker->assertPublished(ProducerMessageFaker::class);
});
