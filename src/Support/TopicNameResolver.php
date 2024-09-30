<?php

namespace Micromus\KafkaBus\Support;


use Micromus\KafkaBus\Contracts\TopicNameResolver as TopicNameResolverContract;
use Micromus\KafkaBus\Exceptions\TopicCannotResolvedException;

class TopicNameResolver implements TopicNameResolverContract
{
    public function __construct(
        protected string $prefix,
        protected array $topics
    ) {
    }

    public function resolve(string $topicKey): string
    {
        return "$this->prefix{$this->name($topicKey)}";
    }

    private function name(string $topicKey): string
    {
        return $this->topics[$topicKey]
            ?? throw new TopicCannotResolvedException("Topic [$topicKey] not found");
    }
}
