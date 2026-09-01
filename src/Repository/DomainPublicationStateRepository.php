<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use App\Domaining\Entity\DomainBinding;
use App\Domaining\Entity\DomainPublicationState;
use App\Domaining\RepositoryInterface\DomainPublicationStateRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DomainPublicationState>
 */
final class DomainPublicationStateRepository extends ServiceEntityRepository implements DomainPublicationStateRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainPublicationState::class);
    }

    public function findOneForBinding(DomainBinding $binding): ?DomainPublicationState
    {
        $state = $this->findOneBy(['binding' => $binding]);

        return $state instanceof DomainPublicationState ? $state : null;
    }
}
