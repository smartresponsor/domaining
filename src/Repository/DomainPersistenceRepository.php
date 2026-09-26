<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Centralizes Doctrine unit-of-work access for Domaining application services.
 */
final readonly class DomainPersistenceRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function persist(object ...$entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    public function persistAndFlush(object ...$entities): void
    {
        $this->persist(...$entities);
        $this->flush();
    }
}
