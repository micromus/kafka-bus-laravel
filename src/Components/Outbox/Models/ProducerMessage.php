<?php

namespace Micromus\KafkaBusLaravel\Components\Outbox\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $connection_name
 * @property string $topic_name
 * @property string $payload
 * @property array $headers
 * @property string|null $key
 * @property int $partition
 * @property array $additional_options
 */
final class ProducerMessage extends Model
{
    public $timestamps = false;

    protected $table = 'kafka_bus_producer_messages';

    protected $attributes = [
        'headers' => [],
        'additional_options' => []
    ];

    protected $casts = [
        'headers' => 'array',
        'partition' => 'integer',
        'additional_options' => 'array',
    ];
}
