<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Interfacing;

use App\Domaining\Dto\DomainInterfacingPayload;
use App\Domaining\Dto\DomainTemplateRenderResult;

interface DomainTemplateRenderServiceInterface
{
    public function render(DomainInterfacingPayload $payload): DomainTemplateRenderResult;
}
