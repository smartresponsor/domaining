<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use App\Domaining\RepositoryInterface\DomainVerificationChallengeRepositoryInterface;
use App\Domaining\Entity\DomainVerificationChallenge;
use App\Domaining\Enum\DomainVerificationStatus;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DomainVerificationChallenge>
 */
final class DomainVerificationChallengeRepository extends ServiceEntityRepository implements DomainVerificationChallengeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainVerificationChallenge::class);
    }

    /**
     * @return list<DomainVerificationChallenge>
     */
    public function findPendingReadyForCheck(DateTimeImmutable $now, int $limit = 50): array
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
