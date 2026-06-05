<?php

declare(strict_types=1);

use Micromus\KafkaBus\Testing\Messages\ConsumerHandlerFaker;
use Micromus\KafkaBusLaravel\Listeners\WorkerFactory;

it('returns empty array when no workers configured', function () {
    config()->set('kafka-bus.consumers.workers', []);

    $names = resolve(WorkerFactory::class)->names();

    expect($names)->toBe([]);
});

it('returns all registered worker names', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.consumers.workers', [
        'products' => ConsumerHandlerFaker::class,
        'orders' => ConsumerHandlerFaker::class,
        'payments' => ConsumerHandlerFaker::class,
    ]);

    $names = resolve(WorkerFactory::class)->names();

    expect($names)->toBe(['products', 'orders', 'payments']);
});

it('preserves worker name order from config', function () {
    config()->set('kafka-bus.topics', [
        'b-topic' => 'test-b-topic',
        'a-topic' => 'test-a-topic',
    ]);
    config()->set('kafka-bus.consumers.workers', [
        'beta' => ConsumerHandlerFaker::class,
        'alpha' => ConsumerHandlerFaker::class,
    ]);

    $names = resolve(WorkerFactory::class)->names();

    expect($names)->toBe(['beta', 'alpha']);
});
