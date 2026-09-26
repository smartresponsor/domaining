<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Interfacing;

use App\Domaining\DTO\DomainInterfacingPayloadDTO;
use App\Domaining\Entity\DomainBindingEntity;

interface DomainInterfacingPayloadServiceInterface
{
    public function payloadForBinding(DomainBindingEntity $binding): DomainInterfacingPayloadDTO;
}
