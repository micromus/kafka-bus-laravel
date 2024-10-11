<?php

use Micromus\KafkaBus\Topics\Topic;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBusLaravel\Factories\TopicRegistryFactory;

it('can create a topic registry', function () {
    config()->set('kafka-bus.topic_prefix', 'production.');

    config()->set('kafka-bus.topics', [
        'products' => [
            'name' => 'fact.products.1',
            'partitions' => 5,
        ],
    ]);

    /** @var TopicRegistry $topicRegistry */
    $topicRegistry = resolve(TopicRegistryFactory::class)
        ->create();

    $topic = $topicRegistry->get('products');

    expect($topic)->toBeInstanceOf(Topic::class)
        ->and($topic->name)->toBe('production.fact.products.1')
        ->and($topic->key)->toBe('products')
        ->and($topic->partitions)->toBe(5);
});
