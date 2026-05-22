<?php

namespace Workbench\App\Kafka\Messages;

use Micromus\KafkaBusMessages\DomainMessage;

/**
 * @property int $id
 * @property string $name
 */
final class ProductDomainMessage extends DomainMessage
{
    public function getKey(): ?string
    {
        return $this->id;
    }
}
