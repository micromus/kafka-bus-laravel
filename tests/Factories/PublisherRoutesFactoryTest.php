<?php

use Micromus\KafkaBus\Bus\Publishers\Router\PublisherRoutes;
use Micromus\KafkaBus\Testing\Messages\ProducerMessageFaker;
use Micromus\KafkaBusLaravel\Factories\PublisherRoutesFactory;

it('create publisher routes', function () {
    config()->set('kafka-bus.producers', [
        'additional_options' => [
            'test.option' => 'bar',
            'not.override' => 'test-value',
        ],

        'middlewares' => [
            'FirstMiddlewareOnlyGlobal',
            'MiddlewareClass',
        ],

        'flush_timeout' => 10_000,
        'flush_retries' => 10,
    ]);

    config()->set('kafka-bus.producers.routes', [
        ProducerMessageFaker::class => [
            'topic_key' => 'products',
            'options' => [
                'additional_options' => [
                    'test.option' => 'foo',
                    'new.option' => 'bar',
                ],

                'middlewares' => [
                    'FirstMiddlewareOnlyGlobal',
                    'OtherMiddlewareClass',
                ],

                'flush_timeout' => 15_000,
                'flush_retries' => 15,
            ],
        ],
    ]);

    /** @var PublisherRoutes $routes */
    $routes = resolve(PublisherRoutesFactory::class)
        ->create();

    $route = $routes->get(ProducerMessageFaker::class);

    expect($route->topicKey)->toBe('products')
        ->and($route->options->additionalOptions)->toEqual([
            'test.option' => 'foo',
            'not.override' => 'test-value',
            'new.option' => 'bar',
        ])
        ->and($route->options->middlewares)->toEqual([
            'MiddlewareClass',
            'FirstMiddlewareOnlyGlobal',
            'OtherMiddlewareClass',
        ])
        ->and($route->options->flushTimeout)->toEqual(15_000)
        ->and($route->options->flushRetries)->toEqual(15);
});

it('create publisher routes by short config', function () {
    config()->set('kafka-bus.producers', [
        'additional_options' => [
            'test.option' => 'bar',
            'not.override' => 'test-value',
        ],

        'middlewares' => [
            'FirstMiddlewareOnlyGlobal',
            'MiddlewareClass',
        ],

        'flush_timeout' => 10_000,
        'flush_retries' => 10,
    ]);

    config()->set('kafka-bus.producers.routes', [
        ProducerMessageFaker::class => 'products',
    ]);

    /** @var PublisherRoutes $routes */
    $routes = resolve(PublisherRoutesFactory::class)
        ->create();

    $route = $routes->get(ProducerMessageFaker::class);

    expect($route->topicKey)->toBe('products')
        ->and($route->options->additionalOptions)->toEqual([
            'test.option' => 'bar',
            'not.override' => 'test-value',
        ])
        ->and($route->options->middlewares)->toEqual([
            'FirstMiddlewareOnlyGlobal',
            'MiddlewareClass',
        ])
        ->and($route->options->flushTimeout)->toEqual(10_000)
        ->and($route->options->flushRetries)->toEqual(10);
});
