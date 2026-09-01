<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use App\Domaining\RepositoryInterface\DomainBindingRepositoryInterface;
use App\Domaining\Entity\DomainBinding;
use App\Domaining\Enum\DomainBindingStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DomainBinding>
 */
final class DomainBindingRepository extends ServiceEntityRepository implements DomainBindingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainBinding::class);
    }

    public function findActiveByDomainName(string $domainName): ?DomainBinding
    {
        return $this->findOneBy(['domainName' => $domainName, 'status' => DomainBindingStatus::Active]);
    }

    public function findLiveByDomainName(string $domainName): ?DomainBinding
    {
        return $this->createQueryBuilder('binding')
            ->andWhere('binding.domainName = :domainName')
            ->andWhere('binding.status IN (:statuses)')
            ->setParameter('domainName', $domainName)
            ->setParameter('statuses', [
                DomainBindingStatus::Verified,
                DomainBindingStatus::Active,
                DomainBindingStatus::Suspended,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }
}
