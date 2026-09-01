<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use App\Domaining\RepositoryInterface\DomainClaimRepositoryInterface;
use App\Domaining\Entity\DomainClaim;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DomainClaim>
 */
final class DomainClaimRepository extends ServiceEntityRepository implements DomainClaimRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainClaim::class);
    }

    public function findOneByDomainAndOwner(string $domainName, string $ownerId): ?DomainClaim
    {
        return $this->findOneBy(['domainName' => $domainName, 'ownerId' => $ownerId]);
    }
}
