<?php

declare(strict_types=1);

namespace App\Domaining\Service\Audit;

use App\Domaining\Entity\DomainAuditRecordEntity;
use App\Domaining\Repository\DomainPersistenceRepository;
use App\Domaining\ServiceInterface\Audit\DomainAuditServiceInterface;

final readonly class DomainAuditService implements DomainAuditServiceInterface
{
    public function __construct(private DomainPersistenceRepository $persistenceRepository)
    {
    }

    public function record(string $domainName, string $action, ?string $actorId = null, array $context = []): void
    {
        $this->persistenceRepository->persist(new DomainAuditRecordEntity($domainName, $action, $actorId, $context));
    }
}
