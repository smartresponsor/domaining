<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Interfacing;

use App\Domaining\DTO\DomainInterfacingPayloadDTO;
use App\Domaining\DTO\DomainTemplateRenderResultDTO;

interface DomainTemplateRenderServiceInterface
{
    public function render(DomainInterfacingPayloadDTO $payload): DomainTemplateRenderResultDTO;
}
