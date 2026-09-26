<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use App\Domaining\Entity\DomainAuditRecordEntity;
use App\Domaining\RepositoryInterface\DomainAuditRecordRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DomainAuditRecordEntity>
 */
final class DomainAuditRecordRepository extends ServiceEntityRepository implements DomainAuditRecordRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainAuditRecordEntity::class);
    }

    /** @return list<DomainAuditRecordEntity> */
    public function recentForDomain(string $domainName, int $limit = 50): array
    {
        return $this->createQueryBuilder('record')
            ->andWhere('record.domainName = :domainName')
            ->setParameter('domainName', $domainName)
            ->orderBy('record.createdAt', 'DESC')
            ->setMaxResults(max(1, min(250, $limit)))
            ->getQuery()
            ->getResult();
    }
}
