<?php

namespace Micromus\KafkaBusLaravel\Components\Repeaters\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $topic_name
 * @property string|null $key
 * @property int $timestamp
 * @property CarbonInterface|null $updated_at
 * @property CarbonInterface|null $created_at
 */
final class MessageCommit extends Model
{
    protected $table = 'kafka_bus_message_commits';

    public $incrementing = false;
}
