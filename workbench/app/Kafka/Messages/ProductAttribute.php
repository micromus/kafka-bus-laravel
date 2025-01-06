<?php

namespace Workbench\App\Kafka\Messages;

use Micromus\KafkaBusMessages\Data\Payload;
use Micromus\KafkaBusMessages\Interfaces\AttributesInterface;

/**
 * @property int $id
 * @property string $name
 */
final class ProductAttribute extends Payload implements AttributesInterface
{
}
