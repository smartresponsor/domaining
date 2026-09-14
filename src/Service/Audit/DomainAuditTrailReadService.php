<?php

declare(strict_types=1);

namespace App\Domaining\Service\Audit;

use App\Domaining\Dto\DomainAuditTrailEntry;
use App\Domaining\Entity\DomainAuditRecord;
use App\Domaining\Repository\DomainAuditRecordRepository;
use App\Domaining\ServiceInterface\Audit\DomainAuditTrailReadServiceInterface;
use App\Domaining\Value\DomainName;

final readonly class DomainAuditTrailReadService implements DomainAuditTrailReadServiceInterface
{
    public function __construct(private DomainAuditRecordRepository $auditRecordRepository)
    {
    }

    public function recentForDomain(string $domainName, int $limit = 50): array
    {
        $normalizedDomainName = (new DomainName($domainName))->value;
        $records = $this->auditRecordRepository->recentForDomain($normalizedDomainName, max(1, min(250, $limit)));

        return array_map(static fn (DomainAuditRecord $record): DomainAuditTrailEntry => new DomainAuditTrailEntry(
            (string) $record->id(),
            $record->domainName(),
            $record->action(),
            $record->actorId(),
            $record->context(),
            $record->createdAt(),
        ), $records);
    }
}
