<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Interfacing;

use App\Domaining\Dto\DomainInterfacingPayload;
use App\Domaining\Entity\DomainBinding;

interface DomainInterfacingPayloadServiceInterface
{
    public function payloadForBinding(DomainBinding $binding): DomainInterfacingPayload;
}
