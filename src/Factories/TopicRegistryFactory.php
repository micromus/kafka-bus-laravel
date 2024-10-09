<?php

namespace Micromus\KafkaBusLaravel\Factories;

use Illuminate\Config\Repository;
use Micromus\KafkaBus\Topics\Topic;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBusLaravel\Exceptions\KafkaBusConfigurationException;

class TopicRegistryFactory
{
    public function __construct(
        protected Repository $configRepository
    ) {
    }

    public function create(): TopicRegistry
    {
        $topicRegistry = new TopicRegistry();

        $prefix = $this->configRepository->get('kafka-bus.topic_prefix');
        $topics = $this->configRepository->get('kafka-bus.topics', []);

        foreach ($topics as $topicKey => $topic) {
            $topicName = $topic['name']
                ?? throw new KafkaBusConfigurationException("Param [kafka-bus.topics.$topicKey.name] is required");

            $partitions = $topic['partitions'] ?? 1;

            $topicRegistry->add(new Topic($prefix . $topicName, $topicKey, $partitions));
        }

        return $topicRegistry;
    }
}
