<?php

declare(strict_types=1);

use Micromus\KafkaBus\Bus;
use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionRegistryInterface;
use Micromus\KafkaBus\Testing\Connections\ConnectionRegistryFaker;
use Micromus\KafkaBus\Testing\Consumers\MessageFactory;
use Micromus\KafkaBus\Testing\Messages\ConsumerHandlerFaker;
use Micromus\KafkaBus\Testing\Messages\ProducerMessageFaker;
use Micromus\KafkaBusLaravel\Testing\KafkaBusFaker;
use PHPUnit\Framework\AssertionFailedError;

// -------------------------------------------------------------------------
// make()
// -------------------------------------------------------------------------

it('replaces ConnectionRegistryInterface with ConnectionRegistryFaker', function () {
    KafkaBusFaker::make();

    expect(app(ConnectionRegistryInterface::class))->toBeInstanceOf(ConnectionRegistryFaker::class);
});

it('rebuilds BusInterface so it uses the fake connection', function () {
    KafkaBusFaker::make();

    expect(app(BusInterface::class))->toBeInstanceOf(Bus::class);
});

// -------------------------------------------------------------------------
// Producer — assertPublished
// -------------------------------------------------------------------------

it('asserts published message by class', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.producers.routes', [ProducerMessageFaker::class => 'products']);

    $fake = KafkaBusFaker::make();

    app(BusInterface::class)->publish(new ProducerMessageFaker('hello'));

    $fake->assertPublished(ProducerMessageFaker::class);
});

it('asserts published message with callback condition on ProducerMessage', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.producers.routes', [ProducerMessageFaker::class => 'products']);

    $fake = KafkaBusFaker::make();

    app(BusInterface::class)->publish(new ProducerMessageFaker('hello'));

    $fake->assertPublished(
        ProducerMessageFaker::class,
        fn ($msg) => $msg->payload === 'hello'
    );
});

it('fails assertPublished when message was not published', function () {
    $fake = KafkaBusFaker::make();

    expect(fn () => $fake->assertPublished(ProducerMessageFaker::class))
        ->toThrow(AssertionFailedError::class);
});

it('fails assertPublished when callback does not match', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.producers.routes', [ProducerMessageFaker::class => 'products']);

    $fake = KafkaBusFaker::make();

    app(BusInterface::class)->publish(new ProducerMessageFaker('hello'));

    expect(fn () => $fake->assertPublished(
        ProducerMessageFaker::class,
        fn ($msg) => $msg->payload === 'other'
    ))->toThrow(AssertionFailedError::class);
});

// -------------------------------------------------------------------------
// Producer — assertPublishedTimes
// -------------------------------------------------------------------------

it('asserts published message count', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.producers.routes', [ProducerMessageFaker::class => 'products']);

    $fake = KafkaBusFaker::make();
    $bus  = app(BusInterface::class);

    $bus->publish(new ProducerMessageFaker('a'));
    $bus->publish(new ProducerMessageFaker('b'));

    $fake->assertPublishedTimes(ProducerMessageFaker::class, 2);
});

it('fails assertPublishedTimes when count does not match', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.producers.routes', [ProducerMessageFaker::class => 'products']);

    $fake = KafkaBusFaker::make();

    app(BusInterface::class)->publish(new ProducerMessageFaker('a'));

    expect(fn () => $fake->assertPublishedTimes(ProducerMessageFaker::class, 3))
        ->toThrow(AssertionFailedError::class);
});

// -------------------------------------------------------------------------
// Producer — assertNotPublished / assertNothingPublished
// -------------------------------------------------------------------------

it('asserts message was not published', function () {
    $fake = KafkaBusFaker::make();

    $fake->assertNotPublished(ProducerMessageFaker::class);
});

it('asserts nothing was published', function () {
    $fake = KafkaBusFaker::make();

    $fake->assertNothingPublished();
});

it('fails assertNothingPublished when a message was published', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.producers.routes', [ProducerMessageFaker::class => 'products']);

    $fake = KafkaBusFaker::make();

    app(BusInterface::class)->publish(new ProducerMessageFaker('hello'));

    expect(fn () => $fake->assertNothingPublished())
        ->toThrow(AssertionFailedError::class);
});

// -------------------------------------------------------------------------
// Producer — getPublished / allPublished
// -------------------------------------------------------------------------

