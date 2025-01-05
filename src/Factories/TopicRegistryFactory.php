<?php

namespace Micromus\KafkaBusLaravel\Factories;

use Illuminate\Config\Repository;
use Micromus\KafkaBus\Topics\Topic;
use Micromus\KafkaBus\Topics\TopicRegistry;

final class TopicRegistryFactory
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

        foreach ($topics as $topicKey => $topicName) {
            $topicRegistry->add(new Topic($prefix.$topicName, $topicKey));
        }

        return $topicRegistry;
    }
}
