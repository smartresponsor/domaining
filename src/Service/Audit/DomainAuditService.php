<?php

declare(strict_types=1);

namespace App\Domaining\Service\Audit;

use App\Domaining\Entity\DomainAuditRecord;
use App\Domaining\ServiceInterface\Audit\DomainAuditServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DomainAuditService implements DomainAuditServiceInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function record(string $domainName, string $action, ?string $actorId = null, array $context = []): void
    {
        $this->entityManager->persist(new DomainAuditRecord($domainName, $action, $actorId, $context));
    }
}
