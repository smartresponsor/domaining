<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainRoutingTargetEntity;
use App\Domaining\RepositoryInterface\DomainRoutingTargetRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DomainRoutingTargetEntity>
 */
final class DomainRoutingTargetRepository extends ServiceEntityRepository implements DomainRoutingTargetRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainRoutingTargetEntity::class);
    }

    public function findOneForBinding(DomainBindingEntity $binding): ?DomainRoutingTargetEntity
    {
        $target = $this->findOneBy(['binding' => $binding]);

        return $target instanceof DomainRoutingTargetEntity ? $target : null;
    }
}