it('returns ProducerMessage objects for a given message class', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.producers.routes', [ProducerMessageFaker::class => 'products']);

    $fake = KafkaBusFaker::make();
    $bus  = app(BusInterface::class);

    $bus->publish(new ProducerMessageFaker('one'));
    $bus->publish(new ProducerMessageFaker('two'));

    $messages = $fake->getPublished(ProducerMessageFaker::class);

    expect($messages)->toHaveCount(2)
        ->and($messages[0]->payload)->toBe('one')
        ->and($messages[1]->payload)->toBe('two');
});

it('returns all published messages flat', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.producers.routes', [ProducerMessageFaker::class => 'products']);

    $fake = KafkaBusFaker::make();
    $bus  = app(BusInterface::class);

    $bus->publish(new ProducerMessageFaker('a'));
    $bus->publish(new ProducerMessageFaker('b'));

    expect($fake->allPublished())->toHaveCount(2);
});

it('returns empty array for getPublished when class has no route configured', function () {
    $fake = KafkaBusFaker::make();

    expect($fake->getPublished(ProducerMessageFaker::class))->toBe([]);
});

// -------------------------------------------------------------------------
// Consumer commit assertions
// -------------------------------------------------------------------------

it('processes multiple queued messages in order', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.consumers.workers', [
        'products' => ConsumerHandlerFaker::class,
    ]);

    $fake = KafkaBusFaker::make();

    $factory = MessageFactory::for()
        ->withTopicKey('products');

    $fake->addMessage($factory->make('first'));
    $fake->addMessage($factory->make('second'));

    ob_start();
    $fake->listen('products');
    $output = ob_get_clean();

    expect($output)
        ->toContain('first')
        ->toContain('second');
});

it('asserts message was committed after listen', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.consumers.workers', [
        'products' => ConsumerHandlerFaker::class,
    ]);

    $message = MessageFactory::for()
        ->withTopicKey('products')
        ->withHeaders(['x-version' => '2'])
        ->make('hello');

    $fake = KafkaBusFaker::make();
    $fake->addMessage($message);

    ob_start();
    $fake->listen('products');
    ob_end_clean();

    $fake->assertCommitted('products');
});

it('asserts committed message with callback condition', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.consumers.workers', [
        'products' => ConsumerHandlerFaker::class,
    ]);

    $message = MessageFactory::for()
        ->withTopicKey('products')
        ->withHeaders(['x-version' => '2'])
        ->make('hello');

    $fake = KafkaBusFaker::make();
    $fake->addMessage($message);

    ob_start();
    $fake->listen('products');
    ob_end_clean();

    $fake->assertCommitted(
        'products',
        fn ($msg) => $msg->payload() === 'hello'
            && $msg->headers()['x-version'] === '2'
    );
});

it('asserts committed count', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.consumers.workers', [
        'products' => ConsumerHandlerFaker::class,
    ]);

    $factory = MessageFactory::for()
        ->withTopicKey('products');

    $fake = KafkaBusFaker::make();
    $fake->addMessage($factory->make('a'));
    $fake->addMessage($factory->make('b'));

    ob_start();
    $fake->listen('products');
    ob_end_clean();

    $fake->assertCommittedTimes('products', 2);
});

it('asserts nothing committed before listen', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.consumers.workers', [
        'products' => ConsumerHandlerFaker::class,
    ]);

    $message = MessageFactory::for()
        ->withTopicKey('products')
        ->make('hello');

    $fake = KafkaBusFaker::make();
    $fake->addMessage($message);

    // listen() not called yet
    $fake->assertNothingCommitted();
});

it('returns committed messages for inspection', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.consumers.workers', [
        'products' => ConsumerHandlerFaker::class,
    ]);

    $message = MessageFactory::for()
        ->withTopicKey('products')
        ->withHeaders(['foo' => 'bar'])
        ->make('hello');

    $fake = KafkaBusFaker::make();
    $fake->addMessage($message);

    ob_start();
    $fake->listen('products');
    ob_end_clean();

    $committed = $fake->getCommitted('products');

    expect($committed)->toHaveCount(1)
        ->and($committed[0]->payload())->toBe('hello')
        ->and($committed[0]->headers())->toBe(['foo' => 'bar']);
});

// -------------------------------------------------------------------------
// Chaining
// -------------------------------------------------------------------------

it('supports chaining assertions', function () {
    config()->set('kafka-bus.topics', ['products' => 'test-products-topic']);
    config()->set('kafka-bus.producers.routes', [ProducerMessageFaker::class => 'products']);

    $fake = KafkaBusFaker::make();

    app(BusInterface::class)
        ->publish(new ProducerMessageFaker('hello'));

    $fake
        ->assertPublished(ProducerMessageFaker::class)
        ->assertPublishedTimes(ProducerMessageFaker::class, 1);
});
