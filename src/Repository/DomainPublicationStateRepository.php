<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use App\Domaining\RepositoryInterface\DomainPublicationStateRepositoryInterface;
use App\Domaining\Entity\DomainPublicationState;
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
}
