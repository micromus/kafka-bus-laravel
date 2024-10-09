<?php

use Workbench\App\Kafka\Consumers\ProductsTopicConsumer;

return [
    'default' => env('KAFKA_CONNECTION', 'kafka'),

    'connections' => [
        'testing' => [
            'driver' => 'null',
            'options' => [],
        ],

        'kafka' => [
            'driver' => 'kafka',
            'options' => [
                /*
                 | Your kafka brokers url.
                 */
                'metadata.broker.list' => env('KAFKA_BROKER_LIST'),

                /*
                 | Default security protocol
                 */
                'security.protocol' => env('KAFKA_SECURITY_PROTOCOL', 'plaintext'),
                'sasl.mechanisms' => env('KAFKA_SASL_MECHANISMS'),
                'sasl.username' => env('KAFKA_SASL_USERNAME'),
                'sasl.password' => env('KAFKA_SASL_PASSWORD'),

                'log_level' => env('KAFKA_DEBUG', false) ? (string) LOG_DEBUG : (string) LOG_INFO,

                /*
                 | Choose if debug is enabled or not.
                 */
                'debug' => env('KAFKA_DEBUG', false) ? 'all' : null,
            ],
        ],
    ],

    'prefix' => env('KAFKA_PREFIX', env('APP_ENV', 'local').'.'),

    'topics' => [
        'products' => [
            'name' => 'fact.products.1',
            'partition' => (int) env('KAFKA_TOPIC_PRODUCTS_PARTITIONS', 1),
        ],
    ],

    'consumers' => [
        /*
         | Optional, defaults to empty array.
         | Array of middleware.
        */
        'middlewares' => [
            //
        ],

        /*
         | If you set enable.auto.commit (which is the default), then the consumer will automatically commit offsets periodically at the
         | interval set by auto.commit.interval.ms.
         */
        'auto_commit' => env('KAFKA_CONSUMER_AUTO_COMMIT', true),

        /*
         | Optional, defaults to 20000.
         | Kafka consume timeout in milliseconds.
         */
        'consume_timeout' => 20000,

        /*
         | Options for Kafka Consumer
         */
        'additional_options' => [
            /*
             | Kafka consumers belonging to the same consumer group share a group id.
             | The consumers in a group then divides the topic partitions as fairly amongst themselves as possible by
             | establishing that each partition is only consumed by a single consumer from the group.
             | This config defines the consumer group id you want to use for your project.
             */
            'group.id' => env('KAFKA_CONSUMER_GROUP_ID', env('APP_NAME')),

            'auto.offset.reset' => 'beginning',
        ],

        /*
         | Optional, defaults to -1.
         | The number of messages that will be listened
         | to before is disabled.
         */
        'max_messages' => env('KAFKA_CONSUMER_MAX_MESSAGES', 150_000),

        /*
         | Optional, defaults to -1.
         | The amount of time that will be listened to before disabling.
         */
        'max_time' => env('KAFKA_CONSUMER_MAX_TIME', -1),

        'workers' => [
            'default' => [
                'topics' => [
                    'products' => [
                        'handler' => ProductsTopicConsumer::class,
                    ],
                ],
            ],
        ],
    ],

    'producers' => [
        /*
         | Optional, defaults to empty array.
         | Array of middleware.
        */
        'middlewares' => [
            //
        ],

        /*
         | Optional, defaults to 5000.
         | Kafka producer flush timeout in milliseconds.
         */
        'flush_timeout' => 5000,

        /*
         | Optional, defaults to 5.
         | Kafka producer flush retries
         */
        'flush_retries' => 5,

        /*
         | Options for Kafka Producer
         */
        'additional_options' => [
            /*
             | Kafka supports 4 compression codecs: none , gzip , lz4 and snappy
             */
            'compression.codec' => env('KAFKA_PRODUCER_COMPRESSION_CODEC', 'snappy'),
        ],

        'routes' => [
            //App\Kafka\Messages\ProductMessage::class => [
            //    'topic_key' => 'products',
            //    'options' => [
            //        'middlewares' => [],
            //        'additional_options' => [],
            //        'flush_timeout' => 5000, // Override global option, remove if not need
            //        'flush_retries' => 5, // Override global option, remove if not need
            //    ]
            //]
        ],
    ],
];