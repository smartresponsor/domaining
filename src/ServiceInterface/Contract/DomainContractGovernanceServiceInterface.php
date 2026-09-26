<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Contract;

use App\Domaining\DTO\DomainContractGovernanceReportDTO;

interface DomainContractGovernanceServiceInterface
{
    public function buildReport(): DomainContractGovernanceReportDTO;
}
