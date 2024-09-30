<?php

namespace Micromus\KafkaBus\Contracts;

interface TopicNameResolver
{
    public function resolve(string $topicKey): string;
}
