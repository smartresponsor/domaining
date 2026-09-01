<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Contract;

use App\Domaining\Dto\DomainContractGovernanceReport;

interface DomainContractGovernanceServiceInterface
{
    public function buildReport(): DomainContractGovernanceReport;
}
