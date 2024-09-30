<?php

namespace Micromus\KafkaBus\Contracts\Bus;

interface Bus extends Thread
{
    public function onConnection(string $connectionName): Thread;
}
