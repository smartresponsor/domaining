<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Release;

use App\Domaining\Dto\DomainReleaseGateReport;

interface DomainReleaseGateServiceInterface
{
    public function evaluate(): DomainReleaseGateReport;
}
