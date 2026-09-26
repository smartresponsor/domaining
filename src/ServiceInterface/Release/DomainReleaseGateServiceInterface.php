<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Release;

use App\Domaining\DTO\DomainReleaseGateReportDTO;

interface DomainReleaseGateServiceInterface
{
    public function evaluate(): DomainReleaseGateReportDTO;
}
