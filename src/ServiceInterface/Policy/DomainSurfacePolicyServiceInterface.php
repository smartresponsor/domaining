<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Policy;

use App\Domaining\Dto\DomainSurfacePolicyReport;
use App\Domaining\Enum\DomainSurfaceType;

interface DomainSurfacePolicyServiceInterface
{
    public function buildReport(string $ownerId, ?DomainSurfaceType $surfaceType = null, ?string $surfaceKey = null): DomainSurfacePolicyReport;
}
