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
            'MiddlewareClass',
        ],

        'consume_timeout' => 5_000,
        'auto_commit' => true,
        'max_messages' => 150_000,
    ]);

    config()->set('kafka-bus.consumers.workers', [
        'default-worker' => [
            'options' => [
                'additional_options' => [
                    'test.option' => 'foo',
                    'new.option' => 'bar',
                ],

                'middlewares' => [
                    'OtherMiddlewareClass',
                ],
            ],

            'topics' => [
                'products' => [
                    'handler' => 'HandlerClass',
                    'message_factory' => 'MessageFactoryClass',
                ],
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
    assertEquals($worker->maxMessages, 150_000);

    assertEquals($worker->options->middlewares, ['MiddlewareClass', 'OtherMiddlewareClass']);

    $routes = $worker->routes->all();

    assertCount(1, $routes);
    assertEquals('products', $routes['products']->topicKey);
    assertEquals('HandlerClass', $routes['products']->handlerClass);
    assertEquals('MessageFactoryClass', $routes['products']->messageFactoryClass);
});
