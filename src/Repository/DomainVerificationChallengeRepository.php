<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use App\Domaining\Entity\DomainVerificationChallengeEntity;
use App\Domaining\Enum\DomainVerificationStatus;
use App\Domaining\RepositoryInterface\DomainVerificationChallengeRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DomainVerificationChallengeEntity>
 */
final class DomainVerificationChallengeRepository extends ServiceEntityRepository implements DomainVerificationChallengeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainVerificationChallengeEntity::class);
    }

    /**
     * @return list<DomainVerificationChallengeEntity>
     */
    public function findPendingReadyForCheck(\DateTimeImmutable $now, int $limit = 50): array
    {
        return $this->createQueryBuilder('challenge')
            ->andWhere('challenge.status = :status')
            ->andWhere('challenge.expiresAt > :now')
            ->andWhere('challenge.nextCheckAfter IS NULL OR challenge.nextCheckAfter <= :now')
            ->setParameter('status', DomainVerificationStatus::Pending)
            ->setParameter('now', $now)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
