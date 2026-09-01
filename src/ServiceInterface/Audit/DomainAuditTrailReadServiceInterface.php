<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Audit;

use App\Domaining\Dto\DomainAuditTrailEntry;

interface DomainAuditTrailReadServiceInterface
{
    /** @return list<DomainAuditTrailEntry> */
    public function recentForDomain(string $domainName, int $limit = 50): array;
}
