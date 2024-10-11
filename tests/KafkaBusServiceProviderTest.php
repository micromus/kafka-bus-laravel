<?php


use Micromus\KafkaBus\Connections\KafkaConnection;
use Micromus\KafkaBus\Connections\Registry\DriverRegistry;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionRegistryInterface;
use Micromus\KafkaBus\Topics\TopicRegistry;

it('resolve topic name', function () {
    config()->set('kafka-bus.topic_prefix', 'production');
    config()->set('kafka-bus.topics', ['products' => ['name' => '.fact.products.1']]);

    $topicRegistry = resolve(TopicRegistry::class);

    expect($topicRegistry->getTopicName('products'))
        ->toEqual('production.fact.products.1');
});

it('can add new driver to driver registry', function () {
    app()->afterResolving(DriverRegistry::class, function (DriverRegistry $driverRegistry) {
        $driverRegistry->add('test', fn() => new KafkaConnection([]));
    });

    $connection = resolve(DriverRegistry::class)
        ->makeConnection('test', []);

    expect($connection)
        ->toBeInstanceOf(KafkaConnection::class);
});

it('can create connection', function () {
    $connection = resolve(ConnectionRegistryInterface::class)
        ->connection('kafka');

    expect($connection)
        ->toBeInstanceOf(KafkaConnection::class);
});
