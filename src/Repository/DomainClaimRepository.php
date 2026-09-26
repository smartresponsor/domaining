<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use App\Domaining\Entity\DomainClaimEntity;
use App\Domaining\RepositoryInterface\DomainClaimRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DomainClaimEntity>
 */
final class DomainClaimRepository extends ServiceEntityRepository implements DomainClaimRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainClaimEntity::class);
    }

    public function findOneByDomainAndOwner(string $domainName, string $ownerId): ?DomainClaimEntity
    {
        return $this->findOneBy(['domainName' => $domainName, 'ownerId' => $ownerId]);
    }
}
