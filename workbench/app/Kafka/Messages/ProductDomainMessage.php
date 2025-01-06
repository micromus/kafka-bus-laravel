<?php

namespace Workbench\App\Kafka\Messages;

use Micromus\KafkaBusMessages\DomainMessage;

/**
 * @extends DomainMessage<ProductAttribute>
 */
final readonly class ProductDomainMessage extends DomainMessage
{
    public function getKey(): ?string
    {
        return (string) $this->attributes->id;
    }
}
