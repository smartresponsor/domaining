<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainPublicationStateEntity;
use App\Domaining\RepositoryInterface\DomainPublicationStateRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DomainPublicationStateEntity>
 */
final class DomainPublicationStateRepository extends ServiceEntityRepository implements DomainPublicationStateRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainPublicationStateEntity::class);
    }

    public function findOneForBinding(DomainBindingEntity $binding): ?DomainPublicationStateEntity
    {
        $state = $this->findOneBy(['binding' => $binding]);

        return $state instanceof DomainPublicationStateEntity ? $state : null;
    }
}
