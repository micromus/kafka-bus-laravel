<?php

use Micromus\KafkaBusLaravel\Factories\WorkerRegistryFactory;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

it('create worker', function () {
    config()->set('kafka-bus.consumers', [
        'additional_options' => [
            'test.option' => 'bar',
            'not.override' => 'test-value',
        ],

        'middlewares' => [
            'FirstMiddlewareOnlyGlobal',
            'MiddlewareClass',
        ],

        'consume_timeout' => 5_000,
        'auto_commit' => true,
    ]);

    config()->set('kafka-bus.consumers.workers', [
        'default-worker' => [
            'options' => [
                'additional_options' => [
                    'test.option' => 'foo',
                    'new.option' => 'bar',
                ],

                'middlewares' => [
                    'FirstMiddlewareOnlyGlobal',
                    'OtherMiddlewareClass',
                ],
            ],

            'topics' => [
                'products' => 'HandlerClass',
            ],
        ],
    ]);

    /** @var \Micromus\KafkaBus\Bus\Listeners\Workers\Worker $worker */
    $worker = resolve(WorkerRegistryFactory::class)
        ->create()
        ->get('default-worker');

    assertEquals($worker->options->additionalOptions, [
        'test.option' => 'foo',
        'not.override' => 'test-value',
        'new.option' => 'bar',
    ]);

    assertEquals($worker->options->consumerTimeout, 5_000);

    assertEquals($worker->options->middlewares, ['MiddlewareClass', 'FirstMiddlewareOnlyGlobal', 'OtherMiddlewareClass']);

    $routes = $worker->routes->all();

    assertCount(1, $routes);
    assertEquals('products', $routes['products']->topicKey);
    assertEquals('HandlerClass', $routes['products']->handlerClass);
});

it('create worker with short configuration', function () {
    config()->set('kafka-bus.consumers', [
        'additional_options' => [
            'test.option' => 'bar',
            'not.override' => 'test-value',
        ],

        'middlewares' => [
            'MiddlewareClass',
        ],

        'consume_timeout' => 5_000,
        'auto_commit' => true,
    ]);

    config()->set('kafka-bus.consumers.workers', [
        'products' => 'HandlerClass',
    ]);

    /** @var \Micromus\KafkaBus\Bus\Listeners\Workers\Worker $worker */
    $worker = resolve(WorkerRegistryFactory::class)
        ->create()
        ->get('products');

    assertEquals($worker->options->additionalOptions, [
        'test.option' => 'bar',
        'not.override' => 'test-value',
    ]);

    assertEquals($worker->options->consumerTimeout, 5_000);

    assertEquals($worker->options->middlewares, ['MiddlewareClass']);

    $routes = $worker->routes->all();

    assertCount(1, $routes);
    assertEquals('products', $routes['products']->topicKey);
    assertEquals('HandlerClass', $routes['products']->handlerClass);
});

it('create worker with consume one topic', function () {
    config()->set('kafka-bus.consumers', [
        'additional_options' => [
            'test.option' => 'bar',
            'not.override' => 'test-value',
        ],

        'middlewares' => [
            'FirstMiddlewareOnlyGlobal',
            'MiddlewareClass',
        ],

        'consume_timeout' => 5_000,
        'auto_commit' => true,
    ]);

    config()->set('kafka-bus.consumers.workers', [
        'products' => [
            'options' => [
                'additional_options' => [
                    'test.option' => 'foo',
                    'new.option' => 'bar',
                ],

                'middlewares' => [
                    'FirstMiddlewareOnlyGlobal',
                    'OtherMiddlewareClass',
                ],
            ],

            'handler' => 'HandlerClass'
        ],
    ]);

    /** @var \Micromus\KafkaBus\Bus\Listeners\Workers\Worker $worker */
    $worker = resolve(WorkerRegistryFactory::class)
        ->create()
        ->get('products');

    assertEquals($worker->options->additionalOptions, [
        'test.option' => 'foo',
        'not.override' => 'test-value',
        'new.option' => 'bar',
    ]);

    assertEquals($worker->options->consumerTimeout, 5_000);

    assertEquals($worker->options->middlewares, ['MiddlewareClass', 'FirstMiddlewareOnlyGlobal', 'OtherMiddlewareClass']);

    $routes = $worker->routes->all();

    assertCount(1, $routes);
    assertEquals('products', $routes['products']->topicKey);
    assertEquals('HandlerClass', $routes['products']->handlerClass);
});

it('create worker with consume one topic with custom topic key', function () {
    config()->set('kafka-bus.consumers', [
        'additional_options' => [
            'test.option' => 'bar',
            'not.override' => 'test-value',
        ],

        'middlewares' => [
            'FirstMiddlewareOnlyGlobal',
            'MiddlewareClass',
        ],

        'consume_timeout' => 5_000,
        'auto_commit' => true,
    ]);

    config()->set('kafka-bus.consumers.workers', [
        'products_other' => [
            'options' => [
                'additional_options' => [
                    'test.option' => 'foo',
                    'new.option' => 'bar',
                ],

                'middlewares' => [
                    'FirstMiddlewareOnlyGlobal',
                    'OtherMiddlewareClass',
                ],
            ],

            'topic_key' => 'products',
            'handler' => 'HandlerClass'
        ],
    ]);

    /** @var \Micromus\KafkaBus\Bus\Listeners\Workers\Worker $worker */
    $worker = resolve(WorkerRegistryFactory::class)
        ->create()
        ->get('products_other');

    assertEquals($worker->options->additionalOptions, [
        'test.option' => 'foo',
        'not.override' => 'test-value',
        'new.option' => 'bar',
    ]);

    assertEquals($worker->options->consumerTimeout, 5_000);

    assertEquals($worker->options->middlewares, ['MiddlewareClass', 'FirstMiddlewareOnlyGlobal', 'OtherMiddlewareClass']);

    $routes = $worker->routes->all();

    assertCount(1, $routes);
    assertEquals('products', $routes['products']->topicKey);
    assertEquals('HandlerClass', $routes['products']->handlerClass);
});
