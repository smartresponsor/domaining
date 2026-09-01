<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use App\Domaining\Entity\DomainBinding;
use App\Domaining\Entity\DomainRoutingTarget;
use App\Domaining\RepositoryInterface\DomainRoutingTargetRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DomainRoutingTarget>
 */
final class DomainRoutingTargetRepository extends ServiceEntityRepository implements DomainRoutingTargetRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainRoutingTarget::class);
    }

    public function findOneForBinding(\App\Domaining\Entity\DomainBinding $binding): ?DomainRoutingTarget
    {
        $target = $this->findOneBy(['binding' => $binding]);

        return $target instanceof DomainRoutingTarget ? $target : null;
    }
}
