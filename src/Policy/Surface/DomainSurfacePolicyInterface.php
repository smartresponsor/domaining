<?php

declare(strict_types=1);

namespace App\Domaining\Policy\Surface;

use App\Domaining\DTO\DomainSurfacePolicyReportDTO;
use App\Domaining\Enum\DomainSurfaceType;

interface DomainSurfacePolicyInterface
{
    public function buildReport(string $ownerId, ?DomainSurfaceType $surfaceType = null, ?string $surfaceKey = null): DomainSurfacePolicyReportDTO;
}
