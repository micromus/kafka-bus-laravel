<?php

namespace Micromus\KafkaBusLaravel\Components\Repeaters\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $worker_name
 * @property string $topic_name
 * @property string $payload
 * @property array $headers
 * @property string|null $key
 * @property int $partition
 * @property int $offset
 *
 * @property CarbonInterface|null $updated_at
 * @property CarbonInterface|null $created_at
 */
final class MessageFailed extends Model
{
    public $incrementing = false;

    protected $table = 'kafka_bus_message_fails';

    protected $casts = [
        'headers' => 'array',
    ];
}
