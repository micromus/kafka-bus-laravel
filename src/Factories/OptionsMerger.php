<?php

namespace Micromus\KafkaBusLaravel\Factories;

final class OptionsMerger
{
    public function merge(array $options, array $globalOptions): array
    {
        $middlewares = $options['middlewares'] ?? [];
        $globalMiddlewares = array_diff($globalOptions['middlewares'] ?? [], $middlewares);

        return [
           ...$globalOptions,
           ...$options,

           'middlewares' => [
               ...$globalMiddlewares,
               ...$middlewares,
           ],

           'additional_options' => [
               ...($globalOptions['additional_options'] ?? []),
               ...($options['additional_options'] ?? []),
           ],
        ];
    }
}
