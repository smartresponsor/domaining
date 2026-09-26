<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Audit;

use App\Domaining\DTO\DomainAuditTrailEntryDTO;

interface DomainAuditTrailReadServiceInterface
{
    /** @return list<DomainAuditTrailEntryDTO> */
    public function recentForDomain(string $domainName, int $limit = 50): array;
}
