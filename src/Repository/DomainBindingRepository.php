<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainDeclarationEntity;
use App\Domaining\Enum\DomainBindingStatus;
use App\Domaining\RepositoryInterface\DomainBindingRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DomainBindingEntity>
 */
final class DomainBindingRepository extends ServiceEntityRepository implements DomainBindingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainBindingEntity::class);
    }

    public function findActiveByDomainName(string $domainName): ?DomainBindingEntity
    {
        return $this->findOneBy(['domainName' => $domainName, 'status' => DomainBindingStatus::Active]);
    }

    public function findLiveByDomainName(string $domainName): ?DomainBindingEntity
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

    public function findOneForDeclaration(DomainDeclarationEntity $declaration): ?DomainBindingEntity
    {
        $binding = $this->findOneBy(['declaration' => $declaration]);

        return $binding instanceof DomainBindingEntity ? $binding : null;
    }
}
